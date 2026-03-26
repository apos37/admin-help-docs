<?php
/**
 * Bottom of Pages
 */

namespace PluginRx\AdminHelpDocs;

if ( ! defined( 'ABSPATH' ) ) exit;

class Bottom {

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

        add_action( 'admin_notices', [ $this, 'render' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'scripts' ] );
    } // End __construct()


    /**
     * Render the docs
     */
    public function render() : void {
        if ( Helpers::is_gutenberg() || empty( $this->docs ) ) {
            return;
        }

        echo '<div class="helpdocs-bottom-wrapper" style="display:none;">';
        foreach ( $this->docs as $doc ) {
            $content = apply_filters( 'the_content', $doc->post_content );
            echo wp_kses_post( Helpers::output_doc( $doc->ID, $doc->post_title, $content, 'bottom' ) );
        }
        echo '</div>';
    } // End render()


    /**
     * Enqueue scripts
     */
    public function scripts() : void {
        $screen = get_current_screen();
        if ( ! $screen || empty( $this->docs ) ) {
            return;
        }

        $is_gutenberg = Helpers::is_gutenberg();
        $docs = $is_gutenberg ? Helpers::clean_docs_for_gutenberg( $this->docs ) : [];

        $text_domain = Bootstrap::textdomain();
        $script_version = Bootstrap::script_version();

        wp_enqueue_script( $text_domain . "-bottom", Bootstrap::url( "inc/docs/page-locations/js/bottom.js" ), [ 'jquery' ], $script_version, true );
        wp_localize_script( $text_domain . "-bottom", "helpdocs_bottom", [
            'docs'         => $docs,
            'is_gutenberg' => $is_gutenberg,
            'template'     => $is_gutenberg ? Helpers::output_doc( '{doc_id}', '{doc_title}', '{doc_content}', 'bottom' ) : '',
        ] );
    } // End scripts()

    
    /**
     * Prevent cloning and unserializing
     */
    public function __clone() {}
    public function __wakeup() {}

}