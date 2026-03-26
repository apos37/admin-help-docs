<?php
/**
 * Footer Text
 */

namespace PluginRx\AdminHelpDocs;

if ( ! defined( 'ABSPATH' ) ) exit;

class FooterText {


    /**
     * The single instance of the class
     *
     * @var self|null
     */
    private static ?FooterText $instance = null;


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
        add_filter( ( 'admin_footer_text' ), [ $this, 'render_left_footer' ] );
        add_filter( ( 'update_footer' ), [ $this, 'render_right_footer' ], 9999 );
    } // End __construct()


    /**
     * Replaces the "Thank you for creating with WordPress" text.
     * Supports {version} tag to dynamically insert the current WordPress version.
     * 
     * @param string $text The original footer text
     * @return string The modified footer text
     */
    public function render_left_footer( $text ) {
        $option = get_option( 'helpdocs_left_footer' );
        if ( ! $option ) {
            return $text;
        }

        $content = wp_unslash( $option );
        $content = str_replace( '{version}', get_bloginfo( 'version' ), $content );

        return wp_kses_post( $content );
    } // End render_left_footer()


    /**
     * Replaces the WordPress version/update text on the right.
     * Supports {version} tag to dynamically insert the current WordPress version.
     * 
     * @param string $text The original footer text
     * @return string The modified footer text
     */
    public function render_right_footer( $text ) {
        $option = get_option( 'helpdocs_right_footer' );
        if ( ! $option ) {
            return $text;
        }

        $content = wp_unslash( $option );
        $content = str_replace( '{version}', get_bloginfo( 'version' ), $content );

        return wp_kses_post( $content );
    } // End render_right_footer()

    
    /**
     * Prevent cloning and unserializing
     */
    public function __clone() {}
    public function __wakeup() {}

}


FooterText::instance();