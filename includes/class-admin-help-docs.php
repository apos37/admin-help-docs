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

        // Load other dependencies.
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

        // Disable curly quotes everywhere, because they're stupid and cause issues when sharing code
        if ( get_option( HELPDOCS_GO_PF.'curly_quotes' ) && get_option( HELPDOCS_GO_PF.'curly_quotes' ) == 1 ) {
            remove_filter( 'the_content', 'wptexturize' );
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
     * Admin-only dependencies
     *
	 * @return void
     */
    public function load_admin_dependencies() {
        // Admin menu, also loads options.php
        require_once HELPDOCS_PLUGIN_ADMIN_PATH . 'menu.php';
        
        // Options page functions such as form table rows
        require_once HELPDOCS_PLUGIN_ADMIN_PATH . 'functions.php';
        require_once HELPDOCS_PLUGIN_ADMIN_PATH . 'admin-area.php';

        // Classes
        require_once HELPDOCS_PLUGIN_CLASSES_PATH . 'class-colors.php';
        require_once HELPDOCS_PLUGIN_CLASSES_PATH . 'class-imports.php';
        require_once HELPDOCS_PLUGIN_CLASSES_PATH . 'class-user-profile.php';
        require_once HELPDOCS_PLUGIN_CLASSES_PATH . 'class-admin-bar.php';
        require_once HELPDOCS_PLUGIN_CLASSES_PATH . 'class-discord.php';
        require_once HELPDOCS_PLUGIN_CLASSES_PATH . 'class-gf-mergetags.php';
        require_once HELPDOCS_PLUGIN_CLASSES_PATH . 'class-dashboard-toc.php';
        require_once HELPDOCS_PLUGIN_CLASSES_PATH . 'class-admin-menu.php';

        // Enqueue scripts
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    } // End load_admin_dependencies()

    
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

        // The main doc class where we register the post type and taxonomy
        require_once HELPDOCS_PLUGIN_CLASSES_PATH . 'class-documentation.php';
        
        // Rest API end-point
        require_once HELPDOCS_PLUGIN_CLASSES_PATH . 'class-api.php';
    } // End load_dependencies()


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
        echo wp_kses_post( get_option( HELPDOCS_GO_PF.'footer_left' ) );
    } // End footer_left()


    /**
     * Change the right footer text
     *
     * @return void
     */
    public function footer_right() {
        // Option
        $text = get_option( HELPDOCS_GO_PF.'footer_right', 'Version {version}' );

        // Check for {version}
        if ( strpos( $text, '{version}' ) !== false ) {

            // Get the version
            $version = get_bloginfo( 'version' );

            // Replace it
            $text = str_replace( '{version}', $version, $text );
        }

        // Return it
        return wp_kses_post( $text );
    } // End footer_right()


    /**
     * Enqueue scripts
     * Reminder to bump version number during testing to avoid caching
     *
     * @param string $hook
     * @return void
     */
    public function enqueue_scripts( $screen ) {
        
        // Get the options page slug
        $options_page = 'toplevel_page_'.HELPDOCS_TEXTDOMAIN;

        // Allow for multisite
        if ( is_network_admin() ) {
            $options_page .= '-network';
        }

        // Are we on the options page?
        if ( $screen != $options_page ) {
            return;
        }

        // Settings page script
        if ( helpdocs_get( 'tab', '==', 'settings' ) ) {
            wp_register_script( HELPDOCS_GO_PF.'settings_script', HELPDOCS_PLUGIN_JS_PATH.'settings.js', [ 'jquery' ], HELPDOCS_SCRIPT_VERSION, true );
            wp_enqueue_script( HELPDOCS_GO_PF.'settings_script' );
        }

        // Admin menu script
        if ( helpdocs_get( 'tab', '==', 'adminmenu' ) ) {
            wp_register_script( HELPDOCS_GO_PF.'adminmenu_script', HELPDOCS_PLUGIN_JS_PATH.'adminmenu.js', [ 'jquery', 'jquery-ui-sortable' ], HELPDOCS_SCRIPT_VERSION, true );
            wp_enqueue_script( HELPDOCS_GO_PF.'adminmenu_script' );
        }

        // Run jQuery, et al
        if ( helpdocs_get( 'tab', '==', 'settings' ) || helpdocs_get( 'tab', '==', 'adminmenu' ) || helpdocs_get( 'tab', '==', 'about' ) ) {
            wp_enqueue_script( 'jquery' );
        }
    } // End enqueue_scripts()
}