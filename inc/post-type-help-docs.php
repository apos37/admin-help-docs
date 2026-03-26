<?php
/**
 * Help Docs post type (Manage Tab)
 */

namespace PluginRx\AdminHelpDocs;

if ( ! defined( 'ABSPATH' ) ) exit;

class HelpDocs {

    /**
     * Post type slug
     *
     * @var string
     */
    public static $post_type = 'help-docs';


    /**
     * Get available site locations for displaying help docs
     *
     * @return array
     */
    public static function site_locations() {
        $cache_key = 'helpdocs_loc_' . get_current_user_id();
        $cached    = get_transient( $cache_key );

        if ( false !== $cached && is_array( $cached ) ) {
            return apply_filters( 'helpdocs_site_locations', $cached );
        }
        
        $locations = [
            'main'      => [
                'label' => __( 'Main Documentation Page', 'admin-help-docs' ),
                'fields' => [ 'order', 'toc' ]
            ],
            'admin_bar' => [
                'label' => __( 'Admin Bar Menu (Must Be Enabled in Settings)', 'admin-help-docs' ),
                'fields' => [ 'order', 'admin_bar_tips' ]
            ],
            'update-core.php' => [
                'label' => __( 'Updates Page', 'admin-help-docs' ),
                'fields' => [ 'page_location', 'page_location:element:css_selector' ]
            ],
            'replace_dashboard' => [
                'label' => __( 'WordPress Dashboard (Replaces Dashboard Entirely)', 'admin-help-docs' ),
                'fields' => [ 'dashboard_warning' ]
            ],
            'index.php' => [
                'label' => __( 'WordPress Dashboard Widget', 'admin-help-docs' ),
                'fields' => []
            ],
            'function'  => [
                'label' => __( 'Function: admin_help_doc( id )', 'admin-help-docs' ),
                'fields' => [ 'function_example' ]
            ],
            'post.php'  => [
                'label' => __( 'Post/Page Edit Screen', 'admin-help-docs' ),
                'fields' => [ 'page_location', 'post_types', 'page_location:element:css_selector' ]
            ],
            'edit.php'  => [
                'label' => __( 'Post/Page Admin List Screen', 'admin-help-docs' ),
                'fields' => [ 'page_location', 'post_types', 'page_location:element:css_selector' ]
            ]
        ];

        global $menu, $submenu;
        $fetching_all_locations = false;
    
        if ( ! empty( $menu ) && is_array( $menu ) ) {
            $fetching_all_locations = true;

            $site_location_names = apply_filters( 'helpdocs_location_names', [
                'edit-comments.php'                        => __( 'Comments', 'admin-help-docs' ),
                'site-editor.php'                          => __( 'Editor', 'admin-help-docs' ),
                'plugins.php'                              => __( 'Plugins', 'admin-help-docs' ),
                'dev-debug-tools'                          => 'Dev Debug Tools',
                'admin.php?page=dev-debug-tools&tool=logs' => __( 'Logs', 'admin-help-docs' )
            ] );

            $textdomain = Bootstrap::textdomain();
            $admin_url = Bootstrap::admin_url();
            
            foreach ( $menu as $m ) {

                // Skip separators
                if ( str_starts_with( $m[2], 'separator' ) || $m[2] == 'hp_separator' || ( isset( $m[4] ) && strpos( $m[4], 'wp-menu-separator' ) !== false ) ) {
                    continue;
                }

                // Skip dashboard and help topics
                if ( $m[2] == 'index.php' || $m[2] == $textdomain ) {
                    continue;
                }

                // Skip post types
                if ( str_starts_with( $m[2], 'edit.php' ) ) {
                    continue;
                }

                // Change option names
                if ( array_key_exists( $m[2], $site_location_names ) ) {
                    $site_location_name = $site_location_names[ $m[2] ];
                } else {
                    $site_location_name = $m[0];
                }

                // Strip html
                $site_location_name = self::strip_admin_menu_counters( $site_location_name );

                // Add the parent location
                if ( ! array_key_exists( $m[2], $submenu ) ) {

                    $url = self::get_admin_menu_item_url( $m[2] );
                    if ( is_null( $url ) ) {
                        $url = 'not found';
                    }

                    $url = str_replace( $admin_url, '', $url );

                    $locations[ $url ] = [
                        'label'  => $site_location_name,
                        'fields' => [ 'page_location', 'page_location:element:css_selector' ]
                    ];
                }
                
                // Check for sub menu items
                foreach ( $submenu as $k => $sub ) {

                    if ( $k == $m[2] ) {
                        foreach ( $sub as $s ) {

                            if ( array_key_exists( $s[2], $site_location_names ) ) {
                                $sublocation_name = $site_location_names[ $s[2] ];
                            } else {
                                $sublocation_name = $s[0];
                            }

                            $sublocation_name = self::strip_admin_menu_counters( $sublocation_name );

                            $url = self::get_admin_menu_item_url( $s[2] );
                            if ( is_null( $url ) ) {
                                $url = 'not found';
                            }

                            $url = str_replace( $admin_url, '', $url );

                            $locations[ $url ] = [
                                'label'  => $site_location_name . ' > ' . $sublocation_name,
                                'fields' => [ 'page_location', 'page_location:element:css_selector' ]
                            ];
                        }
                    }
                }
            }
        }

        // Add a custom link option to the bottom
        $locations[ 'custom' ] = [
            'label' => __( 'Other/Custom Page', 'admin-help-docs' ),
            'fields' => [ 'custom', 'addt_params', 'page_location', 'page_location:element:css_selector' ]
        ];

        // Cache the result for 1 hour
        if ( $fetching_all_locations ) {
            set_transient( $cache_key, $locations, 12 * HOUR_IN_SECONDS );
        }

        return apply_filters( 'helpdocs_site_locations', $locations );
    } // End site_locations()


    /**
     * Get available page locations for displaying help docs
     *
     * @return array
     */
    public static function page_locations() {
        $locations = [
            'contextual' => __( 'Contextual Help Tab (At Top of Screen)', 'admin-help-docs' ),
            'top'        => __( 'Top', 'admin-help-docs' ),
            'bottom'     => __( 'Bottom', 'admin-help-docs' ),
            'side'       => __( 'Side', 'admin-help-docs' ),
            'element'    => __( 'Next to Specific Element (Beta)', 'admin-help-docs' ),
        ];

        return apply_filters( 'helpdocs_page_locations', $locations );
    } // End page_locations()


    /**
     * Get available post types for displaying help docs
     *
     * @return array
     */
    public static function post_types() {
        return array_merge( [ 'post' => 'post', 'page' => 'page' ], get_post_types( [ '_builtin' => false ] ) );
    } // End post_types()


    /**
     * The single instance of the class
     *
     * @var self|null
     */
    private static ?HelpDocs $instance = null;


    /**
     * Get the singleton instance
     *
     * @return self
     */
    public static function instance() : self {
        return self::$instance ??= new self();
    } // End instance()


    /**
     * HelpDocs constructor.
     *
     * Private to enforce singleton pattern.
     */
    private function __construct() {

        // Register the post type
        add_action( 'init', [ $this, 'register_post_type' ] );

        // Add the header to the top of the admin list page
        add_action( 'load-edit.php', [ $this, 'add_header' ] );

        // Move the search box to the subheader
        add_action( 'helpdocs_subheader_right', [ $this, 'render_search_box' ] );

        // Add the meta box
        add_action( 'add_meta_boxes', [ $this, 'meta_boxes' ] );

        // Rename excerpt meta box
        add_filter( 'gettext', [ $this, 'excerpt_meta_box' ], 10, 2 );

        // Save the post data
        add_action( 'save_post', [ $this, 'save_post' ] );
        add_action( 'before_delete_post', [ $this, 'delete_post' ] );

        // Add admin columns
        add_filter( 'manage_' . self::$post_type . '_posts_columns', [ $this, 'admin_columns' ] );
        add_action( 'manage_' . self::$post_type . '_posts_custom_column', [ $this, 'admin_column_content' ], 10, 2 );

        // Either enqueue block editor styles or disable block editor
        if ( ! get_option( 'helpdocs_gutenberg_editor' ) ) {
            add_filter( 'use_block_editor_for_post_type', [ $this, 'disable_gutenberg' ], 10, 2 );
        }

        // Enqueue back-end styles
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_styles' ] );
        
    } // End __construct()


    /**
     * Register the post type
     */
    public function register_post_type() {
        // Set the labels
        $labels = [
            'name'                  => _x( 'Help Documents', 'Post Type General Name', 'admin-help-docs' ),
            'singular_name'         => _x( 'Help Document', 'Post Type Singular Name', 'admin-help-docs' ),
            'menu_name'             => __( 'Help Documents', 'admin-help-docs' ),
            'name_admin_bar'        => __( 'Help Documents', 'admin-help-docs' ),
            'archives'              => __( 'Help Document Archives', 'admin-help-docs' ),
            'attributes'            => __( 'Help Document Attributes', 'admin-help-docs' ),
            'parent_item_colon'     => __( 'Parent Help Document:', 'admin-help-docs' ),
            'all_items'             => __( 'All Help Documents', 'admin-help-docs' ),
            'add_new_item'          => __( 'Add New Help Document', 'admin-help-docs' ),
            'add_new'               => __( 'Add New', 'admin-help-docs' ),
            'new_item'              => __( 'New Help Document', 'admin-help-docs' ),
            'edit_item'             => __( 'Edit Help Document', 'admin-help-docs' ),
            'update_item'           => __( 'Update Help Document', 'admin-help-docs' ),
            'view_item'             => __( 'View Help Document', 'admin-help-docs' ),
            'view_items'            => __( 'View Help Documents', 'admin-help-docs' ),
            'search_items'          => __( 'Search Help Documents', 'admin-help-docs' ),
            'not_found'             => __( 'Not found', 'admin-help-docs' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'admin-help-docs' ),
            'insert_into_item'      => __( 'Insert into help document', 'admin-help-docs' ),
            'uploaded_to_this_item' => __( 'Uploaded to this help document', 'admin-help-docs' ),
            'items_list'            => __( 'Help Document list', 'admin-help-docs' ),
            'items_list_navigation' => __( 'Help Document list navigation', 'admin-help-docs' ),
            'filter_items_list'     => __( 'Filter help document list', 'admin-help-docs' ),
        ];

        // Allow filter for supports and taxonomies
        $supports = apply_filters( 'helpdocs_post_type_supports', [ 'title', 'editor', 'author', 'revisions', 'excerpt' ] );
        $taxonomies = apply_filters( 'helpdocs_post_type_taxonomies', [] );
    
        // Set the CPT args
        $args = [
            'label'                 => __( 'Help Documents', 'admin-help-docs' ),
            'description'           => __( 'Help Documents', 'admin-help-docs' ),
            'labels'                => $labels,
            'supports'              => $supports,
            'taxonomies'            => $taxonomies,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => false,
            'show_in_admin_bar'     => false,
            'show_in_nav_menus'     => false,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'query_var'             => self::$post_type,
            'capability_type'       => 'post',
            'show_in_rest'          => true,
        ];
    
        // Register the CPT
        register_post_type( self::$post_type, $args );
    } // End register_post_type()


    /**
     * Add the header to the top of the admin list page
     *
     * @return void
     */
    public function add_header() {
        $screen = get_current_screen();
        if ( 'edit-' . self::$post_type === $screen->id ) {
            add_action( 'in_admin_header', function() {
                include Bootstrap::path( 'inc/header.php' );
            } );
        }
    } // End add_header()


    /**
     * Add a search box to the subheader on the folders page
     *
     * @param string $current_tab The current admin tab
     * @return void
     */
    public function render_search_box( string $current_tab ) {
        if ( $current_tab !== 'manage' ) {
            return;
        }

        $search_value = sanitize_text_field( wp_unslash( $_GET[ 's' ] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        ?>
        <form method="get" class="helpdocs-posttype-search">
            <?php foreach ( $_GET as $key => $value ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                if ( in_array( $key, [ 's', 'action', 'paged' ], true ) ) continue;
            ?>
                <input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" />
            <?php endforeach; ?>

            <input type="search"
                name="s"
                value="<?php echo esc_attr( $search_value ); ?>"
                placeholder="<?php echo esc_attr__( 'Search Docs', 'admin-help-docs' ); ?>"
                class="helpdocs-search-input" />

            <input type="submit"
                class="helpdocs-button"
                value="<?php echo esc_attr__( 'Search', 'admin-help-docs' ); ?>" />

            <a href="<?php
                $clear_url = remove_query_arg( 's' );
                echo esc_url( $clear_url );
            ?>" class="helpdocs-button">
                <?php echo esc_html__( 'Clear', 'admin-help-docs' ); ?>
            </a>
        </form>
        <?php
    } // End render_search_box()


    /**
     * Meta box
     *
     * @return void
     */
    public function meta_boxes() {
        add_meta_box( 
            'helpdocs_locations',
            __( 'Location', 'admin-help-docs' ),
            [ $this, 'meta_box_content' ],
            self::$post_type,
            'advanced',
			'high'
        );
    } // End meta_boxes()


    /**
     * Rename excerpt meta box as description
     *
     * @param string $translation
     * @param string $original
     * @return string
     */
    public function excerpt_meta_box( $translation, $original ) {
        // Make sure we are only looking at our post type
        global $post_type;
        if ( $post_type != self::$post_type ) {
            return $translation;
        }

        // Update the box
        if ( 'Excerpt' == $original ) {
            return __( 'Description', 'admin-help-docs' );
        } elseif ( false !== strpos( $original, 'Excerpts are optional hand-crafted summaries of your' ) ) {
            return __( 'This is only used to summarize the document for the manage documents list table. If left blank, it will show first couple of lines of the content.', 'admin-help-docs' );
        }
        return $translation;
    } // End excerpt_meta_box()


    /**
     * Meta box content
     *
     * @param object $post
     * @return void
     */
    public function meta_box_content( $post ) {
        wp_nonce_field( 'help_location_nonce', 'help_location_nonce' );

        $locations = get_post_meta( $post->ID, 'helpdocs_locations', true );

        if ( 'auto-draft' === $post->post_status && isset( $_GET[ 'site_location' ] ) ) { // phpcs:ignore
            $locations = [
                [
                    'site_location' => sanitize_text_field( wp_unslash( $_GET[ 'site_location' ] ) ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                    'page_location' => 'top', // Default to top
                    'custom'        => isset( $_GET[ 'custom_url' ] ) ? esc_url_raw( wp_unslash( $_GET[ 'custom_url' ] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                    'post_types'    => isset( $_GET[ 'slpt' ] ) ? [ sanitize_text_field( wp_unslash( $_GET[ 'slpt' ] ) ) ] : [], // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                    'order'         => 0,
                    'toc'           => false,
                    'css_selector'  => '',
                    'addt_params'   => true,
                ]
            ];
        }

        if ( ! is_array( $locations ) || empty( $locations ) ) {
            // Backward compatibility: use legacy single-location fields
            $meta = get_post_meta( $post->ID );

            $locations = [
                [
                    'site_location'     => $meta[ 'helpdocs_site_location' ][ 0 ] ?? '',
                    'page_location'     => $meta[ 'helpdocs_page_location' ][ 0 ] ?? '',
                    'custom'            => $meta[ 'helpdocs_custom' ][ 0 ] ?? '',
                    'addt_params'       => ! empty( $meta[ 'helpdocs_addt_params' ][ 0 ] ),
                    'post_types'        => isset( $meta[ 'helpdocs_post_types' ][ 0 ] ) ? Helpers::normalize_meta_array( $meta[ 'helpdocs_post_types' ][ 0 ] ) : [],
                    'order'             => isset( $meta[ 'helpdocs_order' ][ 0 ] ) ? intval( $meta[ 'helpdocs_order' ][ 0 ] ) : 0,
                    'toc'               => isset( $meta[ 'helpdocs_toc' ][ 0 ] ) ? filter_var( $meta[ 'helpdocs_toc' ][ 0 ], FILTER_VALIDATE_BOOLEAN ) : false,
                    'css_selector'      => $meta[ 'helpdocs_css_selector' ][ 0 ] ?? '',
                ]
            ];
        }

        // Global fields
        $meta       = get_post_meta( $post->ID );
        $api        = $meta[ 'helpdocs_api' ][ 0 ] ?? '';
        $view_roles = isset( $meta[ 'helpdocs_view_roles' ][ 0 ] ) ? Helpers::normalize_meta_array( $meta[ 'helpdocs_view_roles' ][ 0 ] ) : [];
        $roles      = Helpers::get_role_options();

        $all_site_locations = self::site_locations();
        $all_page_locations = self::page_locations();
        $all_post_types     = self::post_types();
        ?>
        <!-- Repeater -->
        <div id="helpdocs-location-repeater">
            <?php foreach ( $locations as $i => $location ) :
                $site_location         = $location[ 'site_location' ] ?? '';
                $site_location_decoded = base64_decode( $site_location );
                $page_location         = $location[ 'page_location' ] ?? '';
                $custom                = $location[ 'custom' ] ?? '';
                $addt_params           = ! empty( $location[ 'addt_params' ] );
                $post_types            = $location[ 'post_types' ] ?? [];
                $order                 = $location[ 'order' ] ?? 0;
                $toc                   = ! empty( $location[ 'toc' ] );
                $css_selector          = $location[ 'css_selector' ] ?? '';

                // Determine which fields to show
                $fields_to_show = [];
                if ( isset( $all_site_locations[ $site_location_decoded ][ 'fields' ] ) ) {
                    foreach ( $all_site_locations[ $site_location_decoded ][ 'fields' ] as $f ) {
                        if ( strpos( $f, ':' ) !== false ) {
                            list( $controller, $controller_value, $dependent ) = explode( ':', $f );
                            if ( ( $location[ $controller ] ?? '' ) === $controller_value ) {
                                $fields_to_show[] = $dependent;
                            }
                        } else {
                            $fields_to_show[] = $f;
                        }
                    }
                }
            ?>
            <div class="helpdocs-location-row" style="margin-bottom:15px; padding:10px; border:1px solid #ddd;">
                <div class="helpdocs-row-main">

                    <!-- Site Location -->
                    <?php $id_site = 'helpdocs_locations_' . $i . '_site_location'; ?>
                    <label for="<?php echo esc_attr( $id_site ); ?>"><?php esc_html_e( 'Site Location:', 'admin-help-docs' ); ?></label>
                    <select name="helpdocs_locations[ <?php echo esc_attr( $i ); ?> ][site_location]" id="<?php echo esc_attr( $id_site ); ?>">
                        <option value=""><?php esc_html_e( '-- None --', 'admin-help-docs' ); ?></option>
                        <?php foreach ( $all_site_locations as $key_l => $info ) :
                            $encoded = base64_encode( $key_l );
                        ?>
                            <option value="<?php echo esc_attr( $encoded ); ?>" <?php selected( $encoded, $site_location ); ?>>
                                <?php echo esc_html( $info[ 'label' ] ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <?php if ( $i > 0 ) : ?>
                        <button type="button" class="remove-location button" title="Remove Location"><span class="dashicons dashicons-trash"></span></button>
                    <?php endif; ?>

                    <!-- Page Location -->
                    <?php $id_page = 'helpdocs_locations_' . $i . '_page_location'; ?>
                    <label for="<?php echo esc_attr( $id_page ); ?>" style="display:<?php echo in_array( 'page_location', $fields_to_show ) ? 'block' : 'none'; ?>;">
                        <?php esc_html_e( 'Page Location:', 'admin-help-docs' ); ?>
                    </label>
                    <select name="helpdocs_locations[ <?php echo esc_attr( $i ); ?> ][page_location]" id="<?php echo esc_attr( $id_page ); ?>" style="display:<?php echo in_array( 'page_location', $fields_to_show ) ? 'block' : 'none'; ?>;">
                        <?php foreach ( $all_page_locations as $key_lop => $lop ) : ?>
                            <option value="<?php echo esc_attr( $key_lop ); ?>" <?php selected( $key_lop, $page_location ); ?>>
                                <?php echo esc_html( $lop ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Element CSS Selector -->
                    <?php $id_css_selector = 'helpdocs_locations_' . $i . '_css_selector'; ?>
                    <label for="<?php echo esc_attr( $id_css_selector ); ?>" style="display:<?php echo in_array( 'css_selector', $fields_to_show ) ? 'block' : 'none'; ?>;">
                        <?php esc_html_e( 'Element CSS Selector:', 'admin-help-docs' ); ?>
                    </label>
                    <input type="text" name="helpdocs_locations[ <?php echo esc_attr( $i ); ?> ][css_selector]" id="<?php echo esc_attr( $id_css_selector ); ?>" value="<?php echo esc_attr( $css_selector ); ?>" style="display:<?php echo in_array( 'css_selector', $fields_to_show ) ? 'inline-block' : 'none'; ?>;">

                    <!-- Order -->
                    <?php $id_order = 'helpdocs_locations_' . $i . '_order'; ?>
                    <label for="<?php echo esc_attr( $id_order ); ?>" style="display:<?php echo in_array( 'order', $fields_to_show ) ? 'block' : 'none'; ?>;">
                        <?php esc_html_e( 'Menu Order:', 'admin-help-docs' ); ?>
                    </label>
                    <input type="number" name="helpdocs_locations[ <?php echo esc_attr( $i ); ?> ][order]" id="<?php echo esc_attr( $id_order ); ?>" value="<?php echo esc_attr( $order ); ?>" style="display:<?php echo in_array( 'order', $fields_to_show ) ? 'inline-block' : 'none'; ?>;">

                    <!-- TOC -->
                    <?php $id_toc = 'helpdocs_locations_' . $i . '_toc'; ?>
                    <label style="display:<?php echo in_array( 'toc', $fields_to_show ) ? 'block' : 'none'; ?>;">
                        <input type="checkbox" name="helpdocs_locations[ <?php echo esc_attr( $i ); ?> ][toc]" id="<?php echo esc_attr( $id_toc ); ?>" value="1" <?php checked( $toc ); ?>>
                        <?php esc_html_e( 'Add to Dashboard Table of Contents', 'admin-help-docs' ); ?>
                    </label>
                    
                </div>

                <!-- Function Example -->
                <?php $id_function = 'helpdocs_locations_' . $i . '_function_example'; ?>
                <span class="helpdocs-function-example info" id="<?php echo esc_attr( $id_function ); ?>" style="display:<?php echo in_array( 'function_example', $fields_to_show ) ? 'block' : 'none'; ?>;">
                    <?php esc_html_e( 'Developers can use this function to display a specific help document by its ID:', 'admin-help-docs' ); ?> <code>admin_help_doc(<?php echo esc_attr( $post->ID ); ?>);</code>
                </span>

                <!-- Admin Bar Tips -->
                <?php $id_function = 'helpdocs_locations_' . $i . '_admin_bar_tips'; ?>
                <span class="helpdocs-admin-bar-tips info" id="<?php echo esc_attr( $id_function ); ?>" style="display:<?php echo in_array( 'admin_bar_tips', $fields_to_show ) ? 'block' : 'none'; ?>;">
                    <?php esc_html_e( ' To add a quick link to the admin bar, set the title of this help doc to the label and add the link by itself to the content.', 'admin-help-docs' ); ?>
                </span>

                <!-- Dashboard Warning -->
                <?php $id_dashboard_warning = 'helpdocs_locations_' . $i . '_dashboard_warning'; ?>
                <span class="helpdocs-dashboard-warning warning" id="<?php echo esc_attr( $id_dashboard_warning ); ?>" style="display:<?php echo in_array( 'dashboard_warning', $fields_to_show ) ? 'block' : 'none'; ?>;">
                    <?php esc_html_e( 'You must enable "Replace WordPress Dashboard with a Help Doc" in Settings for this to show up. Warning: Replacing the WordPress dashboard entirely may affect other plugins and functionality. No other widgets or dashboard elements will be displayed.', 'admin-help-docs' ); ?>
                </span>

                <!-- Custom URL -->
                <div class="helpdocs-custom-url" style="display:<?php echo in_array( 'custom', $fields_to_show ) ? 'block' : 'none'; ?>;">
                    <?php $id_custom = 'helpdocs_locations_' . $i . '_custom'; ?>
                    <label class="helpdocs-custom-label" for="<?php echo esc_attr( $id_custom ); ?>">
                        <?php esc_html_e( 'Custom URL:', 'admin-help-docs' ); ?>
                    </label>
                    <input class="helpdocs-custom" type="text" name="helpdocs_locations[ <?php echo esc_attr( $i ); ?> ][custom]" id="<?php echo esc_attr( $id_custom ); ?>" value="<?php echo esc_attr( $custom ); ?>" style="width:60%; display:<?php echo in_array( 'custom', $fields_to_show ) ? 'inline-block' : 'none'; ?>;">

                    <!-- Ignore Params -->
                    <?php $id_addt = 'helpdocs_locations_' . $i . '_addt_params'; ?>
                    <label style="display:<?php echo in_array( 'addt_params', $fields_to_show ) ? 'block' : 'none'; ?>;">
                        <input type="checkbox" name="helpdocs_locations[ <?php echo esc_attr( $i ); ?> ][addt_params]" id="<?php echo esc_attr( $id_addt ); ?>" value="1" <?php checked( $addt_params ); ?>>
                        <?php esc_html_e( 'Ignore Additional Query String Parameters', 'admin-help-docs' ); ?>
                    </label>
                </div>

                <!-- Post Types -->
                <?php $id_post_types = 'helpdocs_locations_' . $i . '_post_types'; ?>
                <div id="<?php echo esc_attr( $id_post_types ); ?>" class="helpdocs-post-types" style="display:<?php echo in_array( 'post_types', $fields_to_show ) ? 'block' : 'none'; ?>;">
                    <div class="helpdocs-checkboxes">
                        <?php foreach ( $all_post_types as $pt ) :
                            $pt_obj = get_post_type_object( $pt );
                            if ( ! $pt_obj || ! $pt_obj->show_ui ) continue;
                        ?>
                            <label>
                                <input type="checkbox" name="helpdocs_locations[ <?php echo esc_attr( $i ); ?> ][post_types][]" value="<?php echo esc_attr( $pt ); ?>" <?php checked( in_array( $pt, $post_types ) ); ?>>
                                <?php echo esc_html( $pt_obj->labels->name ); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
            </div>
            <?php endforeach; ?>
        </div>

        <button type="button" id="add-location" class="button button-primary"><?php esc_html_e( 'Add Location', 'admin-help-docs' ); ?></button>

        <!-- -----------------------------
            Global Fields
            ----------------------------- -->

        <div class="helpdocs-global-settings">
            
            <!-- API -->
            <div class="helpdocs-api">
                <?php $id_api = 'helpdocs_api'; ?>
                <label for="<?php echo esc_attr( $id_api ); ?>"><?php esc_html_e( 'Allow Public:', 'admin-help-docs' ); ?></label>
                <select name="helpdocs_api" id="<?php echo esc_attr( $id_api ); ?>">
                    <?php
                        $get_default_api    = get_option( 'helpdocs_api' );
                        $default_api_choice = $get_default_api ? 'yes' : 'no';
                        $api_choices        = [
                            'default' => sprintf( __( 'Default ( %s )', 'admin-help-docs' ), ucwords( $default_api_choice ) ),
                            'no'      => __( 'No', 'admin-help-docs' ),
                            'yes'     => __( 'Yes', 'admin-help-docs' )
                        ];
                        foreach ( $api_choices as $key_api => $a ) :
                    ?>
                        <option value="<?php echo esc_attr( $key_api ); ?>" <?php selected( $key_api, $api ? $api : 'default' ); ?>>
                            <?php echo esc_html( $a ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- View Roles -->
            <div class="helpdocs-view-roles">
                <label><?php esc_html_e( 'Viewable By Roles:', 'admin-help-docs' ); ?></label>
                <div class="helpdocs-checkboxes">
                    <?php foreach ( $roles as $role ) : ?>
                        <label>
                            <input type="checkbox" name="helpdocs_view_roles[]" value="<?php echo esc_attr( $role[ 'value' ] ); ?>" <?php checked( in_array( $role[ 'value' ], $view_roles ) ); ?>>
                            <?php echo esc_html( $role[ 'label' ] ); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
        <?php
    } // End meta_box_content()


    /**
     * Get menu item url
     * Snippet courtesy of Mighty Minnow
     * https://www.mightyminnow.com/2013/12/how-to-get-urls-for-wordpress-admin-menu-items/
     *
     * @param [type] $menu_item_file
     * @param boolean $submenu_as_parent
     * @return string
     */
    public static function get_admin_menu_item_url( $menu_item_file, $submenu_as_parent = true ) {
        global $menu, $submenu, $self, $typenow;
    
        $admin_is_parent = false;
        $item = '';
        $submenu_item = '';
        $url = '';
    
        // 1. Check if top-level menu item
        foreach( $menu as $key => $menu_item ) {
            if ( array_keys( $menu_item, $menu_item_file, true ) ) {
                $item = $menu[ $key ];
            }
    
            if ( $submenu_as_parent && ! empty( $submenu_item ) ) {
                $menu_hook = get_plugin_page_hook( $submenu_item[2], $item[2] );
                $menu_file = $submenu_item[2];
    
                if ( false !== ( $pos = strpos( $menu_file, '?' ) ) )
                    $menu_file = substr( $menu_file, 0, $pos );
                if ( ! empty( $menu_hook ) || ( ( 'index.php' != $submenu_item[2] ) && file_exists( WP_PLUGIN_DIR . "/$menu_file" ) && ! file_exists( ABSPATH . "/wp-admin/$menu_file" ) ) ) {
                    $admin_is_parent = true;
                    $url = 'admin.php?page=' . $submenu_item[2];
                } else {
                    $url = $submenu_item[2];
                }
            }
    
            elseif ( ! empty( $item[2] ) && current_user_can( $item[1] ) ) {
                $menu_hook = get_plugin_page_hook( $item[2], 'admin.php' );
                $menu_file = $item[2];
    
                if ( false !== ( $pos = strpos( $menu_file, '?' ) ) )
                    $menu_file = substr( $menu_file, 0, $pos );
                if ( ! empty( $menu_hook ) || ( ( 'index.php' != $item[2] ) && file_exists( WP_PLUGIN_DIR . "/$menu_file" ) && ! file_exists( ABSPATH . "/wp-admin/$menu_file" ) ) ) {
                    $admin_is_parent = true;
                    $url = 'admin.php?page=' . $item[2];
                } else {
                    $url = $item[2];
                }
            }
        }
    
        // 2. Check if sub-level menu item
        if ( ! $item ) {
            $sub_item = '';
            foreach( $submenu as $top_file => $submenu_items ) {
    
                // Reindex $submenu_items
                $submenu_items = array_values( $submenu_items );
    
                foreach( $submenu_items as $key => $submenu_item ) {
                    if ( array_keys( $submenu_item, $menu_item_file ) ) {
                        $sub_item = $submenu_items[ $key ];
                        break;
                    }
                }					
    
                if ( ! empty( $sub_item ) )
                    break;
            }
    
            // Get top-level parent item
            foreach( $menu as $key => $menu_item ) {
                if ( array_keys( $menu_item, $top_file, true ) ) {
                    $item = $menu[ $key ];
                    break;
                }
            }
    
            // If the $menu_item_file parameter doesn't match any menu item, return false
            if ( ! $sub_item )
                return false;
    
            // Get URL
            $menu_file = $item[2];
    
            if ( false !== ( $pos = strpos( $menu_file, '?' ) ) )
                $menu_file = substr( $menu_file, 0, $pos );
    
            // Handle current for post_type=post|page|foo pages, which won't match $self.
            // $self_type = ! empty( $typenow ) ? $self . '?post_type=' . $typenow : 'nothing';
            $menu_hook = get_plugin_page_hook( $sub_item[2], $item[2] );
    
            $sub_file = $sub_item[2];
            if ( false !== ( $pos = strpos( $sub_file, '?' ) ) )
                $sub_file = substr($sub_file, 0, $pos);
    
            if ( ! empty( $menu_hook ) || ( ( 'index.php' != $sub_item[2] ) && file_exists( WP_PLUGIN_DIR . "/$sub_file" ) && ! file_exists( ABSPATH . "/wp-admin/$sub_file" ) ) ) {
                // If admin.php is the current page or if the parent exists as a file in the plugins or admin dir
                if ( ( ! $admin_is_parent && file_exists( WP_PLUGIN_DIR . "/$menu_file" ) && ! is_dir( WP_PLUGIN_DIR . "/{$item[2]}" ) ) || file_exists( $menu_file ) )
                    $url = add_query_arg( array( 'page' => $sub_item[2] ), $item[2] );
                else
                    $url = add_query_arg( array( 'page' => $sub_item[2] ), 'admin.php' );
            } else {
                $url = $sub_item[2];
            }
        }
    
        return esc_url( $url );
    } // End get_admin_menu_item_url()


    /**
     * Save the post data
     *
     * @param int $post_id
     * @return void
     */
    public function save_post( $post_id ) {
        // Check nonce and permissions
        if ( ! Helpers::can_save_post( self::$post_type, 'help_location_nonce', 'help_location_nonce' ) ) {
            return;
        }

        $all_site_locations = self::site_locations();
        $locations_input    = isset( $_POST[ 'helpdocs_locations' ] ) ? wp_unslash( $_POST[ 'helpdocs_locations' ] ) : []; // phpcs:ignore
        $locations          = [];

        if ( is_array( $locations_input ) ) {
            foreach ( $locations_input as $loc ) {
                if ( empty( $loc[ 'site_location' ] ) ) {
                    continue;
                }

                $site_key_encoded = sanitize_text_field( $loc[ 'site_location' ] );
                $site_key_decoded = base64_decode( $site_key_encoded );

                if ( ! isset( $all_site_locations[ $site_key_decoded ] ) ) {
                    continue;
                }

                $clean          = [ 'site_location' => $site_key_encoded ];
                $allowed_fields = $all_site_locations[ $site_key_decoded ][ 'fields' ] ?? [];

                foreach ( $allowed_fields as $field_rule ) {
                    $field_name  = $field_rule;
                    $should_save = true;

                    // Handle complex rules (controller:value:dependent)
                    if ( strpos( $field_rule, ':' ) !== false ) {
                        $parts = explode( ':', $field_rule );
                        if ( count( $parts ) === 3 ) {
                            list( $controller, $controller_value, $dependent ) = $parts;
                            $field_name = $dependent;

                            // Only save if the controller field matches the required value
                            $current_val = $loc[ $controller ] ?? '';
                            if ( $current_val !== $controller_value ) {
                                $should_save = false;
                            }
                        } else {
                            $field_name = end( $parts );
                        }
                    }

                    if ( ! $should_save ) {
                        continue;
                    }

                    // If the field isn't in POST, skip it (except post_types which might be empty)
                    if ( ! isset( $loc[ $field_name ] ) && $field_name !== 'post_types' ) {
                        continue;
                    }

                    switch ( $field_name ) {
                        case 'order':
                            $clean[ $field_name ] = intval( $loc[ $field_name ] );
                            break;

                        case 'toc':
                        case 'addt_params':
                            $clean[ $field_name ] = ! empty( $loc[ $field_name ] );
                            break;

                        case 'post_types':
                            $pts = isset( $loc[ 'post_types' ] ) ? (array) $loc[ 'post_types' ] : [];
                            $clean[ $field_name ] = array_map( 'sanitize_key', $pts );
                            break;

                        case 'custom':
                            $clean[ $field_name ] = esc_url_raw( $loc[ $field_name ] );
                            break;

                        case 'page_location':
                            $clean[ $field_name ] = sanitize_key( $loc[ $field_name ] );
                            break;

                        case 'css_selector':
                            $clean[ $field_name ] = sanitize_text_field( $loc[ $field_name ] );
                            break;

                        default:
                            $clean[ $field_name ] = sanitize_text_field( $loc[ $field_name ] );
                            break;
                    }
                }
                $locations[] = $clean;
            }
        }

        update_post_meta( $post_id, 'helpdocs_locations', $locations );

        // Global fields
        $view_roles = isset( $_POST[ 'helpdocs_view_roles' ] ) ? array_map( 'sanitize_key', wp_unslash( $_POST[ 'helpdocs_view_roles' ] ) ) : []; // phpcs:ignore
        update_post_meta( $post_id, 'helpdocs_view_roles', $view_roles );

        $api = isset( $_POST[ 'helpdocs_api' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'helpdocs_api' ] ) ) : 'default'; // phpcs:ignore
        update_post_meta( $post_id, 'helpdocs_api', $api );

        // Cleanup legacy meta fields: these are now handled within the 'helpdocs_locations' repeater array.
        $legacy_keys = [
            'site_location',
            'page_location',
            'custom',
            'addt_params',
            'post_types',
            'order',
            'toc',
            'css_selector'
        ];

        foreach ( $legacy_keys as $key ) {
            delete_post_meta( $post_id, 'helpdocs_' . $key );
        }

        // Flush the cache so changes show immediately
        Helpers::flush_location_cache();
    } // End save_post()


    /**
     * Delete post callback to flush cache
     *
     * @param int $post_id
     * @return void
     */
    public function delete_post( $post_id ) {
        if ( get_post_type( $post_id ) !== self::$post_type ) {
            return;
        }

        // Flush the cache so changes show immediately
        Helpers::flush_location_cache();
    } // End delete_post()


    /**
     * Admin columns
     *
     * @param array $columns
     * @return array
     */
    public function admin_columns( $columns ) {
        $columns[ 'helpdocs_desc' ]           = __( 'Description', 'admin-help-docs' );
        $columns[ 'helpdocs_site_locations' ] = __( 'Site Locations', 'admin-help-docs' );
        $columns[ 'helpdocs_allow_public' ]   = __( 'Public', 'admin-help-docs' );
        return $columns;
    } // End admin_columns()


    /**
     * Admin column content
     *
     * @param string $column
     * @param int $post_id
     * @return void
     */
    public function admin_column_content( $column, $post_id ) {
        // Description
        if ( 'helpdocs_desc' === $column ) {
            echo esc_html( $this->truncate( get_the_excerpt( $post_id ), 140 ) );
        }

        // Consolidated Locations Column
        if ( 'helpdocs_site_locations' === $column ) {
            $locations = get_post_meta( $post_id, 'helpdocs_locations', true );

            // Back-compat check: if repeater is empty, try to build a single location from legacy meta
            if ( ! is_array( $locations ) || empty( $locations ) ) {
                $site_val = get_post_meta( $post_id, 'helpdocs_site_location', true );
                if ( ! empty( $site_val ) ) {
                    $locations = [
                        [
                            'site_location' => $site_val,
                            'page_location' => get_post_meta( $post_id, 'helpdocs_page_location', true ),
                            'custom'        => get_post_meta( $post_id, 'helpdocs_custom', true ),
                            'post_types'    => Helpers::normalize_meta_array( get_post_meta( $post_id, 'helpdocs_post_types', true ) ),
                            'order'         => get_post_meta( $post_id, 'helpdocs_order', true ),
                            'css_selector'  => get_post_meta( $post_id, 'helpdocs_css_selector', true ),
                        ]
                    ];
                }
            }

            if ( ! is_array( $locations ) || empty( $locations ) ) {
                echo '<span class="description">' . esc_html__( 'No locations set.', 'admin-help-docs' ) . '</span>';
                return;
            }

            echo '<ul style="margin:0; padding:0; list-style:none;">';
            foreach ( $locations as $loc ) {
                $site_location_encoded = $loc[ 'site_location' ] ?? '';
                if ( empty( $site_location_encoded ) ) {
                    continue;
                }

                $label = Helpers::get_admin_page_title_from_url( $site_location_encoded );
                $url = Helpers::get_link_from_site_location( $post_id, $loc );
                
                echo '<li style="margin-bottom: 8px; border-bottom: 1px solid #eee; padding-bottom: 4px;">';
                if ( $url ) {
                    echo '<a href="' . esc_url( $url ) . '" target="_blank"><strong>' .  wp_kses_post( $label ) . '</strong></a>';
                } else {
                    echo '<strong>' .  wp_kses_post( $label ) . '</strong>';
                }

                // Meta detail string
                $details = [];

                // 1. Page Location
                if ( ! empty( $loc[ 'page_location' ] ) ) {
                    $page_loc = ucwords( str_replace( '_', ' ', $loc[ 'page_location' ] ) );
                    if ( $page_loc === 'Contextual' ) {
                        $page_loc .= ' Help Tab';
                    }
                    $details[] = '<em>' . esc_html( $page_loc ) . '</em>';
                }

                // 2. Custom URL
                if ( ! empty( $loc[ 'custom' ] ) ) {
                    $details[] = '<code style="font-size:10px;">' . esc_html( $loc[ 'custom' ] ) . '</code>';
                }

                // 3. Post Types
                if ( ! empty( $loc[ 'post_types' ] ) && is_array( $loc[ 'post_types' ] ) ) {
                    $pt_labels = [];
                    foreach ( $loc[ 'post_types' ] as $pt ) {
                        $pt_obj = get_post_type_object( $pt );
                        $pt_labels[] = $pt_obj ? $pt_obj->labels->name : $pt;
                    }
                    $details[] = 'Types: ' . esc_html( implode( ', ', $pt_labels ) );
                }

                // 4. CSS Selector
                if ( ! empty( $loc[ 'css_selector' ] ) ) {
                    $details[] = 'Selector: <code>' . esc_html( $loc[ 'css_selector' ] ) . '</code>';
                }

                // 5. Order
                if ( isset( $loc[ 'order' ] ) && $loc[ 'order' ] !== '' ) {
                    $details[] = 'Order: ' . intval( $loc[ 'order' ] );
                }

                if ( ! empty( $details ) ) {
                    echo '<br><span class="description" style="font-size:11px;">' . wp_kses_post( implode( ' | ', $details ) ) . '</span>';
                }

                echo '</li>';
            }
            echo '</ul>';
        }

        if ( 'helpdocs_allow_public' === $column ) {
            $api = sanitize_key( get_post_meta( $post_id, 'helpdocs_api', true ) );
            if ( empty( $api ) || 'default' === $api ) {
                $default_api = get_option( 'helpdocs_api' );
                $api = $default_api ? __( 'Default (Yes)', 'admin-help-docs' ) : __( 'Default (No)', 'admin-help-docs' );
            } else {
                $api = ucwords( $api );
            }
            echo esc_html( $api );
        }
    } // End admin_column_content()


    /**
     * Strip admin menu counters from a label
     *
     * @param string $label
     * @return string
     */
    private static function strip_admin_menu_counters( $label ) {
        if ( !is_string( $label ) ) {
            return $label;
        }

        // Remove update badges (nested spans included)
        $label = preg_replace( '/<span[^>]*class="[^"]*\b(update-plugins|awaiting-mod|count-[0-9]+|bubble)\b[^"]*"[^>]*>.*?<\/span>/i', '', $label );

        // Remove any remaining HTML
        $label = wp_strip_all_tags( $label );

        // Decode entities then trim
        $label = trim( html_entity_decode( $label, ENT_QUOTES, 'UTF-8' ) );

        // Remove trailing counts like "85", "(85)", "[85]", "{85}"
        $label = preg_replace( '/(?:\s*[\(\[\{]\s*\d+\s*[\)\]\}])|\s*\d+\s*$/', '', $label );

        return trim( $label );
    } // End strip_admin_menu_counters()


    /**
     * Trancate string with ellipses
     *
     * @param string $string
     * @param int $length
     * @return string
     */
    public function truncate( $string, $length ) {
        if ( strlen( $string ) > $length ) {
            return substr( $string, 0, $length - 3 ).'...';
        }
        return $string;
    } // End truncate()

    
    /**
     * Disable Gutenberg while allowing rest
     *
     * @param [type] $current_status
     * @param [type] $post_type
     * @return void
     */
    public function disable_gutenberg( $current_status, $post_type ) {
        $disabled_post_types = [ self::$post_type ];
        if ( in_array( $post_type, $disabled_post_types, true ) ) {
            $current_status = false;
        }
        return $current_status;
    } // End disable_gutenberg()


    /**
     * Get the location rules
     *
     * @return array
     */
    public static function location_rules( $all_site_locations ) {
        $rules = [];

        foreach ( $all_site_locations as $loc_key => $loc_info ) {
            $fields = [];

            if ( isset( $loc_info[ 'fields' ] ) ) {
                foreach ( $loc_info[ 'fields' ] as $f ) {
                    if ( strpos( $f, ':' ) !== false ) {
                        $parts = explode( ':', $f );
                        // Handle controller:value:dependent (and more)
                        $fields[] = [
                            'controller'       => $parts[ 0 ],
                            'controller_value' => $parts[ 1 ],
                            'dependent'        => end( $parts ), // Always the last part
                        ];
                    } else {
                        $fields[] = [
                            'field' => $f,
                        ];
                    }
                }
            }

            $rules[ base64_encode( $loc_key ) ] = $fields;
        }

        return $rules;
    } // End location_rules()


    /**
     * Enqueue admin styles.
     */
    public function enqueue_admin_styles() {
        // Only load on our post type edit screen
        global $current_screen;
        if ( ! is_admin() || ! isset( $current_screen ) || $current_screen->id !== self::$post_type || $current_screen->base !== 'post' ) {
            return;
        }

        $text_domain = Bootstrap::textdomain();
        $version = Bootstrap::script_version();
        
        // CSS
        wp_enqueue_style(
            $text_domain . '-post-type-' . self::$post_type,
            Bootstrap::url( 'inc/css/post-type-' . self::$post_type . '.css' ),
            [],
            $version
        );

        // JS
        wp_enqueue_script(
            $text_domain . '-post-type-' . self::$post_type,
            Bootstrap::url( 'inc/js/post-type-' . self::$post_type . '.js' ),
            [ 'jquery' ],
            $version,
            true
        );

        wp_localize_script( $text_domain . '-post-type-' . self::$post_type, 'helpdocs_' . str_replace( '-', '_', self::$post_type ), [
            'location_rules' => self::location_rules( self::site_locations() ),
        ] );
    } // End enqueue_admin_styles()

}


HelpDocs::instance();