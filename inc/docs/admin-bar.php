<?php
/**
 * Admin Bar
 */

namespace PluginRx\AdminHelpDocs;

if ( ! defined( 'ABSPATH' ) ) exit;

class AdminBar {

    private static $trucated_title_length = 50;


    /**
     * The single instance of the class
     *
     * @var self|null
     */
    private static ?AdminBar $instance = null;


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
        self::$trucated_title_length = apply_filters( 'helpdocs_admin_bar_truncated_title_length', self::$trucated_title_length );

        $do_backend = filter_var( get_option( 'helpdocs_admin_bar' ), FILTER_VALIDATE_BOOLEAN ) && is_admin();
        $do_frontend = filter_var( get_option( 'helpdocs_admin_bar_frontend' ), FILTER_VALIDATE_BOOLEAN ) && ! is_admin();
        
        if ( ( $do_backend || $do_frontend ) && Helpers::user_can_view() ) {
            add_action( 'admin_bar_menu', [ $this, 'admin_bar' ], 100 );
        }
    } // End __construct()


    /**
     * Customize Admin Bar
     *
     * @param object $wp_admin_bar
     * @return void
     */
    public function admin_bar( $wp_admin_bar ) {
        $title = Helpers::get_menu_title();
        
        $dashicon = Helpers::get_icon();

        $parent_id = 'helpdocs_admin_bar';
        
        $icon_html = '<span class="ab-icon ' . esc_attr( $dashicon ) . '" style="top: 2px;"></span>';
        $label_html = '<span class="screen-reader-text">' . esc_html( $title ) . '</span>';

        $has_new_help_doc_link = Helpers::user_can_edit() && ! Helpers::is_our_screen() && is_admin();

        $wp_admin_bar->add_node( [
            'id'    => $parent_id,
            'title' => $icon_html . $label_html,
            'href'  => Bootstrap::tab_url( 'documentation' ),
            'meta'  => [
                'target' => '_blank',
                'title'  => esc_attr( $title ), // This adds the native browser tooltip,
                'class'  => $has_new_help_doc_link ? ' has-add-new-link' : '',
            ],
        ] );

        $docs = Helpers::get_docs( [
            'site_location' => 'admin_bar',
        ] );
        if ( ! empty( $docs ) ) {

            $include_content = filter_var( get_option( 'helpdocs_admin_bar_include_content' ), FILTER_VALIDATE_BOOLEAN );

            usort( $docs, function( $a, $b ) { return strcmp( $a->helpdocs_order, $b->helpdocs_order ) ; } );

            foreach ( $docs as $key => $doc ) {
                $content = get_the_excerpt( $doc );
                $href    = filter_var( $content, FILTER_VALIDATE_URL ) ? $content : false;
                $suffix  = '';

                if ( ! $href && '' !== $content && $include_content ) {
                    $suffix = ' — ' . esc_html( wp_html_excerpt( wp_strip_all_tags( $content ), self::$trucated_title_length, '...' ) );
                }

                // If no URL found in content, check for internal documentation link
                if ( ! $href ) {
                    $locations = get_post_meta( $doc->ID, 'helpdocs_locations', true );
                    if ( is_array( $locations ) ) {
                        foreach ( $locations as $loc ) {
                            if ( base64_encode( 'main' ) === ( $loc[ 'site_location' ] ?? '' ) ) {
                                $href = add_query_arg( 'id', absint( $doc->ID ), Bootstrap::tab_url( 'documentation' ) );
                                break;
                            }
                        }
                    }
                }

                $wp_admin_bar->add_node( [
                    'id'     => 'helpdocs_' . $key,
                    'parent' => $parent_id,
                    'title'  => esc_html( $doc->post_title ) . $suffix,
                    'href'   => $href,
                    'meta'   => [ 'target' => '_blank' ],
                ] );
            }
        }

        if ( $has_new_help_doc_link ) {
            global $pagenow;
            $screen = get_current_screen();
            
            $all_locations = HelpDocs::site_locations();
            
            $relative_path = ( in_array( $pagenow, [ 'post.php', 'post-new.php' ] ) ) ? 'post.php' : ( ( $pagenow === 'edit.php' ) ? 'edit.php' : $pagenow );

            $is_standard = array_key_exists( $relative_path, $all_locations );
            
            $url_params = [
                'post_type' => HelpDocs::$post_type,
                'slpt'      => ( $screen ) ? $screen->post_type : '',
            ];

            if ( $is_standard ) {
                $url_params[ 'site_location' ] = base64_encode( $relative_path );
            } else {
                $url_params[ 'site_location' ] = base64_encode( 'custom' );
    
                $clean_get = wp_unslash( $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

                unset( 
                    $clean_get[ '_wp_http_referer' ], 
                    $clean_get[ 'settings-updated' ], 
                    $clean_get[ 'wp_http_referer' ] 
                );

                $current_url = admin_url( $pagenow );

                if ( ! empty( $clean_get ) ) {
                    $current_url = add_query_arg( map_deep( $clean_get, 'sanitize_text_field' ), $current_url );
                }
                
                $final_url = remove_query_arg( [ '_wp_http_referer', 'settings-updated' ], $current_url );

                $url_params[ 'custom_url' ] = urlencode( $final_url );
            }

            $add_new_url = add_query_arg( $url_params, admin_url( 'post-new.php' ) );

            $wp_admin_bar->add_node( [
                'id'     => 'helpdocs_add_new',
                'parent' => $parent_id,
                'title'  => '<span class="ab-icon dashicons-plus-alt"></span>' . __( 'Add help doc to this page', 'admin-help-docs' ),
                'href'   => $add_new_url,
                'meta'   => [ 'target' => '_blank', 'class' => 'helpdocs-add-new-admin-bar' ],
            ] );
        }
    } // End admin_bar()

    
    /**
     * Prevent cloning and unserializing
     */
    public function __clone() {}
    public function __wakeup() {}

}


AdminBar::instance();