<?php
/**
 * Plugin Name:         Admin Help Docs
 * Plugin URI:          https://pluginrx.com/plugin/admin-help-docs/
 * Description:         Site developers and operators can easily create help documentation for the admin area
 * Version:             2.0.0.1
 * Requires at least:   5.9
 * Tested up to:        6.9
 * Requires PHP:        8.0
 * Author:              PluginRx
 * Author URI:          https://pluginrx.com/
 * Discord URI:         https://discord.gg/3HnzNEJVnR
 * Text Domain:         admin-help-docs
 * License:             GPLv2 or later
 * License URI:         http://www.gnu.org/licenses/gpl-2.0.txt
 * Created on:          November 14, 2022
 */


namespace PluginRx\AdminHelpDocs;

if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * BOOTSTRAP
 *
 * Loads plugin metadata, performs environment checks, and initializes the plugin.
 */
final class Bootstrap {

    /**
     * Plugin files to load.
     *
     * This array contains the paths to all plugin files that need to be included.
     */
    public const FILES = [
        'plugins-page.php',
        'helpers.php',
        'colors.php',
        'post-type-help-doc-imports.php',
        'post-type-help-docs.php',
        'taxonomy-folders.php',
        'tabs/settings.php',
        'tabs/admin-menu.php',
        'tabs/documentation.php',
        'tabs/faq.php',
        'tabs/support.php',
        'tabs/import.php',
        'menu.php',
        'shortcodes.php',
        'api.php',
        'deprecated.php',
        'cleanup.php',
    ];


    /**
     * Plugin header keys for get_file_data()
     */
    public const HEADER_KEYS = [
        'name'         => 'Plugin Name',
        'description'  => 'Description',
        'version'      => 'Version',
        'plugin_uri'   => 'Plugin URI',
        'requires_php' => 'Requires PHP',
        'textdomain'   => 'Text Domain',
        'author'       => 'Author',
        'author_uri'   => 'Author URI',
        'discord_uri'  => 'Discord URI'
    ];


    /**
     * @var array Plugin metadata from file header
     */
    private array $meta;


    /**
     * @var Bootstrap|null Singleton instance
     */
    private static ?Bootstrap $instance = null;


    /**
     * Get instance
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
        $this->meta = $this->load_meta();
        $this->check_environment();
        add_action( 'plugins_loaded', [ $this, 'load_files' ] );
    } // End __construct()


    /**
     * Check if test mode is enabled
     *
     * @return bool
     */
    public static function is_test_mode() : bool {
        return filter_var( apply_filters( 'helpdocs_test_mode', get_option( 'ddtt_test_mode' ) ), FILTER_VALIDATE_BOOLEAN );
    } // End is_test_mode()


    /**
     * Load plugin metadata
     *
     * @return array
     */
    private function load_meta() : array {
        return get_file_data( __FILE__, self::HEADER_KEYS );
    } // End load_meta()


    /**
     * Check environment requirements
     *
     * @return void
     */
    private function check_environment() : void {
        if ( version_compare( PHP_VERSION, $this->meta[ 'requires_php' ], '<' ) ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die( sprintf(
                /* translators: %1$s is plugin name, %2$s is required PHP version */
                esc_html( __( '%1$s requires PHP %2$s or higher.', 'dev-debug-tools' ) ),
                esc_html( $this->meta[ 'name' ] ),
                esc_html( $this->meta[ 'requires_php' ] )
            ) );
        }
    } // End check_environment()


    /**
     * Load all required plugin files
     *
     * @return void
     */
    public function load_files() : void {
        foreach ( self::FILES as $file ) {
            $file_path = __DIR__ . '/inc/' . $file;
            if ( file_exists( $file_path ) ) {
                require_once $file_path;
            } else {
                _doing_it_wrong(
                    __METHOD__,
                    sprintf( 'File not found: %s', esc_html( $file_path ) ),
                    esc_html( $this->version() )
                );
            }
        }

        // Autoload all files in the "inc/docs/page-locations" directory
        $docs_dir = __DIR__ . '/inc/docs/page-locations/';
        if ( is_dir( $docs_dir ) ) {
            foreach ( glob( $docs_dir . '*.php' ) as $file ) {
                require_once $file;
            }
        } else {
            _doing_it_wrong(
                __METHOD__,
                sprintf( 'Directory not found: %s', esc_html( $docs_dir ) ),
                esc_html( $this->version() )
            );
        }

        // Autoload all files in the "inc/docs" directory
        $docs_dir = __DIR__ . '/inc/docs/';
        if ( is_dir( $docs_dir ) ) {
            foreach ( glob( $docs_dir . '*.php' ) as $file ) {
                require_once $file;
            }
        } else {
            _doing_it_wrong(
                __METHOD__,
                sprintf( 'Directory not found: %s', esc_html( $docs_dir ) ),
                esc_html( $this->version() )
            );
        }
    } // End load_files()


    /**
     * Get admin URL
     *
     * @param string $path
     * @param string $scheme
     * @return string
     */
    public static function admin_url( $path = '', $scheme = 'admin' ) {
         return is_network_admin() ? network_admin_url( $path, $scheme ) : admin_url( $path, $scheme );
    } // End admin_url()


    /**
     * Get metadata value
     *
     * @param string $key
     * @return string
     */
    public static function meta( string $key ) : string {
        return self::$instance->meta[ $key ] ?? '';
    } // End meta()


    /**
     * Get plugin URL
     *
     * @param string $append
     * @return string
     */
    public static function url( string $append = '' ) : string {
        return plugin_dir_url( __FILE__ ) . ltrim( $append, '/' );
    } // End url()


    /**
     * Get plugin path
     *
     * @param string $append
     * @return string
     */
    public static function path( string $append = '' ) : string {
        return plugin_dir_path( __FILE__ ) . ltrim( $append, '/' );
    } // End path()


    /**
     * Get a page URL
     *
     * @param string $append
     * @return string
     */
    public static function tab_url( $slug ) : string {
        $slug = sanitize_key( $slug );

        switch ( $slug ) {
            case 'manage':
                return add_query_arg(
                    [ 'post_type' => HelpDocs::$post_type ],
                    self::admin_url( 'edit.php' )
                );

            case 'folders':
                return add_query_arg(
                    [ 'taxonomy' => Folders::$taxonomy ],
                    self::admin_url( 'edit-tags.php' )
                );

            case 'imports':
                return add_query_arg(
                    [ 'post_type' => Imports::$post_type ],
                    self::admin_url( 'edit.php' )
                );

            case 'main':
                $slug = 'documentation';
                break;

            case 'adminmenu':
                $slug = 'admin-menu';
                break;
        }

        return add_query_arg(
            [
                'page' => self::textdomain(),
                'tab'  => $slug,
            ],
            self::admin_url( 'admin.php' )
        );
    } // End tab_url()


    /**
     * Get plugin name
     *
     * @return string
     */
    public static function name() : string {
        return self::meta( 'name' );
    } // End name()


    /**
     * Get plugin version
     *
     * @return string
     */
    public static function version() : string {
        return self::meta( 'version' );
    } // End version()


    /**
     * Get script/style version for cache busting.
     * Returns timestamp if TEST_MODE is enabled, otherwise plugin version.
     *
     * @return string
     */
    public static function script_version() : string {
        if ( self::is_test_mode() ) {
            return 'TEST-' . time();
        }
        return self::version();
    } // End script_version()


    /**
     * Get plugin text domain
     *
     * @return string
     */
    public static function textdomain() : string {
        return self::meta( 'textdomain' );
    } // End textdomain()


    /**
     * Get plugin author
     *
     * @return string
     */
    public static function author() : string {
        return self::meta( 'author' );
    } // End author()


    /**
     * Get plugin URI
     *
     * @return string
     */
    public static function plugin_uri() : string {
        return self::meta( 'plugin_uri' );
    } // End plugin_uri()


    /**
     * Get author URI
     *
     * @return string
     */
    public static function author_uri() : string {
        return self::meta( 'author_uri' );
    } // End author_uri()


    /**
     * Get Discord URI
     *
     * @return string
     */
    public static function discord_uri() : string {
        return self::meta( 'discord_uri' );
    } // End discord_uri()


    /**
     * Prevent cloning and unserializing
     */
    public function __clone() {}
    public function __wakeup() {}

} // End Bootstrap


Bootstrap::instance();