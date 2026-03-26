<?php
/**
 * Sidebars
 */

namespace PluginRx\AdminHelpDocs;

if ( ! defined( 'ABSPATH' ) ) exit;

class Side {

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

        add_action( 'submitpost_box', [ $this, 'render' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'scripts' ] );
    } // End __construct()


    /**
     * Render the docs
     */
    public function render() : void {
        if ( Helpers::is_gutenberg() || empty( $this->docs ) ) {
            return;
        }

        $allowed_tags = Helpers::allow_addt_tags( wp_kses_allowed_html( 'post' ) );

        echo '<div class="helpdocs-side-wrapper">';
        foreach ( $this->docs as $doc ) {
            $content = apply_filters( 'the_content', $doc->post_content );
            $html    = Helpers::output_doc( $doc->ID, $doc->post_title, $content, 'side' );

            echo wp_kses( $html, $allowed_tags );
        }
        echo '</div>';
    } // End render()


    /**
     * Enqueue scripts
     */
    public function scripts() : void {
        $screen = get_current_screen();
        if ( ! $screen || ! Helpers::is_gutenberg() || empty( $this->docs ) ) {
            return;
        }

        $docs = Helpers::clean_docs_for_gutenberg( $this->docs );

        $text_domain = Bootstrap::textdomain();
        $script_version = Bootstrap::script_version();

        wp_enqueue_script( $text_domain . "-side", Bootstrap::url( "inc/docs/page-locations/js/side.js" ), [ 'jquery' ], $script_version, true );
        wp_localize_script( $text_domain . "-side", "helpdocs_side", [
            'docs'         => $docs,
            'template'     => Helpers::output_doc( '{doc_id}', '{doc_title}', '{doc_content}', 'side' ),
        ] );
    } // End scripts()

    
    /**
     * Prevent cloning and unserializing
     */
    public function __clone() {}
    public function __wakeup() {}

}