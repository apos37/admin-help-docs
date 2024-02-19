<?php
/**
 * Colors class
 * 
 * USAGE:
 * $HELPDOCS_COLORS = new HELPDOCS_COLORS();
 * $value = $HELPDOCS_COLORS->get( $color );
 */

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Main plugin class.
 */
class HELPDOCS_COLORS {

    /**
     * Get a hex color from option or default
     *
     * @param string $color
     * @return string
     */
    public function get( $color ) {
        // Assign the defaults
        $colors = [
            'ac' => '#2F76DB', // Light blue
            'bg' => '#FBFBFB', // Snow white
            'fg' => '#1D2327', // WP admin menu darkest
            'ti' => '#1F2D5A', // Midnight blue
            'cl' => '#2F76DB', // Light blue
        ];

        // Check that the color exists
        if ( array_key_exists( $color, $colors ) ) {

            // Check if the option exists
            if ( get_option( HELPDOCS_GO_PF.'color_'.$color ) && get_option( HELPDOCS_GO_PF.'color_'.$color ) != '' ) {

                // Return the option color
                return get_option( HELPDOCS_GO_PF.'color_'.$color );

            // Otherwise get the default
            } else {
                return $colors[ $color ];
            }

        // Oops! Color id does not exist
        } else {
            return false;
        }
    } // End get()
}