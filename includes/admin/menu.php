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
        // Only view
        if ( !helpdocs_user_can_view() ) {
            return;
        }

        // The icon
        $icon = get_option( HELPDOCS_GO_PF.'dashicon', 'dashicons-editor-help' );

        // Get the menu title
        $menu_title = helpdocs_menu_title();

        // Get the menu title
        if ( get_option( HELPDOCS_GO_PF.'menu_position' ) && get_option( HELPDOCS_GO_PF.'menu_position' ) != '' ) {
            $position = get_option( HELPDOCS_GO_PF.'menu_position' );
        } else {
            $position = 2;
        }

        // Capability requirement
        $capability = 'manage_options';
        if ( !helpdocs_has_role( 'administrator' ) ) {
            if ( get_option( HELPDOCS_GO_PF.'user_view_cap' ) && get_option( HELPDOCS_GO_PF.'user_view_cap' ) != '' ) {
                $capability = get_option( HELPDOCS_GO_PF.'user_view_cap' );
            }
        }

        // Add a new top level menu link to the ACP
        add_menu_page(
            $menu_title,                // Title of the page
            $menu_title,                // Text to show on the menu link
            $capability,                // Capability requirement to see the link
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
            $submenu[ $this->slug ][] = [ $menu_item[0], $capability, $link ];
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

        // Holder taxonomy
        } elseif ( $current_screen->id == 'edit-help-docs-folder' ) {
            $submenu_file = 'edit-tags.php?taxonomy=help-docs-folder';
            $parent_file = helpdocs_plugin_options_short_path();

        // Post Type Submenus
        } elseif ( $current_screen->post_type == 'help-docs' ) {
            $submenu_file = 'edit.php?post_type=help-docs';
            $parent_file = helpdocs_plugin_options_short_path();
        } elseif ( $current_screen->post_type == 'help-doc-imports' ) {
            $submenu_file = 'edit.php?post_type=help-doc-imports';
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
    // Get add new link
    $add_new_link = home_url( HELPDOCS_ADMIN_URL.'/post-new.php?post_type='.(new HELPDOCS_DOCUMENTATION)->post_type );

    // The menu items
    // Set 3rd param to true if the item should only be visible to admins
    // Set 4th param to true if the item should not be added to the menu or tabs, but is a hidden subpage
    $items = [
        'documentation'     => [ __( 'Documentation', 'admin-help-docs' ), '<a href="'.esc_url( $add_new_link ).'" class="page-title-action">Add New</a>' ],
        'manage'            => [ __( 'Manage', 'admin-help-docs' ), '', true, false, 'edit.php?post_type=help-docs' ],
        'folders'           => [ __( 'Folders', 'admin-help-docs' ), '', true, false, 'edit-tags.php?taxonomy=help-docs-folder' ],
        'imports'           => [ __( 'Imports', 'admin-help-docs' ), '<p>'.__( 'You can easily import documents from another site.' ).'</p>', true, false, 'edit.php?post_type=help-doc-imports' ],
        'adminmenu'         => [ __( 'Admin Menu', 'admin-help-docs' ), '<p>'.__( 'Sort the left admin menu parent items.', 'admin-help-docs' ).'</p>', true ],
        'settings'          => [ __( 'Settings', 'admin-help-docs' ), '', true ],
        'settingsie'        => [ __( 'Import/Export Settings', 'admin-help-docs' ), '<p>'.__( 'You can easily import settings from another site. Just copy the settings link from the other site and paste it in the field below.' ).'</p>', false, true ],
        'faq'               => [ __( 'FAQ', 'admin-help-docs' ), '<p>'.__( 'Frequently Asked Questions', 'admin-help-docs' ).'</p>', true ],
        'about'             => [ __( 'About', 'admin-help-docs' ), '<p>'.__( 'Version', 'admin-help-docs' ).' '.esc_attr( HELPDOCS_VERSION ).' - <a href="'.helpdocs_plugin_options_path( 'changelog' ).'">View the Changelog</a>'.'</p>', true ],
        'changelog'         => [ __( 'Changelog', 'admin-help-docs' ), '<p>'.__( 'Updates to this plugin.', 'admin-help-docs' ).'</p>', false, true ],
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