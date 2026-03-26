<?php
/**
 * Help Doc Imports post type (Imports Tab)
 */

namespace PluginRx\AdminHelpDocs;

if ( ! defined( 'ABSPATH' ) ) exit;

class Imports {

    /**
     * Post type slug
     *
     * @var string
     */
    public static $post_type = 'help-doc-imports';


    /**
     * The single instance of the class
     *
     * @var self|null
     */
    private static ?Imports $instance = null;


    /**
     * Get the singleton instance
     *
     * @return self
     */
    public static function instance() : self {
        return self::$instance ??= new self();
    } // End instance()


    /**
     * Imports constructor.
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

        // Update edit links to go to the import editor
        add_filter( 'get_edit_post_link', [ $this, 'redirect_import_edit_link' ], 10, 2 );
        add_action( 'current_screen', [ $this, 'redirect_new_post_screen' ] );

        // Add admin columns
        add_filter( 'manage_' . self::$post_type . '_posts_columns', [ $this, 'admin_columns' ] );
        add_action( 'manage_' . self::$post_type . '_posts_custom_column', [ $this, 'admin_column_content' ], 10, 2 );

        // Remove Quick Edit
        add_filter( 'post_row_actions', [ $this, 'remove_quick_edit' ], 10, 2 );

        // Remove Bulk Edit
        add_filter( 'bulk_actions-edit-' . self::$post_type, [ $this, 'remove_bulk_edit' ] );

        // Enqueue back-end styles
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_styles' ] );
        
        // AJAX toggle status
        add_action( 'wp_ajax_helpdocs_toggle_import_status', [ $this, 'ajax_toggle_status' ] );
        
    } // End __construct()


    /**
     * Register the post type
     */
    public function register_post_type() {
        // Set the labels
        $labels = [
            'name'                  => _x( 'Imports', 'Post Type General Name', 'admin-help-docs' ),
            'singular_name'         => _x( 'Import', 'Post Type Singular Name', 'admin-help-docs' ),
            'menu_name'             => __( 'Imports', 'admin-help-docs' ),
            'name_admin_bar'        => __( 'Imports', 'admin-help-docs' ),
            'archives'              => __( 'Import Archives', 'admin-help-docs' ),
            'attributes'            => __( 'Import Attributes', 'admin-help-docs' ),
            'parent_item_colon'     => __( 'Parent Import:', 'admin-help-docs' ),
            'all_items'             => __( 'All Imports', 'admin-help-docs' ),
            'add_new_item'          => __( 'Add New Import', 'admin-help-docs' ),
            'add_new'               => __( 'Add New', 'admin-help-docs' ),
            'new_item'              => __( 'New Import', 'admin-help-docs' ),
            'edit_item'             => __( 'Edit Import', 'admin-help-docs' ),
            'update_item'           => __( 'Update Import', 'admin-help-docs' ),
            'view_item'             => __( 'View Import', 'admin-help-docs' ),
            'view_items'            => __( 'View Imports', 'admin-help-docs' ),
            'search_items'          => __( 'Search Imports', 'admin-help-docs' ),
            'not_found'             => __( 'Not found', 'admin-help-docs' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'admin-help-docs' ),
            'insert_into_item'      => __( 'Insert into import', 'admin-help-docs' ),
            'uploaded_to_this_item' => __( 'Uploaded to this import', 'admin-help-docs' ),
            'items_list'            => __( 'Import list', 'admin-help-docs' ),
            'items_list_navigation' => __( 'Import list navigation', 'admin-help-docs' ),
            'filter_items_list'     => __( 'Filter import list', 'admin-help-docs' ),
        ];

        // Allow filter for supports and taxonomies
        $supports = apply_filters( 'helpdocs_imports_supports', [ 'title', 'excerpt' ] );
        $taxonomies = apply_filters( 'helpdocs_imports_taxonomies', [] );
    
        // Set the CPT args
        $args = [
            'label'                 => __( 'Imports', 'admin-help-docs' ),
            'description'           => __( 'Imports', 'admin-help-docs' ),
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
            'show_in_rest'          => false,
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
        if ( $current_tab !== 'imports' ) {
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
     * Redirect the edit link to the import editor
     *
     * @param string $link The original edit link
     * @param int $post_id The post ID
     * @return string The modified edit link
     */
    public function redirect_import_edit_link( $link, $post_id ) {
        $post = get_post( $post_id );
        if ( $post && $post->post_type === self::$post_type ) {
            return add_query_arg( [ 'id' => $post_id ], Bootstrap::tab_url( 'import' ) );
        }

        return $link;
    } // End redirect_import_edit_link()


    /**
     * If a user manually tries to go to post-new.php for our CPT, 
     * or clicks the "Add New" on the list table, bounce them to our tab.
     */
    public function redirect_new_post_screen() {
        global $pagenow;

        if ( 'post-new.php' === $pagenow && isset( $_GET[ 'post_type' ] ) && sanitize_text_field( wp_unslash( $_GET[ 'post_type' ] ) ) === self::$post_type ) { // phpcs:ignore
            wp_safe_redirect( Bootstrap::tab_url( 'editor' ) );
            exit;
        } 

        if ( 'post.php' === $pagenow && isset( $_GET[ 'post' ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $action = isset( $_GET[ 'action' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'action' ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

            if ( ! empty( $action ) && 'edit' !== $action ) {
                return;
            }
            
            $post_id = isset( $_GET[ 'post' ] ) ? absint( wp_unslash( $_GET[ 'post' ] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $post    = get_post( $post_id );

            if ( $post && $post->post_type === self::$post_type ) {
                wp_safe_redirect( add_query_arg( [ 'id' => $post_id ], Bootstrap::tab_url( 'import' ) ) );
                exit;
            }
        }
    } // End redirect_new_post_screen()


    /**
     * Admin columns
     *
     * @param array $columns
     * @return array
     */
    public function admin_columns( $columns ) {
        $new_columns = [
            'cb'              => $columns[ 'cb' ],
            'helpdocs_status' => __( 'Status', 'admin-help-docs' ),
        ];

        unset( $columns[ 'cb' ], $columns[ 'featured_image' ] );

        $columns[ 'helpdocs_url' ] = __( 'URL', 'admin-help-docs' );
        $columns[ 'helpdocs_all' ] = __( 'Import All?', 'admin-help-docs' );

        return array_merge( $new_columns, $columns );
    } // End admin_columns()


    /**
     * Admin column content
     *
     * @param string $column
     * @param int $post_id
     * @return void
     */
    public function admin_column_content( $column, $post_id ) {
        // Status Toggle
        if ( 'helpdocs_status' === $column ) {
            $status       = get_post_status( $post_id );
            $is_published = ( 'publish' === $status );

            $class = $is_published ? ' is-active' : ' is-inactive';
            
            $label = $is_published ? __( 'Active', 'admin-help-docs' ) : __( 'Inactive', 'admin-help-docs' );

            echo '<button type="button" class="helpdocs-status-toggle ' . esc_attr( $class ) . '" data-id="' . esc_attr( $post_id ) . '">
                <span class="helpdocs-post-status">' . esc_html( $label ) . '</span>
            </button>';
        }
        
        // URL
        if ( 'helpdocs_url' === $column ) {
            $website_url = get_post_meta( $post_id, 'helpdocs_url', true );
            echo $website_url ? esc_url( $website_url ) : '';

            $api_key = get_post_meta( $post_id, 'helpdocs_api_key', true );
            if ( $api_key ) {
                echo '<br><em><small>' . esc_html__( 'API Key Set', 'admin-help-docs' ) . '</small></em>';
            }
        }

        // Import all
        if ( 'helpdocs_all' === $column ) {
            $auto_all = get_post_meta( $post_id, 'helpdocs_all', true );
            $all = $auto_all ? esc_html__( 'Yes', 'admin-help-docs' ) : esc_html__( 'No', 'admin-help-docs' );
            echo esc_html( $all );
        }
    } // End admin_column_content()


    /**
     * Remove Quick Edit from row actions
     *
     * @param array $actions
     * @param \WP_Post $post
     * @return array
     */
    public function remove_quick_edit( $actions, $post ) {
        if ( $post->post_type === self::$post_type ) {
            unset( $actions[ 'inline hide-if-no-js' ] ); // 'inline' is the key for Quick Edit
        }
        return $actions;
    } // End remove_quick_edit()


    /**
     * Remove Bulk Edit from the bulk actions dropdown
     *
     * @param array $actions
     * @return array
     */
    public function remove_bulk_edit( $actions ) {
        unset( $actions[ 'edit' ] );
        return $actions;
    } // End remove_bulk_edit()


    /**
     * Enqueue admin styles.
     */
    public function enqueue_admin_styles() {
        // Only load on our post type edit screen
        global $current_screen;
        if ( ! is_admin() || ! isset( $current_screen ) || $current_screen->id !== 'edit-' . self::$post_type ) {
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
            'nonce'         => wp_create_nonce( 'helpdocs_import_status_nonce' ),
            'active_text'   => __( 'Active', 'admin-help-docs' ),
            'inactive_text' => __( 'Inactive', 'admin-help-docs' ),
        ] );
    } // End enqueue_admin_styles()


    /**
     * Get URL for a specific tab
     *
     * @param string $tab The tab key (e.g., 'main', 'adminmenu', 'import', 'editor')
     * @return string The URL for the specified tab
     */
    public function ajax_toggle_status() {
        check_ajax_referer( 'helpdocs_import_status_nonce', 'nonce' );
        if ( ! Helpers::user_can_edit() ) {
            wp_send_json_error();
        }

        $post_id = isset( $_POST[ 'id' ] ) ? absint( $_POST[ 'id' ] ) : 0;
        if ( ! $post_id ) {
            wp_send_json_error( __( 'Invalid ID', 'admin-help-docs' ) );
        }

        $current_status = get_post_status( $post_id );
        $new_status     = ( 'publish' === $current_status ) ? 'draft' : 'publish';

        $updated = wp_update_post( [
            'ID'          => $post_id,
            'post_status' => $new_status,
        ] );

        if ( is_wp_error( $updated ) ) {
            wp_send_json_error( $updated->get_error_message() );
        }

        wp_send_json_success( [
            'active' => ( 'publish' === $new_status )
        ] );
    } // End ajax_toggle_status()

}


Imports::instance();