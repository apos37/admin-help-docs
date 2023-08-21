<?php
/**
 * Dashboard Table of Contents class
 */

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Initiate the class
 */
new HELPDOCS_TOC;


/**
 * Main plugin class.
 */
class HELPDOCS_TOC {

    /**
     * Post type
     */ 
    public static $post_type;


    /**
	 * Constructor
	 */
	public function __construct() {

        // Define the post type
        self::$post_type = 'help-docs';

        // Dashboard widget
        if ( get_option( HELPDOCS_GO_PF.'dashboard_toc' ) && get_option( HELPDOCS_GO_PF.'dashboard_toc' ) == '1' ) {
            add_action( 'wp_dashboard_setup', [ $this, 'dashboard_widget' ] );
        }

	} // End __construct()


    /**
     * Dashboard widgets
     *
     * @return void
     */
    public function dashboard_widget() {
        // The dashboard widget title
        $title = helpdocs_title().' <span>[ <a href="'.helpdocs_plugin_options_path( 'documentation' ).'" style="display: contents;">View All Docs</a> ]</span>';

        // Add the widget
        if ( helpdocs_user_can_view() ) {
            wp_add_dashboard_widget( HELPDOCS_TEXTDOMAIN, $title, [ $this, 'dashboard_content' ], null, null, 'normal', 'high' );
        }
    } // End dashboard_widget()


    /**
     * Dashboard content
     *
     * @return void
     */
    public function dashboard_content( $var, $args ) {
        // Get all the docs that are enabled
        $args = [
            'posts_per_page'    => -1,
            'post_status'       => 'publish',
            'post_type'         => self::$post_type,
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key'     => HELPDOCS_GO_PF.'site_location',
                    'value'   => base64_encode( 'main' ),
                ],
                [
                    'key'     => HELPDOCS_GO_PF.'toc',
                    'value'   => true,
                ],
            ]
        ];
        $docs = get_posts( $args );

        // Also get the imports
        $imports = helpdocs_get_imports();

        // Merge them together
        if ( !empty( $imports ) ) {
            $docs = array_merge( $docs, $imports );
        }

        // Did we find any?
        if ( !empty( $docs ) ) {

            // First we sort by the doc order
            usort( $docs, function( $a, $b ) { return strcmp( $a->helpdocs_order, $b->helpdocs_order ) ; } );

            // CSS
            echo '<style>
            .toc-cont ul li {
                margin-bottom: 0;
            }
            .toc-item {
                display: block;
                width: calc( 100% - 20px );
                padding: 10px;
                background: #F9F9F9;
                border: 1px solid #ccc;
            }
            .toc-item .dashicons {
                font-size: 18px;
                margin-right: 3px;
                color: #999999;
            }
            </style>';

            // Start the container
            $results = '<div class="toc-cont"><ul>';

            // Iter the docs
            foreach ( $docs as $doc ) {

                // Check imports
                $toc_mk = HELPDOCS_GO_PF.'toc';
                if ( isset( $doc->$toc_mk ) && !$doc->$toc_mk ) {
                    continue;
                }

                // If imported
                if ( isset( $doc->auto_feed ) && $doc->auto_feed != '' ) {
                    $incl_feed = '&feed=true';
                    $icon = 'dashicons-cloud';
                } else {
                    $incl_feed = '';
                    $icon = sanitize_key( get_option( HELPDOCS_GO_PF.'dashicon', 'dashicons-editor-help' ) );
                }

                // Get the title and link
                $title = $doc->post_title;
                $link = helpdocs_plugin_options_path( 'documentation' ).'&id='.$doc->ID.$incl_feed;

                // Add the link
                $results .= '<li><a class="toc-item" href="'.$link.'"><span class="dashicons '.$icon.'"></span> '.$title.'</a></li>';
            }

            // End the container
            $results .= '</ul></div>';

            // Return it
            echo wp_kses_post( $results );

        // No docs found
        } else {
            echo '<br>You have not added any help docs. To do so, edit the docs you want to edit and choose "Add to Dashboard Table of Contents" under "Location" settings.';
        }
        
    } // End dashboard_content()
}