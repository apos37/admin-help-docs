<?php
/**
 * Docs Controller
 */

namespace PluginRx\AdminHelpDocs;

if ( ! defined( 'ABSPATH' ) ) exit;

class Controller {

    /**
     * Store the docs for each screen
     *
     * @var array
     */
    private array $screen_docs = [];


    /**
     * The single instance of the class
     *
     * @var self|null
     */
    private static ?Controller $instance = null;


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
        add_action( 'admin_init', [ $this, 'maybe_replace_dashboard' ], 1 );
        add_action( 'current_screen', [ $this, 'render' ] );
    } // End __construct()


    /**
     * Check if the dashboard should be replaced by help docs
     */
    public function maybe_replace_dashboard() {
        global $pagenow;
        if ( 'index.php' !== $pagenow || isset( $_GET[ 'page' ] ) || ! get_option( 'helpdocs_replace_dashboard' ) ) { // phpcs:ignore
            return;
        }
        
        // 1. THE NUCLEAR OPTION: Wipe out all dashboard widgets
        add_action( ( 'wp_dashboard_setup' ), [ $this, 'nuclear_wipe_widgets' ], 9999 );

        // 2. REDIRECT to our page
        $redirect_url = admin_url( ( 'admin.php?page=admin-help-dashboard' ) );
        wp_safe_redirect( $redirect_url );
        exit;
    } // End maybe_replace_dashboard()


    /**
     * Wipes the global meta boxes for the dashboard
     */
    public function nuclear_wipe_widgets() {
        global $wp_meta_boxes;
        $wp_meta_boxes[ 'dashboard' ] = [];
        remove_action( ( 'welcome_panel' ), 'wp_welcome_panel' );
    } // End nuclear_wipe_widgets()


    /**
     * Fetch docs for the current screen and render them in their appropriate locations
     */
    public function render( $screen ) {
        // Ignore if on WP Dashboard or Main Docs Page
        if ( $screen->base === 'dashboard' || Helpers::is_our_screen() ) {
            return;
        }

        // Get all docs for the current screen, organized by location
        $this->screen_docs = Helpers::get_current_screen_docs( $screen );
        if ( empty( $this->screen_docs ) ) {
            return;
        }

        // Render each doc in its appropriate location
        $page_locations = HelpDocs::page_locations();

        foreach ( $page_locations as $key => $label ) {
            if ( empty( $this->screen_docs[ $key ] ) ) {
                continue;
            }

            $class_name = ucfirst( $key );
            $class_fqn  = __NAMESPACE__ . '\\' . $class_name;

            if ( class_exists( $class_fqn ) ) {
                new $class_fqn( $this->screen_docs[ $key ] );
            }
        }
        // Also render any docs that are hooked in
        do_action( 'helpdocs_render_screen_docs', $this->screen_docs );
    } // End bootstrap_docs()
    
    
    /**
     * Prevent cloning and unserializing
     */
    public function __clone() {}
    public function __wakeup() {}

}


Controller::instance();