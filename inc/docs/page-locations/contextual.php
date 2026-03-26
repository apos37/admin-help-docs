<?php
/**
 * Contextual Help
 */

namespace PluginRx\AdminHelpDocs;

if ( ! defined( 'ABSPATH' ) ) exit;

class Contextual {


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

        add_action( 'admin_head', [ $this, 'add_contextual_help_tabs' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'scripts' ] );
    } // End __construct()


    /**
     * Register help tabs for the current screen on non-gutenberg pages
     */
    public function add_contextual_help_tabs() : void {
        $screen = get_current_screen();
        if ( ! $screen || Helpers::is_gutenberg() || empty( $this->docs ) ) {
            return;
        }

        foreach ( $this->docs as $doc ) {
            $screen->add_help_tab( [
                'id'      => 'help_tab_' . $doc->ID,
                'title'   => $doc->post_title,
                'content' => apply_filters( 'the_content', $doc->post_content ),
            ] );
        }
    } // End add_contextual_help_tabs()


    /**
     * Add a help tab button for the current gutenberg screen
     */
    public function scripts() : void {
        $screen = get_current_screen();
        if ( ! $screen || ! Helpers::is_gutenberg() || empty( $this->docs ) ) {
            return;
        }

        $docs = Helpers::clean_docs_for_gutenberg( $this->docs );

        $text_domain = Bootstrap::textdomain();
        $script_version = Bootstrap::script_version();

        wp_enqueue_script( $text_domain . "-contextual", Bootstrap::url( "inc/docs/page-locations/js/contextual.js" ), [ 'jquery' ], $script_version, true );
        wp_localize_script( $text_domain . "-contextual", "helpdocs_contextual", [
            'docs' => $docs,
        ] );
    } // End scripts()

    
    /**
     * Prevent cloning and unserializing
     */
    public function __clone() {}
    public function __wakeup() {}

}