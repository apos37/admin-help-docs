<?php
/**
 * Plugins Page
 */

namespace PluginRx\AdminHelpDocs;

if ( ! defined( 'ABSPATH' ) ) exit;

class PluginsPage {

    /**
     * @var string Path to the main plugin file for metadata retrieval
     */
    private $plugin_file = 'admin-help-docs/admin-help-docs.php';


    /**
     * Constructor
     */
    public function __construct() {
        add_filter( 'plugin_action_links_' . $this->plugin_file, [ $this, 'settings_link' ] );
        add_filter( 'plugin_row_meta', [ $this, 'meta_links' ], 10, 2 );
    } // End __construct()


    /**
     * Add a "Settings" link to the plugin's action links on the Plugins page.
     *
     * @param array $links Existing action links for the plugin.
     * @return array Modified action links with the "Settings" link added.
     */
    public function settings_link( $links ) {
        $url = Bootstrap::tab_url( 'settings' );

        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url( $url ),
            esc_html__( 'Settings', 'admin-help-docs' )
        );

        array_unshift( $links, $settings_link );

        return $links;
    } // End settings_link()


	/**
     * Add links to plugin row
     *
     * @param array $links
     * @return array
     */
    public function meta_links( $links, $file ) {
        if ( $this->plugin_file == $file ) {
            $text_domain = Bootstrap::textdomain();
            $plugin_name = Bootstrap::name();
            $base_url    = Bootstrap::author_uri();

            $our_links   = [
                'guide' => [
                    'label' => __( 'How-To Guide', 'admin-help-docs' ),
                    'url'   => "{$base_url}guide/plugin/{$text_domain}",
                ],
                'docs' => [
                    'label' => __( 'Developer Docs', 'admin-help-docs' ),
                    'url'   => "{$base_url}docs/plugin/{$text_domain}",
                ],
                'support' => [
                    'label' => __( 'Support', 'admin-help-docs' ),
                    'url'   => "{$base_url}support/plugin/{$text_domain}",
                ],
            ];

            foreach ( $our_links as $key => $link ) {
                $aria_label = sprintf(
                    // translators: %1$s: Link label, %2$s: Plugin name
                    __( '%1$s for %2$s', 'admin-help-docs' ),
                    $link[ 'label' ],
                    $plugin_name
                );
                $links[ $key ] = '<a href="' . esc_url( $link[ 'url' ] ) . '" target="_blank" aria-label="' . esc_attr( $aria_label ) . '">' . esc_html( $link[ 'label' ] ) . '</a>';
            }
        }

        return (array) $links;
    } // End meta()

}


new PluginsPage();