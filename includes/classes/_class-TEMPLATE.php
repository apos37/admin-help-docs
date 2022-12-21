<?php
/**
 * Class template file. Copy and use for other classes.
 * 
 * USAGE:
 * $HELPDOCS_CLASS_NAME = new HELPDOCS_CLASS_NAME();
 * $value = $HELPDOCS_CLASS_NAME->function( $param );
 */

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Initiate the class
 */
new HELPDOCS_CLASS_NAME;


/**
 * Main plugin class.
 */
class HELPDOCS_CLASS_NAME {

    /**
	 * Constructor
	 */
	public function __construct() {

        // Hooks
        // add_filter( 'filter_name', [$this, 'function_name' ] );

        // Run functions directly
        $this->fake_function();
	} // End __construct()


    /**
     * Function
     * 
     * @return string
     */
    public function fake_function() {
        return false;
    } // End fake_function()
}