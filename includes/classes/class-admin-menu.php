<?php
/**
 * Admin Menu Sorting
 */

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Initiate the class
 */
new HELPDOCS_ADMIN_MENU;


/**
 * Main plugin class.
 */
class HELPDOCS_ADMIN_MENU {

    /**
	 * Constructor
	 */
	public function __construct() {

        add_filter( 'custom_menu_order', [ $this, 'enable_custom_menu_order' ] );
        add_action( 'admin_menu', [ $this, 'separators' ], 999 );
        add_filter( 'menu_order', [ $this, 'apply_menu_order' ] );
        add_filter( 'admin_body_class', [ $this, 'add_body_class' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_separator_styles' ] );

	} // End __construct()


    /**
     * Enable custom menu order
     */
    public function enable_custom_menu_order( $enabled ) {
        return filter_var( get_option( HELPDOCS_GO_PF . 'enable_admin_menu_sorting', false ), FILTER_VALIDATE_BOOLEAN ) ? true : $enabled;
    } // End enable_custom_menu_order()


    /**
     * Add/remove separators on the admin menu based on saved order
     */
    public function separators() {
        global $menu;

        $saved_order = get_option( HELPDOCS_GO_PF . 'admin_menu_order' );
        if ( empty( $saved_order ) || ! is_array( $saved_order ) ) {
            return;
        }

        // Add extra separators if they don't exist
        foreach ( $saved_order as $slug ) {
            if ( str_starts_with( (string) $slug, 'separator' ) ) {
                if ( ! $this->separator_exists( $slug ) ) {
                    $menu[] = [
                        '',                 // page title
                        'read',             // capability
                        $slug,              // menu slug
                        '',                 // menu title
                        'wp-menu-separator' // class
                    ];
                }
            }
        }

        // Compute last index of a real menu item
        $last_non_separator_index = $this->get_last_non_separator_index( $menu );

        // Add a "hidden" class to separators below the last real item
        foreach ( $menu as $index => $item ) {
            if ( isset( $item[4] ) && $item[4] === 'wp-menu-separator' && $index > $last_non_separator_index ) {
                $menu[ $index ][4] .= ' helpdocs-hidden-separator';
            }
        }
    } // End separators()

    
    /**
     * Get the index of the last non-separator menu item
     */
    private function get_last_non_separator_index( $menu ) {
        for ( $i = count( $menu ) - 1; $i >= 0; $i-- ) {
            if ( empty( $menu[ $i ][4] ) || $menu[ $i ][4] !== 'wp-menu-separator' ) {
                return $i;
            }
        }
        return -1;
    } // End get_last_non_separator_index()


    /**
     * Check if a separator with the given slug exists in the admin menu
     */
    private function separator_exists( $slug ) {
        global $menu;
        foreach ( $menu as $item ) {
            if ( isset( $item[2] ) && $item[2] === $slug ) {
                return true;
            }
        }
        return false;
    } // End separator_exists()


    /**
     * Apply the saved admin menu order
     */
    public function apply_menu_order( $menu_order ) {
        $saved_order = get_option( HELPDOCS_GO_PF . 'admin_menu_order' );
        if ( empty( $saved_order ) || ! is_array( $saved_order ) ) {
            return $menu_order;
        }

        $ordered = [];
        $remaining = [];

        // Separate out any extra separators
        $extra_separators = [];
        foreach ( $saved_order as $slug ) {
            if ( str_starts_with( (string) $slug, 'separator' ) ) {
                $extra_separators[] = $slug;
            }
        }

        // Collect remaining items
        foreach ( $menu_order as $slug ) {
            if ( in_array( $slug, $saved_order, true ) ) {
                continue;
            }
            $remaining[] = $slug;
        }

        // Build ordered list
        foreach ( $saved_order as $slug ) {
            if ( in_array( $slug, $menu_order, true ) || str_starts_with( (string) $slug, 'separator' ) ) {
                $ordered[] = $slug;
            }
        }

        // Inject an extra separator above remaining items if any
        if ( ! empty( $remaining ) && ! empty( $extra_separators ) ) {
            foreach ( $extra_separators as $sep ) {
                if ( ! in_array( $sep, $ordered, true ) ) {
                    array_unshift( $remaining, $sep );
                }
            }
        }

        return array_merge( $ordered, $remaining );
    } // End apply_menu_order()


    /**
     * Add body class if separator coloring is enabled
     */
    public function add_body_class( $classes ) {
        if ( get_option( HELPDOCS_GO_PF . 'colorize_separators' ) ) {
            $classes .= ' helpdocs-separator-enabled';
        }
        return $classes;
    } // End add_body_class()


    /**
     * Enqueue styles for colored separators
     */
    public function enqueue_separator_styles() {
        $sep_color = get_option( HELPDOCS_GO_PF . 'color_admin_menu_sep', '#d1d1d1' );
        $custom_css = "
            #adminmenu div.wp-menu-separator.helpdocs-hidden-separator {
                display: none;
            }
            .helpdocs-separator-enabled #adminmenu div.separator {
                padding: 0;
                border-top: 1px solid " . esc_html( $sep_color ) . ";
                width: 90%;
                margin: 8px auto;   
                height: 0;
                opacity: 0.37;
                background: transparent;
            }
        ";

        wp_add_inline_style( 'wp-admin', $custom_css );
    } // End enqueue_separator_styles()
    
}