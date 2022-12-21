<?php
/**
 * Main plugin class file.
 */

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Main plugin class.
 */
class HELPDOCS_MAIN {

    /**
	 * Constructor
	 */
	public function __construct() {
        // Ensure is_plugin_active() exists for multisite
		if ( !function_exists( 'is_plugin_active' ) ) {
            if ( is_network_admin() ) {
                $admin_url = str_replace( site_url( '/' ), '', rtrim( admin_url(), '/' ) );
            } else {
                $admin_url = HELPDOCS_ADMIN_URL;
            }
			include_once( ABSPATH . $admin_url . '/includes/plugin.php' );
		}

        // Add "Settings" link to plugins page
        add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), [ $this, 'settings_link' ] );

        // Load dependencies.
        if ( is_admin() ) {
			$this->load_admin_dependencies();
		}
        $this->load_dependencies();

        // Add data to image src
        add_filter( 'kses_allowed_protocols', [ $this, 'kses_allowed_protocols' ] );

        // Change the left footer text
        if ( get_option( HELPDOCS_GO_PF.'footer_left' ) && get_option( HELPDOCS_GO_PF.'footer_left' ) != '' ) {
            add_action( 'admin_footer_text', [ $this, 'footer_left' ], 9999 );
        }

        // Change the right footer text
        if ( get_option( HELPDOCS_GO_PF.'footer_right' ) && get_option( HELPDOCS_GO_PF.'footer_right' ) != '' ) {
            add_action( 'update_footer', [ $this, 'footer_right' ], 9999 );
        }
        
	} // End __construct()


    /**
     * Add "Settings" link to plugins page
     * 
     * @return string
     */
    public function settings_link() {
        $links[] = '<a href="'.helpdocs_plugin_options_path( 'settings' ).'">'.__( 'Settings' ).'</a>';
        return $links;
    } // End settings_link()

    
    /**
     * Global dependencies
     * Not including scripts
     * 
     * @return void
     */
    public function load_dependencies() {
        // Admin Options page
        require_once HELPDOCS_PLUGIN_ADMIN_PATH . 'global-options.php';
        
        // Miscellaneous functions
        require_once HELPDOCS_PLUGIN_INCLUDES_PATH . 'functions.php';
    } // End load_dependencies()


    /**
     * Admin-only dependencies
     *
	 * @return void
     */
    public function load_admin_dependencies() {
        // Admin menu, also loads options.php
        require_once HELPDOCS_PLUGIN_ADMIN_PATH . 'menu.php';
        
        // Options page functions such as form table rows
        require_once HELPDOCS_PLUGIN_ADMIN_PATH . 'functions.php';

        // Classes
        require_once HELPDOCS_PLUGIN_CLASSES_PATH . 'class-documentation.php';
        require_once HELPDOCS_PLUGIN_CLASSES_PATH . 'class-user-profile.php';
        require_once HELPDOCS_PLUGIN_CLASSES_PATH . 'class-admin-bar.php';
    } // End load_admin_dependencies()


    /**
     * Add data to image src
     *
     * @param array $protocols
     * @return array
     */
    public function kses_allowed_protocols( $protocols ) {
        $protocols[] = 'data';
        return $protocols;
    } // End kses_allowed_protocols()


    /**
     * Change the left footer text
     *
     * @return void
     */
    public function footer_left() {
        echo __( get_option( HELPDOCS_GO_PF.'footer_left' ), 'admin-help-docs' );
    } // End footer_left()


    /**
     * Change the right footer text
     *
     * @return void
     */
    public function footer_right() {
        return __( get_option( HELPDOCS_GO_PF.'footer_right' ), 'admin-help-docs' );
    } // End footer_right()
}