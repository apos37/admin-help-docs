<?php
/**
 * Gravity Form Merge Tags class
 */

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Initiate the class
 */
new HELPDOCS_GF_MERGETAGS;


/**
 * Main plugin class.
 */
class HELPDOCS_GF_MERGETAGS {

    /**
     * Merge tags
     *
     * @var string
     */
    public $merge_tags;


    /**
	 * Constructor
	 */
	public function __construct() {

        // Merge tags
        if ( get_option( HELPDOCS_GO_PF.'gf_merge_tags' ) && sanitize_textarea_field( get_option( HELPDOCS_GO_PF.'gf_merge_tags' ) ) != '' ) {
            $this->merge_tags = sanitize_textarea_field( get_option( HELPDOCS_GO_PF.'gf_merge_tags' ) );
        } else {
            $this->merge_tags = 'User First Name (first_name), User Last Name (last_name), User Date Registered (user_registered)';
        }

        // Add merge tags
        add_filter( 'gform_custom_merge_tags', [ $this, 'merge_tags' ], 10, 4 );

	} // End __construct()


    /**
     * Add our own merge tags
     *
     * @param array $merge_tags
     * @param [type] $form_id
     * @param [type] $fields
     * @param [type] $element_id
     * @return array
     */
    public function merge_tags( $merge_tags, $form_id, $fields, $element_id ) {
        // Get the merge tag string
        $string = $this->merge_tags;

        // Explode them
        $tags = explode( ',', $string );

        // Iter the merge tag args
        foreach ( $tags as $tag ) {

            // Let's trim
            $tag = trim( $tag );

            // Get the meta key
            preg_match( '#\((.*?)\)#', $tag, $match );
            $meta_key = $match[1];

            // Make sure we have a match
            if ( $meta_key ) {

                // Remove the meta key to get the label
                $label = str_replace( '('.$meta_key.')', '', $tag );

                // Let's trim the label
                $label = trim( $label );

                // Add the merge tags
                $merge_tags[] = [ 
                    'label'   => $label, 
                    'tag'     => '{user:'.$meta_key.'}'
                ];
            }
        }

        // Return the merge tags
        return $merge_tags;
    } // End merge_tags()
}