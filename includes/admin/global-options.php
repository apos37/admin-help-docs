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
            'dashicon',
            'logo',
            'page_title',
            'menu_title',
            'multisite_sfx',
            'hide_version',
            'menu_position',
            'footer_left',
            'footer_right',
            'edit_roles',
            'disable_user_prefs',
            'copy_from'
        ];
        $this->settings_general = array_merge( $this->settings_general, $this->colors );

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
        $this->register_group_settings( 'settingsie', [ 'import_link', 'import_date' ] );
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