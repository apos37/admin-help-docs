<?php
/**
 * Admin area class file.
 * All functions that modify the admin area, that are not related to docs, the admin bar, or user profiles.
 */

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Initiate the class
 */
new HELPDOCS_ADMIN_AREA;


/**
 * Main plugin class.
 */
class HELPDOCS_ADMIN_AREA {

    /**
	 * Constructor
	 */
	public function __construct() {
        // Add a settings link to plugins list page
        add_filter( 'plugin_action_links_'.HELPDOCS_TEXTDOMAIN.'/'.HELPDOCS_TEXTDOMAIN.'.php', [ $this, 'settings_link' ] );

        // Add links to the website and discord
        add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 2 );
        
	} // End __construct()


    /**
     * Add a settings link to plugins list page
     *
     * @param array $links
     * @return array
     */
    public function settings_link( $links ) {
        // Build and escape the URL.
        $url = esc_url( helpdocs_plugin_options_path( 'settings' ) );
        
        // Create the link.
        $settings_link = "<a href='$url'>" . __( 'Settings', 'admin-help-docs' ) . '</a>';
        
        // Adds the link to the end of the array.
        array_unshift(
            $links,
            $settings_link
        );

        // Return the links
        return $links;
    } // End settings_link()


    /**
     * Add links to the website and discord
     *
     * @param array $links
     * @return array
     */
    public function plugin_row_meta( $links, $file ) {
        // Only apply to this plugin
        if ( HELPDOCS_TEXTDOMAIN.'/'.HELPDOCS_TEXTDOMAIN.'.php' == $file ) {

            // Add the link
            $row_meta = [
                'docs' => '<a href="'.esc_url( HELPDOCS_AUTHOR_URL.'wordpress-admin-help-docs/' ).'" target="_blank" aria-label="'.esc_attr__( 'Plugin Website Link', 'admin-help-docs' ).'">'.esc_html__( 'Website', 'admin-help-docs' ).'</a>',
                'discord' => '<a href="'.esc_url( HELPDOCS_DISCORD_SUPPORT_URL ).'" target="_blank" aria-label="'.esc_attr__( 'Plugin Support on Discord', 'admin-help-docs' ).'">'.esc_html__( 'Discord Support', 'admin-help-docs' ).'</a>'
            ];
            return array_merge( $links, $row_meta );
        }

        // Return the links
        return (array) $links;
    } // End plugin_row_meta()

}