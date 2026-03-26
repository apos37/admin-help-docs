<?php
/**
 * Help Docs folders taxonomy (Folders Tab)
 */

namespace PluginRx\AdminHelpDocs;

if ( ! defined( 'ABSPATH' ) ) exit;

class Folders {

    /**
     * Taxonomy slug
     *
     * @var string
     */
    public static $taxonomy = 'help-docs-folder';


    /**
     * The single instance of the class
     *
     * @var self|null
     */
    private static ?Folders $instance = null;


    /**
     * Get the singleton instance
     *
     * @return self
     */
    public static function instance() : self {
        return self::$instance ??= new self();
    } // End instance()


    /**
     * Folders constructor.
     *
     * Private to enforce singleton pattern.
     */
    private function __construct() {

        // Register the taxonomy
        add_action( 'init', [ $this, 'register_taxonomy' ] );

        // Add the header to the top of the admin list page
        add_action( 'load-edit-tags.php', [ $this, 'add_header' ] );

        // Move the search box to the subheader
        add_action( 'helpdocs_subheader_right', [ $this, 'render_search_box' ] );
        
    } // End __construct()


    /**
     * Register taxonomy
     */
    public function register_taxonomy() {   
        // Create the labels
        $labels = [
            'name'              => _x( 'Folder', 'Taxonomy General Name', 'admin-help-docs' ),
            'singular_name'     => _x( 'Folder', 'Taxonomy Singular Name', 'admin-help-docs' ),
            'search_items'      => __( 'Search Folders', 'admin-help-docs' ),
            'all_items'         => __( 'Add to Folder', 'admin-help-docs' ),
            'parent_item'       => __( 'Parent Folder', 'admin-help-docs' ),
            'parent_item_colon' => __( 'Parent Folder: ', 'admin-help-docs' ),
            'edit_item'         => __( 'Edit Folder', 'admin-help-docs' ),
            'update_item'       => __( 'Update Folder', 'admin-help-docs' ),
            'add_new_item'      => __( 'Add New Folder', 'admin-help-docs' ),
            'new_item_name'     => __( 'New Folder Name', 'admin-help-docs' ),
            'menu_name'         => __( 'Folders', 'admin-help-docs' ),
        ]; 	
    
        // Register it as a new taxonomy
        register_taxonomy( self::$taxonomy, HelpDocs::$post_type, [
            'hierarchical'       => true,
            'labels'             => $labels,
            'show_ui'            => true,
            'show_in_rest'       => false,
            'show_admin_column'  => true,
            'show_in_quick_edit' => true,
            'query_var'          => true,
            'public'             => false,
            'rewrite'            => [ 'slug' => self::$taxonomy, 'with_front' => false ],
        ] );
    } // End register_taxonomy()


    /**
     * Add the header to the top of the admin list page
     *
     * @return void
     */
    public function add_header() {
        $screen = get_current_screen();
        if ( 'edit-' . self::$taxonomy === $screen->id ) {
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
        if ( $current_tab !== 'folders' ) {
            return;
        }

        $screen = get_current_screen();
        if ( $screen->id !== 'edit-' . self::$taxonomy || $screen->base !== 'edit-tags' ) {
            return;
        }

        $search_value = sanitize_text_field( wp_unslash( $_GET[ 's' ] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        ?>
        <form method="get" class="helpdocs-tax-search">
            <?php foreach ( $_GET as $key => $value ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                if ( in_array( $key, [ 's', 'action', 'paged' ], true ) ) continue;
            ?>
                <input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" />
            <?php endforeach; ?>

            <input type="search"
                name="s"
                value="<?php echo esc_attr( $search_value ); ?>"
                placeholder="<?php echo esc_attr__( 'Search Folders', 'admin-help-docs' ); ?>"
                class="helpdocs-search-input" />

            <input type="submit"
                class="helpdocs-button"
                value="<?php echo esc_attr__( 'Search', 'admin-help-docs' ); ?>" />
        </form>
        <?php
    } // End render_search_box()

}


Folders::instance();