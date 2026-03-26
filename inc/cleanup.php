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
        self::delete_all_docs();
        self::delete_all_transients();

        $options = self::get_all_options();
        foreach ( $options as $option_list ) {
            foreach ( $option_list as $option ) {
                self::delete_option( $option );
            }
        }
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
            'post_type'      => HelpDocs::$post_type,
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
     * Only delete old options, not the current ones
     */
    public static function delete_old_options() {
        $options = self::get_deprecated_options();
        foreach ( $options as $option ) {
            self::delete_option( $option );
        }
    } // End delete_old_options()


    /**
     * Get all option keys used by the plugin
     *
     * @return array
     */
    public static function get_all_options( $incl_old = true ) {
        $settings = Settings::setting_fields();
        
        $current_options = array_map( function( $field ) {
            return $field[ 'type' ] !== 'color' ? ( $field[ 'name' ] ?? null ) : null;
        }, $settings );
        $current_options = array_filter( $current_options ); // Remove null values

        $additional_options = [
            'helpdocs_support_log',
            'helpdocs_colors',
            'helpdocs_editor_type'
        ];
        $current_options = array_merge( $current_options, $additional_options );

        $old_options = self::get_deprecated_options();

        return $incl_old ? array_merge( $current_options, $old_options ) : $current_options;
    } // End get_all_options()


    /**
     * Get old option keys that may still be in the database
     * @return array
     */
    public static function get_deprecated_options() {
        return [
            'color_ac',
            'color_bg',
            'color_fg',
            'color_ti',
            'color_cl',
            'multisite_sfx',
            'gf_merge_tags',
            'user_prefs'
        ];
    } // End get_deprecated_options()


    /**
     * Helper function to delete an option if it exists.
     *
     * @param string $option_name The option name to delete.
     * @param string $prefix The prefix to use (default is 'helpdocs_').
     */
    private static function delete_option( $option_name, $prefix = 'helpdocs_' ) {
        if ( get_option( $prefix . $option_name ) !== false ) {
            delete_option( $prefix . $option_name );
        }
    } // End delete_option()

}