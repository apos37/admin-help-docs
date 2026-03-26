<?php
/**
 * Function: admin_help_doc( $id )
 */

if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Display a help doc manually via ID.
 * Assumes site_location is set to 'function'.
 * * @param int $id The help doc ID.
 */
function admin_help_doc( $id ) {
    // Basic security check
    if ( ! is_admin() ) {
        return;
    }

    $doc = \PluginRx\AdminHelpDocs\Helpers::get_doc( $id );
    
    if ( $doc ) {
        $content = apply_filters( 'the_content', $doc->post_content );
        echo wp_kses_post( \PluginRx\AdminHelpDocs\Helpers::output_doc( $doc->ID, $doc->post_title, $content, 'manual' ) );
    }
} // End admin_help_doc()