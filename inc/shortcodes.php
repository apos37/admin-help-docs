<?php
/**
 * Shortcodes
 */

namespace PluginRx\AdminHelpDocs;

if ( ! defined( 'ABSPATH' ) ) exit;

class Shortcodes {

    /**
     * The single instance of the class
     *
     * @var self|null
     */
    private static ?Shortcodes $instance = null;


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

        // Display shortcodes without executing them
        add_shortcode( 'dont_do_shortcode', [ $this, 'dont_do_shortcode' ] );

        // Deprecated: Add custom CSS to documents
        add_shortcode( 'helpdocs_css', [ $this, 'helpdocs_css' ] );

	} // End __construct()


    /**
     * Display shortcodes without executing them
     * USAGE: [dont_do_shortcode click_to_copy="false" code="false"][your_shortcode_here][/dont_do_shortcode]
     *
     * @param array $atts
     * @return string
     */
    public function dont_do_shortcode( $atts, $content = null ) : string {
        $atts = shortcode_atts( [ 
            'content'       => '',
            'code'          => true,
            'click_to_copy' => true
        ], $atts );

        $wrapper = ( strtolower( sanitize_text_field( $atts[ 'code' ] ) ) == 'false' ) ? 'span' : 'code';
        $click_to_copy = ( strtolower( sanitize_text_field( $atts[ 'click_to_copy' ] ) ) == 'false' ) ? false : true;

        // Support legacy method of passing content as an attribute with curly braces instead of square brackets
        if ( empty( $content ) && ! empty( $atts[ 'content' ] ) ) {
            $content = $atts[ 'content' ];
            $content = str_replace( '{', '[[', $content );
            $content = str_replace( '}', ']]', $content );
        }

        $click_to_copy_class = $click_to_copy ? ' helpdocs-click-to-copy' : '';

        return '<' . $wrapper . ' class="helpdocs_dont_do_shortcode' . $click_to_copy_class . '">' . $content . '</' . $wrapper . '>';
    } // End dont_do_shortcode()


    /**
     * Add custom CSS (External or Inline) to the document
     *
     * @deprecated Add CSS to main docs page from the settings page now.
     * @return string
     */
    public function helpdocs_css() : string {
        _deprecated_function( __FUNCTION__, '2.0', 'Add CSS to main docs page from the settings page now.' );
        return '';
    } // End helpdocs_css()
    
}


Shortcodes::instance();