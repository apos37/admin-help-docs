<?php
/**
 * Admin bar class
 */

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Initiate the class
 */
if ( get_option( HELPDOCS_GO_PF.'admin_bar' ) == 1 ) {
    new HELPDOCS_ADMIN_BAR;
}


/**
 * Main plugin class.
 */
class HELPDOCS_ADMIN_BAR {

    /**
	 * Constructor
	 */
	public function __construct() {

        // Customize the admin bar menu
        add_action( 'admin_bar_menu', [ $this, 'admin_bar' ], 99999 );

	} // End __construct()


    /**
     * Customize Admin Bar
     *
     * @param object $wp_admin_bar
     * @return void
     */
    public function admin_bar( $wp_admin_bar ) {

        // The title
        if ( get_option( HELPDOCS_GO_PF.'page_title' ) && get_option( HELPDOCS_GO_PF.'page_title' ) != '' ) {
            $title = get_option( HELPDOCS_GO_PF.'page_title' );
        } else {
            $title = HELPDOCS_NAME;
        }
        
        // Get the dashicon
        $dashicon = get_option( HELPDOCS_GO_PF.'dashicon', 'dashicons-editor-help' );
        
        // Add the node
        $wp_admin_bar->add_node( [
            'id' => HELPDOCS_TEXTDOMAIN,
            'title' => '<i class="dashicons-before '.esc_attr( $dashicon ).'" title="'.esc_html( $title ).'"></i>',
            'href' => helpdocs_plugin_options_path( 'documentation' ),
            'meta' => [
                'target' => '_blank'
            ],
        ] );

        // Start the args to get the docs
        $args = [
            'posts_per_page'    => -1,
            'post_status'       => 'publish',
            'post_type'         => 'help-docs',
            'meta_key'		    => HELPDOCS_GO_PF.'site_location',
            'meta_value'	    => base64_encode( 'admin_bar' ),
            'meta_compare'	    => '=',
        ];

        // Get the posts
        $docs = get_posts( $args );

        // Also get the imports
        $imports = helpdocs_get_imports( $args );

        // Merge them together
        if ( !empty( $imports ) ) {
            $docs = array_merge( $docs, $imports );
        }

        // Check if we found docs
        if ( !empty( $docs ) ) {

            // First we sort by the doc order
            usort( $docs, function( $a, $b ) { return strcmp( $a->helpdocs_order, $b->helpdocs_order ) ; } );

            // Iter the docs
            foreach ( $docs as $key => $doc ) {

                // Add content
                if ( filter_var( trim( $doc->post_content ), FILTER_VALIDATE_URL) ) {
                    $href = trim( $doc->post_content );
                    $incl_content = '';
                } elseif ( trim( $doc->post_content ) != '' ) {
                    $href = false;
                    $incl_content = ' — '.esc_html( strip_tags( $doc->post_content ) );
                } else {
                    $href = false;
                    $incl_content = '';
                }

                // The node array
                $node = [
                    'id' => HELPDOCS_GO_PF.$key,
                    'parent' => HELPDOCS_TEXTDOMAIN,
                    'title' => esc_html( $doc->post_title ).$incl_content,
                    'meta' => [
                        'target' => '_blank'
                    ],
                ];

                // Are we making it a link?
                if ( $href ) {
                    $node[ 'href' ] = $href;
                }

                // Add the node
                $wp_admin_bar->add_node( $node );
            }
        }
        
        // CSS
        echo '<style>
        #wp-admin-bar-'.esc_attr( HELPDOCS_TEXTDOMAIN ).' .dashicons-before::before {
            line-height: 1.6;
        }
        </style>';
    } // End admin_bar()
}