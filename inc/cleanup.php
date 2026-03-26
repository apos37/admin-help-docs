<?php
/**
 * Cleanup
 */

namespace PluginRx\AdminHelpDocs;

if ( ! defined( 'ABSPATH' ) ) exit;

class Cleanup {

    /**
     * Run the cleanup process
     */
    public static function run() {
        self::delete_all_transients();
        self::delete_all_docs();
        self::delete_all_imports();
        self::delete_all_prefixed_options();

        error_log( 'Admin Help Docs: ' . __( 'Cleanup complete. All plugin data has been removed after uninstallation.', 'admin-help-docs' ) );
    } // End run()


    /**
     * Delete all transients related to the plugin
     */
    private static function delete_all_transients() {
        global $wpdb;
        $transients = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_helpdocs_%'" ); // phpcs:ignore
        foreach ( $transients as $transient ) {
            $transient_name = str_replace( '_transient_', '', $transient );
            delete_transient( $transient_name );
        }
    } // End delete_all_transients()


    /**
     * Delete all help docs posts
     */
    private static function delete_all_docs() {
        $args = [
            'post_type'      => 'help-docs',
            'post_status'    => 'any',
            'numberposts'    => -1,
            'fields'         => 'ids',
        ];
        $docs = get_posts( $args );
        foreach ( $docs as $doc_id ) {
            wp_delete_post( $doc_id, true );
        }
    } // End delete_all_docs()


    /**
     * Delete all imports
     */
    private static function delete_all_imports() {
        $args = [
            'post_type'      => 'help-doc-imports',
            'post_status'    => 'any',
            'numberposts'    => -1,
            'fields'         => 'ids',
        ];
        $docs = get_posts( $args );
        foreach ( $docs as $doc_id ) {
            wp_delete_post( $doc_id, true );
        }
    } // End delete_all_imports()


    /**
     * Delete all options starting with helpdocs_
     */
    private static function delete_all_prefixed_options() {
        global $wpdb;

        $options = $wpdb->get_col( // phpcs:ignore
            $wpdb->prepare( // phpcs:ignore
                "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s", 
                'helpdocs_%' 
            ) 
        );

        if ( ! empty( $options ) ) {
            foreach ( $options as $option ) {
                delete_option( ( $option ) );
            }
        }
    } // End delete_all_prefixed_options()

}