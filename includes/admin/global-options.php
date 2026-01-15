<?php
/**
 * Global options class file.
 */

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Initiate the class
 */
new HELPDOCS_GLOBAL_OPTIONS;


/**
 * Main plugin class.
 */
class HELPDOCS_GLOBAL_OPTIONS {

    /**
     * Color keys
     * 
     * @var array
     */
    public $colors = [];


    /**
     * General setting keys
     *
     * @var array
     */
    public $settings_general = [];


    /**
     * Admin menu setting keys
     *
     * @var array
     */
    public $settings_adminmenu = [];


    /**
	 * Constructor
	 */
	public function __construct() {

        // Define the color keys
        $this->colors = [
            'color_ac',
            'color_bg',
            'color_ti',
            'color_fg',
            'color_cl'
        ];

        // Define the other keys
        $this->settings_general = [
            'admin_bar',
            'dashboard_toc',
            'gutenberg_editor',
            'enqueue_frontend_styles',
            'dashicon',
            'logo',
            'page_title',
            'menu_title',
            'multisite_sfx',
            'hide_version',
            'menu_position',
            'footer_left',
            'footer_right',
            'user_view_cap',
            'edit_roles',
            'api',
            'curly_quotes',
            'auto_htoc',
            'user_prefs',
            'gf_merge_tags',
            'copy_from',
            'default_doc',
            'hide_doc_meta'
        ];
        
        // Merge colors into general settings
        $this->settings_general = array_merge( $this->settings_general, $this->colors );

        // Define the admin menu keys
        $this->settings_adminmenu = [
            'enable_admin_menu_sorting',
            'colorize_separators',
            'color_admin_menu_sep',
            'admin_menu_order'
        ];

        // Call register settings function
        add_action( 'admin_init', [ $this, 'register_settings' ] );

	} // End __construct()


    /**
     * Register settings
     * Do not need to include the prefix
     *
     * @return void
     */
    public function register_settings() {
        // General Settings
        $this->register_group_settings( 'settings', $this->settings_general );

        // Settings Import/Export
        $this->register_group_settings( 'settingsie', [ 'import_link' ] );

        // Admin Menu Order
        $this->register_group_settings( 'adminmenu', $this->settings_adminmenu );
    } // End register_settings()


    /**
     * Register group settings
     * 
     * @return void
     */
    public function register_group_settings( $group_name = 'options', $options = [] ) {   
        if ( !empty( $options ) ) {
            foreach ( $options as $option ) {
                register_setting( HELPDOCS_PF.'group_'.$group_name, HELPDOCS_GO_PF.$option );
            }
        }
    } // End register_group_settings
}