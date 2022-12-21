<?php
/**
 * Admin menu class file.
 */

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Initiate the class
 */
if ( !is_network_admin() ) {
    new HELPDOCS_MENU;
}


/**
 * Main plugin class.
 */
class HELPDOCS_MENU {

    /**
     * The menu slug
     *
     * @var string
     */
    public $slug;
    

    /**
	 * Constructor
	 */
	public function __construct() {
        // Define the menu slug
        $this->slug = HELPDOCS_TEXTDOMAIN;

        // Add the menu
        $hook = is_network_admin() ? 'network_' : '';
        add_action( $hook.'admin_menu', [ $this, 'admin_menu' ] );

        // Fix the Manage link to show active
        add_filter( 'parent_file', [ $this, 'submenus' ] );
	} // End __construct()


    /**
     * Add options menu to the Admin Control Panel
     * 
     * @return void
     */
    public function admin_menu() {
        // The icon
        // https://developer.wordpress.org/resource/dashicons/
        $icon = get_option( HELPDOCS_GO_PF.'dashicon', 'dashicons-editor-help' );
        // $icon = HELPDOCS_PLUGIN_IMG_PATH.'icon-15x15.png';

        /**
         * To make an SVG from a Font Awesome icon:
         * Go to: http://fontawesome.io/icons/
         * Pick an icon
         * Go to: https://github.com/encharm/Font-Awesome-SVG-PNG/tree/master/black/svg
         * Click the icon, then click <> in Github to see the raw code instead of the image
         * Copy the <svg><path/></svg> code to use below
         * Change the width and height to 20x20
         * Add fill="white" to the path's element
         */

         /**
         * To get <svg> for encoding from Adobe Illustrator:
         * Create the svg and open the .svg in notepad to get the code
         * Remove the <?xml tag
         * Replace the id attribute from the <svg> tag with width="20" height="20"
         * Remove the <defs> tag with the styles inside it
         * Remove the class from the first path
         * Combine all of the path d="" attributes together in one long path.
         * Add fill="#9CA2A7" or fill="white" to the path's element
         */
        // $icon_base64 = base64_encode('<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 190.32 190.32"><path fill="#9CA2A7" d="M168.96,16.08H21.84c-5.7,0-10.32,4.62-10.32,10.32l-.24,94.56c0,5.7,4.62,10.32,10.32,10.32h46.23l22.21,26.09c2.68,3.15,7.55,3.15,10.23,0l22.21-26.09h46.23c5.7,0,10.32-4.62,10.32-10.32l.24-94.56c0-5.7-4.62-10.32-10.32-10.32Z"/></svg>');
        // $icon = 'data:image/svg+xml;base64,' . $icon_base64;

        // Get the menu title
        if ( get_option( HELPDOCS_GO_PF.'menu_title' ) && get_option( HELPDOCS_GO_PF.'menu_title' ) != '' ) {
            $menu_title = get_option( HELPDOCS_GO_PF.'menu_title' );
        } else {
            $menu_title = 'Help Docs';
        }

        // Get the menu title
        if ( get_option( HELPDOCS_GO_PF.'menu_position' ) && get_option( HELPDOCS_GO_PF.'menu_position' ) != '' ) {
            $position = get_option( HELPDOCS_GO_PF.'menu_position' );
        } else {
            $position = 2;
        }

        // Add a new top level menu link to the ACP
        add_menu_page(
            $menu_title,                // Title of the page
            $menu_title,                // Text to show on the menu link
            'manage_options',           // Capability requirement to see the link
            $this->slug,                // The 'slug' (file to display when clicking the link)
            [ $this, 'options_page' ],  // Function to call
            $icon,                      // The admin menu icon
            $position                   // Position on the menu
        );

        // Fetch the global submenu
        global $submenu;

        // Get the menu items
        $menu_items = helpdocs_plugin_menu_items();

        // Skip if multisite
        $multisite_skip = [];

        // Add them
        foreach ( $menu_items as $key => $menu_item ) {
            
            // Skip if multisite
            if ( is_network_admin() && in_array( $key, $multisite_skip ) ) {
                continue;
            }

            // Skip if a hidden subpage
            if ( isset( $menu_item[3] ) && $menu_item[3] == true ) {
                continue;
            }

            // Check if they have access
            if ( !helpdocs_user_can_edit() && isset( $menu_item[2] ) && $menu_item[2] == true ) {
                continue;
            }

            //  The link
            if ( isset( $menu_item[4] ) && $menu_item[4] != '' ) {
                $link = $menu_item[4];
            } else {
                $link = 'admin.php?page='.HELPDOCS_TEXTDOMAIN.'&tab='.$key;
            }

            // Add the menu item
            $submenu[ $this->slug ][] = [ $menu_item[0], 'manage_options', $link ];
        }
    } // End admin_menu()


    /**
     * Call the options page
     *
     * @return void
     */
    public function options_page() {
        include HELPDOCS_PLUGIN_ADMIN_PATH.'options.php';
    } // End options_page()


    /**
     * Fix the Manage link to show active
     *
     * @param string $parent_file
     * @return string
     */
    public function submenus( $parent_file ) {
        // Get the global vars
        global $submenu_file, $current_screen;

        // Get the options page
        $options_page = 'toplevel_page_'.HELPDOCS_TEXTDOMAIN;

        // Allow for multisite
        if ( is_network_admin() ) {
            $options_page .= '-network';
        }

        // Help Docs
        if ( $current_screen->id == $options_page ) {
            $tab = helpdocs_get( 'tab' ) ?? '';
            $submenu_file = 'admin.php?page='.HELPDOCS_TEXTDOMAIN.'&tab='.$tab;

        // Post Type Submenu
        } elseif ( $current_screen->post_type == 'help-docs' ) {
            $submenu_file = 'edit.php?post_type=help-docs';
            $parent_file = helpdocs_plugin_options_short_path();
        }
        return $parent_file;
    } // End submenus()
}


/**
 * Plugin menu items / tabs
 * [ Menu item name, item slug ]
 *
 * @param string $slug
 * @return string|array
 */
function helpdocs_plugin_menu_items( $slug = null, $desc = false ) {
    // The menu items
    // Set 3rd param to true if the item should only be visible to admins
    // Set 4th param to true if the item should not be added to the menu or tabs, but is a hidden subpage
    $items = [
        'documentation'     => [ __( 'Documentation', 'admin-help-docs' ), '' ],
        'manage'            => [ __( 'Manage', 'admin-help-docs' ), '', true, false, 'edit.php?post_type=help-docs' ],
        'faq'               => [ __( 'FAQ', 'admin-help-docs' ), __( 'Frequently Asked Questions', 'admin-help-docs' ), true ],
        'settings'          => [ __( 'Settings', 'admin-help-docs' ), '', true ],
        'settingsie'        => [ __( 'Import/Export Settings', 'admin-help-docs' ), __( 'You can easily import settings from another site. Just copy the settings link from the other site and paste it in the field below.' ), false, true ],
        // 'developer'         => [ __( 'Developer', 'admin-help-docs' ), __( 'Action and filters available for developers.', 'admin-help-docs' ), true ],
        'about'             => [ __( 'About', 'admin-help-docs' ), __( 'Version', 'admin-help-docs' ).' '.esc_attr( HELPDOCS_VERSION ).' - <a href="'.helpdocs_plugin_options_path( 'changelog' ).'">View the Changelog</a>', false ],
        'changelog'         => [ __( 'Changelog', 'admin-help-docs' ), __( 'Updates to this plugin.', 'admin-help-docs' ), false, true ],
    ];

    if ( !is_null( $slug ) ) {
        if ( $desc ) {
            return $items[$slug][1];
        } else {
            return $items[$slug][0];
        }
    } else {
        return $items;
    }
} // End menu_items()