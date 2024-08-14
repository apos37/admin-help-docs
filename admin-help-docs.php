<?php
/**
 * Plugin Name:         Admin Help Docs
 * Plugin URI:          https://github.com/apos37/admin-help-docs
 * Description:         Site developers and operators can easily create help documentation for the admin area
 * Version:             1.3.5.4
 * Requires at least:   5.9.0
 * Tested up to:        6.6.1
 * Requires PHP:        7.4
 * Author:              Apos37
 * Author URI:          https://apos37.com/
 * Text Domain:         admin-help-docs
 * License:             GPLv2 or later
 * License URI:         http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Defines
 */

// Versions
define( 'HELPDOCS_VERSION', '1.3.5.4' );
define( 'HELPDOCS_MIN_PHP_VERSION', '7.4' );

// Names
define( 'HELPDOCS_NAME', 'Admin Help Docs' );
define( 'HELPDOCS_TEXTDOMAIN', 'admin-help-docs' );
define( 'HELPDOCS_AUTHOR', 'Apos37' );
define( 'HELPDOCS_AUTHOR_EMAIL', 'apos37@pm.me' );
define( 'HELPDOCS_AUTHOR_URL', 'https://apos37.com/' );
define( 'HELPDOCS_DISCORD_SUPPORT_URL', 'https://discord.gg/3HnzNEJVnR' );

// Prevent loading the plugin if PHP version is not minimum
if ( version_compare( PHP_VERSION, HELPDOCS_MIN_PHP_VERSION, '<=' ) ) {
    add_action( 'admin_init', static function() {
        deactivate_plugins( plugin_basename( __FILE__ ) );
    } );
    add_action( 'admin_notices', static function() {
        /* translators: 1: Plugin name, 2: Minimum PHP version */
        $message = sprintf( __( '"%1$s" requires PHP %2$s or newer.', 'admin-help-docs' ),
            HELPDOCS_NAME,
            HELPDOCS_MIN_PHP_VERSION
        );
        echo '<div class="notice notice-error"><p>'.esc_html( $message ).'</p></div>';
    } );
    return;
}

// Prefixes
define( 'HELPDOCS_PF', 'HELPDOCS_' ); // Plugin prefix
define( 'HELPDOCS_GO_PF', 'helpdocs_' ); // Global options prefix

// Fetch site url only once
$site_url = site_url( '/' );

// Paths
define( 'HELPDOCS_ADMIN_URL', str_replace( $site_url, '', rtrim( helpdocs_admin_url(), '/' ) ) );           //: wp-admin || wp-admin/network
define( 'HELPDOCS_CONTENT_URL', str_replace( $site_url, '', content_url() ) );                              //: wp-content
define( 'HELPDOCS_INCLUDES_URL', str_replace( $site_url, '', rtrim( includes_url(), '/' ) ) );              //: wp-includes
define( 'HELPDOCS_PLUGINS_URL', str_replace( $site_url, '', plugins_url() ) );                              //: wp-content/plugins
define( 'HELPDOCS_PLUGIN_ABSOLUTE', __FILE__ );                                                             //: /home/.../public_html/wp-content/plugins/admin-help-docs/admin-help-docs.php)
define( 'HELPDOCS_PLUGIN_ROOT', plugin_dir_path( __FILE__ ) );                                              //: /home/.../public_html/wp-content/plugins/admin-help-docs/
define( 'HELPDOCS_PLUGIN_DIR', plugins_url( '/'.HELPDOCS_TEXTDOMAIN.'/' ) );                                //: https://domain.com/wp-content/plugins/admin-help-docs/
define( 'HELPDOCS_PLUGIN_SHORT_DIR', str_replace( site_url(), '', HELPDOCS_PLUGIN_DIR ) );                  //: /wp-content/plugins/admin-help-docs/
define( 'HELPDOCS_PLUGIN_ASSETS_PATH', HELPDOCS_PLUGIN_ROOT.'assets/' );                                    //: /home/.../public_html/wp-content/plugins/admin-help-docs/assets/
define( 'HELPDOCS_PLUGIN_IMG_PATH', HELPDOCS_PLUGIN_DIR.'includes/admin/img/' );                            //: https://domain.com/wp-content/plugins/admin-help-docs/includes/admin/img/
define( 'HELPDOCS_PLUGIN_INCLUDES_PATH', HELPDOCS_PLUGIN_ROOT.'includes/' );                                //: /home/.../public_html/wp-content/plugins/admin-help-docs/includes/
define( 'HELPDOCS_PLUGIN_ADMIN_PATH', HELPDOCS_PLUGIN_INCLUDES_PATH.'admin/' );                             //: /home/.../public_html/wp-content/plugins/admin-help-docs/includes/admin/
define( 'HELPDOCS_PLUGIN_CLASSES_PATH', HELPDOCS_PLUGIN_INCLUDES_PATH.'classes/' );                         //: /home/.../public_html/wp-content/plugins/admin-help-docs/includes/classes/
define( 'HELPDOCS_PLUGIN_CSS_PATH', HELPDOCS_PLUGIN_SHORT_DIR.'includes/admin/css/' );                      //: /wp-content/plugins/admin-help-docs/includes/admin/css/
define( 'HELPDOCS_PLUGIN_JS_PATH', HELPDOCS_PLUGIN_SHORT_DIR.'includes/admin/js/' );                        //: /wp-content/plugins/admin-help-docs/includes/admin/js/
define( 'HELPDOCS_PLUGIN_FILES_PATH', HELPDOCS_PLUGIN_SHORT_DIR.'includes/files/' );                        //: /wp-content/plugins/admin-help-docs/includes/files/


/**
 * Get admin URL (handles multisite)
 *
 * @param string $path
 * @param string $scheme
 * @return string
 */
function helpdocs_admin_url( $path = '', $scheme = 'admin' ) {
    if ( is_network_admin() ) {
        $admin_url = network_admin_url( $path, $scheme );
    } else {
        $admin_url = admin_url( $path, $scheme );
    }
    return $admin_url;
} // End helpdocs_admin_url()


/**
 * Get a path to one of our options pages
 * https://domain.com/wp-admin/admin.php?page=admin-help-docs%2Fincludes%2Fadmin%2Foptions.php
 * https://domain.com/wp-admin/admin.php?page=admin-help-docs%2Fincludes%2Fadmin%2Foptions.php&tab=testing
 *
 * @param string $tab
 * @return string
 */
function helpdocs_plugin_options_path( $tab = null ) {
    $incl_tab = !is_null( $tab ) ? '&tab='.sanitize_html_class( $tab ) : '';
    return helpdocs_admin_url( 'admin.php?page='.HELPDOCS_TEXTDOMAIN.$incl_tab );
} // End helpdocs_plugin_options_path()


/**
 * Get a short path to our options pages
 * admin-help-docs/includes/admin/options.php
 * admin-help-docs/includes/admin/options.php&tab=testing
 *
 * @param string $tab
 * @return string
 */
function helpdocs_plugin_options_short_path( $tab = null ) {
    $incl_tab = !is_null( $tab ) ? '&tab='.sanitize_html_class( $tab ) : '';
    return HELPDOCS_TEXTDOMAIN.$incl_tab;
} // End helpdocs_plugin_options_short_path()


/**
 * Multisite verbiage
 *
 * @return string
 */
function helpdocs_multisite_suffix() {
    if ( is_network_admin() ) {
        $sfx = ' <em>' . __( '- Network', 'admin-help-docs' ) . '</em>';
    } elseif ( is_multisite() && is_main_site() ) {
        $sfx = ' <em>' . __( '- Primary', 'admin-help-docs' ) . '</em>';
    } elseif ( is_multisite() && !is_main_site() ) {
        $sfx = ' <em>' . __( '- Subsite', 'admin-help-docs' ) . '</em>';
    } else {
        $sfx = '';
    }
    return $sfx;
} // End helpdocs_multisite_suffix()



/**
 * Activate
 */
register_activation_hook( __FILE__, 'helpdocs_activate_plugin' );
function helpdocs_activate_plugin() {
    // Log when this plugin was installed
    if ( !get_option( HELPDOCS_GO_PF.'plugin_installed' ) ) {
        update_option( HELPDOCS_GO_PF.'plugin_installed', gmdate( 'Y-m-d H:i:s' ) );
    }

	// Log when this plugin was last activated
    update_option( HELPDOCS_GO_PF.'plugin_activated', gmdate( 'Y-m-d H:i:s' ) );

    // Uninstall
    register_uninstall_hook( __FILE__, HELPDOCS_GO_PF.'uninstall_plugin' );
} // End helpdocs_activate_plugin()


/**
 * Uninstall
 * Registered inside register_activation_hook above
 */
function helpdocs_uninstall_plugin() {
    // Delete options
    delete_option( HELPDOCS_GO_PF.'plugin_activated' ); // Date the plugin was last activated
} // End helpdocs_uninstall_plugin()


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require HELPDOCS_PLUGIN_INCLUDES_PATH . 'class-'. HELPDOCS_TEXTDOMAIN .'.php';


/**
 * Begin execution of the plugin
 */
new HELPDOCS_MAIN();