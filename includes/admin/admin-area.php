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
        $text_domain = HELPDOCS_TEXTDOMAIN;
        if ( $text_domain . '/' . $text_domain . '.php' == $file ) {

            $guide_url = HELPDOCS_GUIDE_URL;
            $docs_url = HELPDOCS_DOCS_URL;
            $support_url = HELPDOCS_SUPPORT_URL;
            $plugin_name = HELPDOCS_NAME;

            $our_links = [
                'guide' => [
                    // translators: Link label for the plugin's user-facing guide.
                    'label' => __( 'How-To Guide', 'admin-help-docs' ),
                    'url'   => $guide_url
                ],
                'docs' => [
                    // translators: Link label for the plugin's developer documentation.
                    'label' => __( 'Developer Docs', 'admin-help-docs' ),
                    'url'   => $docs_url
                ],
                'support' => [
                    // translators: Link label for the plugin's support page.
                    'label' => __( 'Support', 'admin-help-docs' ),
                    'url'   => $support_url
                ],
            ];

            $row_meta = [];
            foreach ( $our_links as $key => $link ) {
                // translators: %1$s is the link label, %2$s is the plugin name.
                $aria_label = sprintf( __( '%1$s for %2$s', 'admin-help-docs' ), $link[ 'label' ], $plugin_name );
                $row_meta[ $key ] = '<a href="' . esc_url( $link[ 'url' ] ) . '" target="_blank" aria-label="' . esc_attr( $aria_label ) . '">' . esc_html( $link[ 'label' ] ) . '</a>';
            }

            // Add the links
            return array_merge( $links, $row_meta );
        }

        // Return the links
        return (array) $links;
    } // End plugin_row_meta()

}