<?php
/**
 * Shortcodes
 */

namespace PluginRx\AdminHelpDocs;

if ( ! defined( 'ABSPATH' ) ) exit;

class Shortcodes {

    /**
     * The single instance of the class
     *
     * @var self|null
     */
    private static ?Shortcodes $instance = null;


    /**
     * Get the singleton instance
     *
     * @return self
     */
    public static function instance() : self {
        return self::$instance ??= new self();
    } // End instance()


    /**
	 * Constructor
	 */
	private function __construct() {

        // Display shortcodes without executing them
        add_shortcode( 'dont_do_shortcode', [ $this, 'dont_do_shortcode' ] );

        // Deprecated: Add custom CSS to documents
        add_shortcode( 'helpdocs_css', [ $this, 'helpdocs_css' ] );

        // Stale posts and and pages
        add_shortcode( 'helpdocs_stale_content', [ $this, 'stale_content' ] );

        // Display information about the currently logged-in user
        add_shortcode( 'helpdocs_current_user', [ $this, 'current_user' ] );

        // Display site information like name and URL and other site options
        add_shortcode( 'helpdocs_site_info', [ $this, 'site_info' ] );

        // List all posts with certain criteria
        add_shortcode( 'helpdocs_posts', [ $this, 'posts' ] );

        // List all users with certain criteria
        add_shortcode( 'helpdocs_users', [ $this, 'users' ] );

	} // End __construct()


    /**
     * Display shortcodes without executing them
     * USAGE: [dont_do_shortcode click_to_copy="false" code="false"][your_shortcode_here][/dont_do_shortcode]
     *
     * @param array $atts
     * @return string
     */
    public function dont_do_shortcode( $atts, $content = null ) : string {
        $atts = shortcode_atts( [ 
            'content'       => '',
            'code'          => true,
            'click_to_copy' => true
        ], $atts );

        $wrapper = ( strtolower( sanitize_text_field( $atts[ 'code' ] ) ) == 'false' ) ? 'span' : 'code';
        $click_to_copy = ( strtolower( sanitize_text_field( $atts[ 'click_to_copy' ] ) ) == 'false' ) ? false : true;

        // Support legacy method of passing content as an attribute with curly braces instead of square brackets
        if ( empty( $content ) && ! empty( $atts[ 'content' ] ) ) {
            $content = $atts[ 'content' ];
            $content = str_replace( '{', '[[', $content );
            $content = str_replace( '}', ']]', $content );
        }

        $click_to_copy_class = $click_to_copy ? ' helpdocs-click-to-copy' : '';

        return '<' . $wrapper . ' class="helpdocs_dont_do_shortcode' . $click_to_copy_class . '">' . $content . '</' . $wrapper . '>';
    } // End dont_do_shortcode()


    /**
     * Add custom CSS (External or Inline) to the document
     *
     * @deprecated Add CSS to main docs page from the settings page now.
     * @return string
     */
    public function helpdocs_css() : string {
        _deprecated_function( __FUNCTION__, '2.0', 'Add CSS to main docs page from the settings page now.' );
        return '';
    } // End helpdocs_css()


    /**
     * Shortcode: List stale posts or pages that haven't been updated in over X time.
     *
     * Usage:
     *   [helpdocs_stale_content post_types="post, custom_post_type" since="6 months"]
     *   [helpdocs_stale_content post_types="page" since="1 year"]
     *   [helpdocs_stale_content since="90 days"]
     *   [helpdocs_stale_content since="2 weeks" limit="20"]
     *
     * @param  array  $atts     Shortcode attributes.
     * @return string           Rendered HTML output.
     */
    public function stale_content( $atts ) {
        $atts = shortcode_atts(
            [
                'since'      => '1 year',   // e.g. "6 months", "90 days", "2 weeks", "1 year"
                'limit'      => 50,
                'post_types' => 'post, page',
            ],
            $atts,
        );

        // Build post type array — default to post + page if blank.
        if ( ! empty( $atts[ 'post_types' ] ) ) {
            $post_types = array_map( 'sanitize_key', array_map( 'trim', explode( ',', $atts[ 'post_types' ] ) ) );
        } else {
            $post_types = [ 'post', 'page' ];
        }

        // Parse the "since" value into a cutoff date.
        $cutoff = Helpers::parse_since_to_date( $atts[ 'since' ] );

        if ( ! $cutoff ) {
            return '<p class="helpdocs-error">'
                . esc_html__( 'Invalid "since" value. Use a format like "6 months", "90 days", "2 weeks", or "1 year".', 'admin-help-docs' )
                . '</p>';
        }

        $query = new \WP_Query( [
            'post_type'      => $post_types,
            'post_status'    => 'publish',
            'posts_per_page' => (int) $atts[ 'limit' ],
            'orderby'        => 'modified',
            'order'          => 'ASC',  // Oldest first — most stale at the top.
            'date_query'     => [
                [
                    'column' => 'post_modified_gmt',
                    'before' => $cutoff,
                ],
            ],
        ] );

        if ( ! $query->have_posts() ) {
            return '<p class="helpdocs-table-none">'
                . esc_html__( 'No content due for review was found.', 'admin-help-docs' )
                . '</p>';
        }

        $rows = '';
        while ( $query->have_posts() ) {
            $query->the_post();

            $last_modified = get_the_modified_date( 'Y-m-d' );

            $rows .= sprintf(
                '<tr>
                    <td class="helpdocs-table-title"><a href="%s">%s</a></td>
                    <td class="helpdocs-table-type">%s</td>
                    <td class="helpdocs-table-last-modified">%s</td>
                    <td class="helpdocs-table-age">%s</td>
                </tr>',
                esc_url( get_permalink() ),
                esc_html( get_the_title() ),
                esc_html( get_post_type_object( get_post_type() )->labels->singular_name ),
                esc_html( $last_modified ),
                esc_html( Helpers::time_elapsed_string( $last_modified ) )
            );
        }
        wp_reset_postdata();

        return sprintf(
            '<table class="helpdocs-table">
                <thead>
                    <tr>
                        <th class="helpdocs-table-title">%s</th>
                        <th class="helpdocs-table-type">%s</th>
                        <th class="helpdocs-table-last-modified">%s</th>
                        <th class="helpdocs-table-age">%s</th>
                    </tr>
                </thead>
                <tbody>%s</tbody>
            </table>',
            esc_html__( 'Title', 'admin-help-docs' ),
            esc_html__( 'Type', 'admin-help-docs' ),
            esc_html__( 'Last Modified', 'admin-help-docs' ),
            esc_html__( 'Age', 'admin-help-docs' ),
            $rows
        );
    } // End stale_content()


    /**
     * Shortcode: Display information about the currently logged-in user.
     *
     * Usage:
     *   [helpdocs_current_user]                        — Display name
     *   [helpdocs_current_user field="first_name"]     — First name
     *   [helpdocs_current_user field="user_email"]     — Email address
     *   [helpdocs_current_user field="my_custom_meta"] — Any custom user meta key
     *
     * @param  array  $atts     Shortcode attributes.
     * @return string           The user field value, or empty string if not found.
     */
    public function current_user( $atts ) {
        $atts = shortcode_atts( [
            'field'       => 'display_name',
            'date_format' => '',
        ], $atts );

        $user = wp_get_current_user();
        if ( ! $user || ! $user->exists() ) {
            return '';
        }

        $field = sanitize_key( $atts[ 'field' ] );
        $value = '';
        if ( isset( $user->$field ) ) {
            $value = $user->$field;
        } else {
            $meta = get_user_meta( $user->ID, $field, true );
            if ( $meta ) {
                $value = $meta;
            }
        }
        if ( $value === '' ) {
            return '';
        }

        if ( ! empty( $atts[ 'date_format' ] ) ) {
            $timestamp = is_numeric( $value ) ? (int) $value : strtotime( $value );
            if ( $timestamp ) {
                return esc_html( date_i18n( $atts[ 'date_format' ], $timestamp ) );
            }
        }
        return esc_html( $value );
    } // End current_user()


    /**
     * Shortcode: Display site information like name and URL and other site options.
     *
     * Usage:
     *   [helpdocs_site_info field="site_title"] — Site title
     *   [helpdocs_site_info field="admin_email"] — Admin email
     *
     * @param  array  $atts     Shortcode attributes.
     * @return string           The requested site information, or empty string if not found.
     */
    public function site_info( $atts ) {
        $atts = shortcode_atts( [
            'field'       => 'site_title',
            'date_format' => '',
        ], $atts );

        $map = [
            'site_title'          => 'blogname',
            'tagline'             => 'blogdescription',
            'admin_email'         => 'admin_email',
            'timezone'            => 'timezone_string',
            'permalink_structure' => 'permalink_structure',
            'language'            => 'WPLANG',
            'wp_version'          => null,
            'front_page'          => null,
            'posts_page'          => null,
            'date_format'         => 'date_format',
            'time_format'         => 'time_format',
            'uploads_path'        => 'upload_path',
            'site_url'            => 'siteurl',
            'home_url'            => 'home',
            'charset'             => 'blog_charset',
            'posts_per_page'      => 'posts_per_page',
            'anyone_can_register' => 'users_can_register',
            'default_role'        => 'default_role',
            'comments_open'       => 'default_comment_status',
        ];

        $field = sanitize_key( $atts[ 'field' ] );
        $value = '';
        if ( $field === 'wp_version' ) {
            global $wp_version;
            $value = $wp_version;
        } elseif ( $field === 'front_page' ) {
            $page_id = get_option( 'page_on_front' );
            $value   = $page_id ? get_the_title( $page_id ) : esc_html__( 'Latest Posts', 'admin-help-docs' );
        } elseif ( $field === 'posts_page' ) {
            $page_id = get_option( 'page_for_posts' );
            $value   = $page_id ? get_the_title( $page_id ) : esc_html__( 'Not set', 'admin-help-docs' );
        } elseif ( isset( $map[ $field ] ) ) {
            $value = get_option( $map[ $field ], '' );
        } else {
            $value = get_option( $field, '' );
        }
        if ( $value === '' ) {
            return '';
        }
        if ( ! empty( $atts[ 'date_format' ] ) ) {
            $timestamp = is_numeric( $value ) ? (int) $value : strtotime( $value );
            if ( $timestamp ) {
                return esc_html( date_i18n( $atts[ 'date_format' ], $timestamp ) );
            }
        }
        return esc_html( $value );
    } // End site_info()


    /**
     * Shortcode: List posts with various criteria.
     *
     * Usage:
     *   [helpdocs_posts] — Lists recent posts and pages.
     *   [helpdocs_posts post_type="post, page" post_status="publish, draft" limit="10" order="ASC" orderby="title" date_format="F j, Y"]
     *   [helpdocs_posts post_type="custom_post_type" author="admin, editor" category="news, updates" tag="featured"]
     *   [helpdocs_posts meta_key="my_meta_key" meta_value="my_meta_value"]
     *
     * @param  array  $atts     Shortcode attributes.
     * @return string           Rendered HTML output.
     */
    public function posts( $atts ) {
        $reserved = [ 'post_type', 'post_status', 'limit', 'order', 'orderby', 'date_format', 'author', 'category', 'tag', 'before', 'after', 'post_password__not', 'post_password' ];
        $atts      = $atts ? $atts : [];
        $split     = function( $value ) {
            return array_map( 'trim', explode( ',', $value ) );
        };
        $post_types    = isset( $atts[ 'post_type' ] )    ? $split( $atts[ 'post_type' ] )   : [ 'post', 'page' ];
        $post_statuses = isset( $atts[ 'post_status' ] )  ? $split( $atts[ 'post_status' ] ) : [ 'publish' ];
        $order         = isset( $atts[ 'order' ] )        ? strtoupper( $atts[ 'order' ] )   : 'DESC';
        $orderby       = isset( $atts[ 'orderby' ] )      ? $atts[ 'orderby' ]               : 'date';
        $limit         = isset( $atts[ 'limit' ] )        ? (int) $atts[ 'limit' ]           : 50;
        $date_format   = isset( $atts[ 'date_format' ] )  ? $atts[ 'date_format' ]           : 'Y-m-d';
        $show_type     = count( $post_types ) > 1;
        $show_status   = count( $post_statuses ) > 1;
        $query_args = [
            'post_type'      => $post_types,
            'post_status'    => $post_statuses,
            'posts_per_page' => $limit,
            'order'          => in_array( $order, [ 'ASC', 'DESC' ] ) ? $order : 'DESC',
            'orderby'        => sanitize_key( $orderby ),
        ];
        if ( isset( $atts[ 'author' ] ) ) {
            $authors = $split( $atts[ 'author' ] );
            $author_ids = [];
            foreach ( $authors as $author ) {
                if ( is_numeric( $author ) ) {
                    $author_ids[] = (int) $author;
                } else {
                    $user = get_user_by( 'login', $author );
                    if ( $user ) {
                        $author_ids[] = $user->ID;
                    }
                }
            }
            if ( $author_ids ) {
                $query_args[ 'author__in' ] = $author_ids;
            }
        }
        if ( isset( $atts[ 'category' ] ) ) {
            $cats = $split( $atts[ 'category' ] );
            $cat_ids = [];
            foreach ( $cats as $cat ) {
                if ( is_numeric( $cat ) ) {
                    $cat_ids[] = (int) $cat;
                } else {
                    $term = get_term_by( 'slug', $cat, 'category' );
                    if ( $term ) {
                        $cat_ids[] = $term->term_id;
                    }
                }
            }
            if ( $cat_ids ) {
                $query_args[ 'category__in' ] = $cat_ids;
            }
        }
        if ( isset( $atts[ 'tag' ] ) ) {
            $tags = $split( $atts[ 'tag' ] );
            $tag_ids = [];
            foreach ( $tags as $t ) {
                if ( is_numeric( $t ) ) {
                    $tag_ids[] = (int) $t;
                } else {
                    $term = get_term_by( 'slug', $t, 'post_tag' );
                    if ( $term ) {
                        $tag_ids[] = $term->term_id;
                    }
                }
            }
            if ( $tag_ids ) {
                $query_args[ 'tag__in' ] = $tag_ids;
            }
        }
        if ( isset( $atts[ 'post_password__not' ] ) && $atts[ 'post_password__not' ] === '' ) {
            $query_args[ 'has_password' ] = true;
        }
        if ( isset( $atts[ 'post_password' ] ) && $atts[ 'post_password' ] === '' ) {
            $query_args[ 'has_password' ] = false;
        }
        if ( isset( $atts[ 'before' ] ) || isset( $atts[ 'after' ] ) ) {
            $date_query = [];
            if ( isset( $atts[ 'after' ] ) ) {
                $date_query[ 'after' ] = sanitize_text_field( $atts[ 'after' ] );
            }
            if ( isset( $atts[ 'before' ] ) ) {
                $date_query[ 'before' ] = sanitize_text_field( $atts[ 'before' ] );
            }
            $date_query[ 'inclusive' ] = true;
            $query_args[ 'date_query' ] = [ $date_query ];
        }
        $meta_query = [];
        foreach ( $atts as $key => $value ) {
            if ( in_array( $key, $reserved ) ) {
                continue;
            }
            $negated   = str_ends_with( $key, '__not' );
            $meta_key  = $negated ? substr( $key, 0, -5 ) : $key;
            $values    = $split( $value );
            $compare   = $negated ? 'NOT IN' : 'IN';
            $meta_query[] = [
                'key'     => sanitize_key( $meta_key ),
                'value'   => $values,
                'compare' => $compare,
            ];
        }
        if ( $meta_query ) {
            $meta_query[ 'relation' ] = 'AND';
            $query_args[ 'meta_query' ] = $meta_query;
        }
        $query = new \WP_Query( $query_args );
        if ( ! $query->have_posts() ) {
            return '<p class="helpdocs-table-none">'
                . esc_html__( 'No posts found.', 'admin-help-docs' )
                . '</p>';
        }
        $rows = '';
        while ( $query->have_posts() ) {
            $query->the_post();
            $type_cell   = $show_type   ? '<td class="helpdocs-posts-type">' . esc_html( get_post_type_object( get_post_type() )->labels->singular_name ) . '</td>' : '';
            $status_label = get_post_status_object( get_post_status() );
            $status_cell  = $show_status ? '<td class="helpdocs-posts-status">' . esc_html( $status_label ? $status_label->label : get_post_status() ) . '</td>' : '';
            $rows .= sprintf(
                '<tr>
                    <td class="helpdocs-posts-title"><a href="%s">%s</a></td>
                    %s
                    %s
                    <td class="helpdocs-posts-date">%s</td>
                </tr>',
                esc_url( get_permalink() ),
                esc_html( get_the_title() ),
                $type_cell,
                $status_cell,
                esc_html( date_i18n( $date_format, get_the_date( 'U' ) ) )
            );
        }
        wp_reset_postdata();
        $type_header   = $show_type   ? '<th class="helpdocs-posts-type">'   . esc_html__( 'Type', 'admin-help-docs' )   . '</th>' : '';
        $status_header = $show_status ? '<th class="helpdocs-posts-status">' . esc_html__( 'Status', 'admin-help-docs' ) . '</th>' : '';
        return sprintf(
            '<table class="helpdocs-table">
                <thead>
                    <tr>
                        <th class="helpdocs-posts-title">%s</th>
                        %s
                        %s
                        <th class="helpdocs-posts-date">%s</th>
                    </tr>
                </thead>
                <tbody>%s</tbody>
            </table>',
            esc_html__( 'Title', 'admin-help-docs' ),
            $type_header,
            $status_header,
            esc_html__( 'Published', 'admin-help-docs' ),
            $rows
        );
    } // End posts()


    /**
     * Shortcode: List users with various criteria.
     *
     * Usage:
     *   [helpdocs_users] — Lists all users.
     *   [helpdocs_users role="administrator, editor" role__not="subscriber" limit="20" order="ASC" orderby="display_name" date_format="F j, Y" before="2024-01-01" after="2023-01-01"]
     *   [helpdocs_users meta_key="my_meta_key" meta_value="my_meta_value" can_view="administrator, editor"]
     *
     * @param  array  $atts     Shortcode attributes.
     * @return string           Rendered HTML output.
     */
    public function users( $atts ) {
        $reserved = [ 'role', 'role__not', 'limit', 'order', 'orderby', 'date_format', 'before', 'after', 'can_view' ];
        $atts     = $atts ? $atts : [];
        $split    = function( $value ) {
            return array_map( 'trim', explode( ',', $value ) );
        };
        $can_view = isset( $atts[ 'can_view' ] ) ? $split( $atts[ 'can_view' ] ) : [ 'administrator' ];
        $current_user = wp_get_current_user();
        if ( ! $current_user->exists() ) {
            return '';
        }
        $user_roles  = (array) $current_user->roles;
        $has_access  = array_intersect( $user_roles, $can_view );
        if ( ! $has_access ) {
            return '';
        }
        $order       = isset( $atts[ 'order' ] )        ? strtoupper( $atts[ 'order' ] )  : 'ASC';
        $orderby     = isset( $atts[ 'orderby' ] )      ? $atts[ 'orderby' ]              : 'display_name';
        $limit       = isset( $atts[ 'limit' ] )        ? (int) $atts[ 'limit' ]          : 50;
        $date_format = isset( $atts[ 'date_format' ] )  ? $atts[ 'date_format' ]          : 'Y-m-d';
        $query_args = [
            'number'  => $limit,
            'order'   => in_array( $order, [ 'ASC', 'DESC' ] ) ? $order : 'ASC',
            'orderby' => sanitize_key( $orderby ),
        ];
        if ( isset( $atts[ 'role' ] ) ) {
            $query_args[ 'role__in' ] = $split( $atts[ 'role' ] );
        }
        if ( isset( $atts[ 'role__not' ] ) ) {
            $query_args[ 'role__not_in' ] = $split( $atts[ 'role__not' ] );
        }
        if ( isset( $atts[ 'before' ] ) || isset( $atts[ 'after' ] ) ) {
            $date_query = [];
            if ( isset( $atts[ 'after' ] ) ) {
                $date_query[ 'after' ] = sanitize_text_field( $atts[ 'after' ] );
            }
            if ( isset( $atts[ 'before' ] ) ) {
                $date_query[ 'before' ] = sanitize_text_field( $atts[ 'before' ] );
            }
            $date_query[ 'inclusive' ]    = true;
            $query_args[ 'date_query' ]   = [ $date_query ];
        }
        $meta_query = [];
        foreach ( $atts as $key => $value ) {
            if ( in_array( $key, $reserved ) ) {
                continue;
            }
            $negated    = str_ends_with( $key, '__not' );
            $meta_key   = $negated ? substr( $key, 0, -5 ) : $key;
            $values     = $split( $value );
            $compare    = $negated ? 'NOT IN' : 'IN';
            $meta_query[] = [
                'key'     => sanitize_key( $meta_key ),
                'value'   => $values,
                'compare' => $compare,
            ];
        }
        if ( $meta_query ) {
            $meta_query[ 'relation' ]   = 'AND';
            $query_args[ 'meta_query' ] = $meta_query;
        }
        $users = get_users( $query_args );
        if ( empty( $users ) ) {
            return '<p class="helpdocs-table-none">'
                . esc_html__( 'No users found.', 'admin-help-docs' )
                . '</p>';
        }
        $rows = '';
        foreach ( $users as $user ) {
            $roles = array_map( function( $role ) {
                $wp_roles = wp_roles();
                return isset( $wp_roles->roles[ $role ] ) ? $wp_roles->roles[ $role ][ 'name' ] : $role;
            }, $user->roles );
            $rows .= sprintf(
                '<tr>
                    <td class="helpdocs-users-name"><a href="%s">%s</a></td>
                    <td class="helpdocs-users-login">%s</td>
                    <td class="helpdocs-users-email"><a href="mailto:%s">%s</a></td>
                    <td class="helpdocs-users-roles">%s</td>
                    <td class="helpdocs-users-registered">%s</td>
                </tr>',
                esc_url( get_edit_user_link( $user->ID ) ),
                esc_html( $user->display_name ),
                esc_html( $user->user_login ),
                esc_attr( $user->user_email ),
                esc_html( $user->user_email ),
                esc_html( implode( ', ', $roles ) ),
                esc_html( date_i18n( $date_format, strtotime( $user->user_registered ) ) )
            );
        }
        return sprintf(
            '<table class="helpdocs-table">
                <thead>
                    <tr>
                        <th class="helpdocs-users-name">%s</th>
                        <th class="helpdocs-users-login">%s</th>
                        <th class="helpdocs-users-email">%s</th>
                        <th class="helpdocs-users-roles">%s</th>
                        <th class="helpdocs-users-registered">%s</th>
                    </tr>
                </thead>
                <tbody>%s</tbody>
            </table>',
            esc_html__( 'Display Name', 'admin-help-docs' ),
            esc_html__( 'Username', 'admin-help-docs' ),
            esc_html__( 'Email', 'admin-help-docs' ),
            esc_html__( 'Role(s)', 'admin-help-docs' ),
            esc_html__( 'Registered', 'admin-help-docs' ),
            $rows
        );
    } // End users()
    
}


Shortcodes::instance();