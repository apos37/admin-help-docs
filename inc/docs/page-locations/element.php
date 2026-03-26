<?php
/**
 * Element (CSS Selector) Location
 */

namespace PluginRx\AdminHelpDocs;

if ( ! defined( 'ABSPATH' ) ) exit;

class Element {


    /**
     * The docs to be rendered
     *
     * @var array
     */
    private array $docs = [];


    /**
     * Constructor
     */
    public function __construct( $docs = [] ) {
        if ( empty( $docs ) ) {
            return;
        }

        $this->docs = $docs;

        add_action( 'admin_enqueue_scripts', [ $this, 'scripts' ] );
    } // End __construct()


    /**
     * Add a help tab button for the current gutenberg screen
     */
    public function scripts() : void {
        $screen = get_current_screen();
        if ( ! $screen || empty( $this->docs ) ) {
            return;
        }

        $is_gutenberg = Helpers::is_gutenberg();
        $docs = Helpers::clean_docs_for_gutenberg( $this->docs );

        $text_domain = Bootstrap::textdomain();
        $script_version = Bootstrap::script_version();

        wp_enqueue_script( $text_domain . "-element", Bootstrap::url( "inc/docs/page-locations/js/element.js" ), [ 'jquery' ], $script_version, true );
        wp_localize_script( $text_domain . "-element", "helpdocs_element", [
            'docs'         => $docs,
            'is_gutenberg' => $is_gutenberg,
        ] );
    } // End scripts()

    
    /**
     * Prevent cloning and unserializing
     */
    public function __clone() {}
    public function __wakeup() {}

}