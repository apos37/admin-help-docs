<?php
/**
 * Top of Pages
 */

namespace PluginRx\AdminHelpDocs;

if ( ! defined( 'ABSPATH' ) ) exit;

class Top {

    /**
     * The placement of the top docs
     *
     * @var string
     */
    private $placement = 'admin_notices'; // Default placement


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

        $available_placements = Settings::top_placements();
        $this->placement = sanitize_key( get_option( 'helpdocs_top_location_type', 'admin_notices' ) );
        if ( array_key_exists( $this->placement, $available_placements ) ) {
            add_action( $this->placement, [ $this, 'render' ] );
        }

        add_action( 'admin_enqueue_scripts', [ $this, 'scripts' ] );
    } // End __construct()


    /**
     * Render the docs
     */
    public function render() : void {
        if ( Helpers::is_gutenberg() || empty( $this->docs ) ) {
            return;
        }

        echo '<div class="helpdocs-top-wrapper helpdocs-' . esc_attr( str_replace( '_', '-', $this->placement ) ) . '">';
        foreach ( $this->docs as $doc ) {
            $content = apply_filters( 'the_content', $doc->post_content );
            echo wp_kses_post( Helpers::output_doc( $doc->ID, $doc->post_title, $content, 'top' ) );
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

        wp_enqueue_script( $text_domain . "-top", Bootstrap::url( "inc/docs/page-locations/js/top.js" ), [ 'jquery' ], $script_version, true );
        wp_localize_script( $text_domain . "-top", "helpdocs_top", [
            'docs'     => $docs,
            'template' => Helpers::output_doc( '{doc_id}', '{doc_title}', '{doc_content}', 'top' ),
        ] );
    } // End scripts()

    
    /**
     * Prevent cloning and unserializing
     */
    public function __clone() {}
    public function __wakeup() {}

}