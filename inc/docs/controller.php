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
        if ( 'index.php' !== $pagenow || ! get_option( 'helpdocs_replace_dashboard' ) ) {
            return;
        }

        if ( wp_doing_ajax() || wp_doing_cron() || ( defined( 'IFRAME_REQUEST' ) && IFRAME_REQUEST ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
            return;
        }

        // Third-party plugins use /wp-admin/?foo=bar as an endpoint (Gravity Forms' ?gf_page=select_columns iframe, for one), so bail on anything that isn't a plain dashboard load
        $allowed = apply_filters( 'helpdocs_dashboard_allowed_params', [ 'message', 'updated', 'welcome' ] );
        $unknown = array_diff( array_keys( $_GET ), $allowed ); // phpcs:ignore
        if ( ! empty( $unknown ) ) {
            return;
        }

        wp_safe_redirect( admin_url( 'admin.php?page=admin-help-dashboard' ) );
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