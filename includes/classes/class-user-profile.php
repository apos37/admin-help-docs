<?php
/**
 * User Profile Page Class
 */

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Initiate the class
 */
if ( get_option( HELPDOCS_GO_PF.'disable_user_prefs' ) != 1 ) {
    new HELPDOCS_USER_PROFILE;
}


/**
 * Main plugin class.
 */
class HELPDOCS_USER_PROFILE {

    /**
	 * Constructor
	 */
	public function __construct() {

        // Add user options to other users' profiles
        add_action( 'edit_user_profile', [ $this, 'options' ], 1 );

        // Add user options to own profile
        add_action( 'show_user_profile', [ $this, 'options' ] );

	} // End __construct()


    /**
     * Add fields to user options
     *
     * @param object $user
     * @return void
     */
    public function options( $user ) {
        // Update user profile if called
        $this->reset( $user->id );

        // Add some css
        ?>
        <style>
        table.form-table td {
            padding-top: 20px !important;
        }
        </style>
        <?php

        // Get the plugin name
        if ( get_option( HELPDOCS_GO_PF.'page_title' ) && get_option( HELPDOCS_GO_PF.'page_title' ) != '' ) {
            $plugin_name = get_option( HELPDOCS_GO_PF.'page_title' );
        } else {
            $plugin_name = HELPDOCS_NAME;
        }

        // Start the section
        ?>
        <br>
        <h2 id="<?php echo esc_attr( HELPDOCS_TEXTDOMAIN ); ?>"><?php echo esc_attr( $plugin_name ); ?></h2>
        <p>Users can reset the following preferences they have made in the admin area with the defaults: which columns are hidden in admin list tables, which meta boxes are hidden, and where meta boxes are positioned on edit pages.</p>
        <table class="form-table">
        <?php

        // Get the user's meta
        $user_meta = get_user_meta( $user->id );

        // Get the current url without extra params
        if ( $user_id = helpdocs_get( 'user_id' ) ) {
            $current_url = helpdocs_get_current_admin_url( false ).'?user_id='.absint( $user_id );
        } else {
            $current_url = helpdocs_get_current_admin_url( false );
        }

        // Admin list columns
        ?>
            <tr>
                <th>Reset Hidden Admin List Columns</th>
                <td><?php
                // Store them here
                $manageedit_links = [];

                // Iter the user meta
                foreach ( $user_meta as $key => $value ) {

                    // Get only these meta keys
                    if ( str_starts_with( $key, 'manageedit-' ) && str_ends_with( $key, 'columnshidden' ) ) {

                        // Strip the key to get the post type
                        $manageedit_post_type = str_replace( [ 'manageedit-', 'columnshidden' ], '', $key );

                        // Fetch the post type object
                        $post_type_obj = get_post_type_object( $manageedit_post_type );

                        // Check if post type still exists, and is on this site 
                        // Note: user may have additional post type options for different sites if on multisite
                        if ( !is_null( $post_type_obj ) ) {

                            // Get the values
                            $values = unserialize( $value[0] );

                            // Skip empty values
                            if ( empty( $values ) ) {
                                continue;
                            }

                            // Get the post type name
                            $post_type_name = $post_type_obj->labels->name;

                            // Add a link to reset only this post type
                            $manageedit_links[ $post_type_name ] = '<a href="'.esc_url( add_query_arg( 'manageedit', $manageedit_post_type, $current_url ) ).'">'.esc_html( $post_type_name ).' Only</a> — <em>currently hiding: '.esc_html(  implode( ', ', $values ) ).'</em>';
                        }
                    }
                }

                // If none found
                if ( empty( $manageedit_links ) ) {
                    ?><em>None found</em><?php
                } else {

                    // Add the clear all link
                    ?>
                    <a href="<?php echo esc_url( add_query_arg( 'manageedit', 'all', $current_url ) ); ?>">ALL POST TYPES</a><br>
                    <?php

                    // Sort them
                    ksort( $manageedit_links );
                    
                    // Iter them in order
                    foreach ( $manageedit_links as $ml ) {
                        ?>
                        ⤷ <?php echo wp_kses_post( $ml ); ?><br>
                        <?php
                    }
                } ?></td>
            </tr>
        <?php

        // Hidden Meta Boxes
        ?>
            <tr>
                <th>Reset Hidden Meta Boxes</th>
                <td><?php
                // Store them here
                $metaboxhidden_links = [];

                // Iter the user meta
                foreach ( $user_meta as $key => $value ) {

                    // Get only these meta keys
                    if ( str_starts_with( $key, 'metaboxhidden_' ) ) {

                        // Strip the key to get the post type
                        $metaboxhidden_area = str_replace( 'metaboxhidden_', '', $key );

                        // Fetch the post type object
                        $post_type_obj = get_post_type_object( $metaboxhidden_area );

                        // Allowed exceptions
                        $exceptions = [
                            'dashboard'         => 'Dashboard',
                            'dashboard-network' => 'Dashboard (Network)',
                            'nav-menus'         => 'Navigation Menus'
                        ];

                        // Check if post type still exists, and is on this site 
                        // Note: user may have additional post type options for different sites if on multisite
                        if ( !is_null( $post_type_obj ) || array_key_exists( $metaboxhidden_area, $exceptions ) ) {

                            // Get the values
                            $values = unserialize( $value[0] );

                            // Skip empty values
                            if ( empty( $values ) ) {
                                continue;
                            }

                            // Get the post type name
                            if ( !is_null( $post_type_obj ) ) {
                                $name = $post_type_obj->labels->name;
                            } else {
                                $name = $exceptions[ $metaboxhidden_area ];
                            }

                            // Add a link to reset only this post type
                            $metaboxhidden_links[ $name ] = '<a href="'.esc_url( add_query_arg( 'metaboxhidden', $metaboxhidden_area, $current_url ) ).'">'.esc_html( $name ).' Only</a> — <em>currently hiding: '.esc_html(  implode( ', ', $values ) ).'</em>';
                        }
                    }
                }

                // If none found
                if ( empty( $metaboxhidden_links ) ) {
                    ?><em>None found</em><?php
                } else {

                    // Add the clear all link
                    ?>
                    <a href="<?php echo esc_url( add_query_arg( 'metaboxhidden', 'all', $current_url ) ); ?>">ALL AREAS</a><br>
                    <?php

                    // Sort them
                    ksort( $metaboxhidden_links );
                    
                    // Iter them in order
                    foreach ( $metaboxhidden_links as $mbl ) {
                        ?>
                        ⤷ <?php echo wp_kses_post( $mbl ); ?><br>
                        <?php
                    }
                } ?></td>
            </tr>
        <?php

        // Meta Box Order
        ?>
            <tr>
                <th>Reset Meta Box Positions</th>
                <td><?php
                // Store them here
                $metaboxorder_links = [];

                // Iter the user meta
                foreach ( $user_meta as $key => $value ) {

                    // Get only these meta keys
                    if ( str_starts_with( $key, 'meta-box-order_' ) ) {

                        // Strip the key to get the post type
                        $metaboxorder_area = str_replace( 'meta-box-order_', '', $key );

                        // Fetch the post type object
                        $post_type_obj = get_post_type_object( $metaboxorder_area );

                        // Allowed exceptions
                        $exceptions = [
                            'dashboard'         => 'Dashboard',
                            'dashboard-network' => 'Dashboard (Network)',
                            'nav-menus'         => 'Navigation Menus'
                        ];

                        // Check if post type still exists, and is on this site 
                        // Note: user may have additional post type options for different sites if on multisite
                        if ( !is_null( $post_type_obj ) || array_key_exists( $metaboxorder_area, $exceptions ) ) {

                            // Get the values
                            $values = unserialize( $value[0] );

                            // Skip empty values
                            if ( empty( $values ) ) {
                                continue;
                            }

                            // Get the post type name
                            if ( !is_null( $post_type_obj ) ) {
                                $name = $post_type_obj->labels->name;
                            } else {
                                $name = $exceptions[ $metaboxorder_area ];
                            }

                            // Add a link to reset only this post type
                            $metaboxorder_links[ $name ] = '<a href="'.esc_url( add_query_arg( 'meta-box-order', $metaboxorder_area, $current_url ) ).'">'.esc_html( $name ).' Only</a></em>';
                        }
                    }
                }

                // If none found
                if ( empty( $metaboxorder_links ) ) {
                    ?><em>None found</em><?php
                } else {

                    // Add the clear all link
                    ?>
                    <a href="<?php echo esc_url( add_query_arg( 'meta-box-order', 'all', $current_url ) ); ?>">ALL AREAS</a><br>
                    <?php

                    // Sort them
                    ksort( $metaboxorder_links );
                    
                    // Iter them in order
                    foreach ( $metaboxorder_links as $mbol ) {
                        ?>
                        ⤷ <?php echo wp_kses_post( $mbol ); ?><br>
                        <?php
                    }
                } ?></td>
            </tr>
        <?php

        // End the section
        ?>
        </table>
        <?php
    } // End options()


    /**
     * Reset meta keys
     *
     * @param int $user_id
     * @param string $meta_key
     * @return void
     */
    public function reset( $user_id ) {
        // Check for query strings
        if ( isset( $_GET ) && !empty( $_GET ) ) {

            // Meta keys
            $meta_keys = [
                'manageedit'        => 'manageedit-{value}columnshidden',
                'metaboxhidden'     => 'metaboxhidden_{value}',
                'meta-box-order'    => 'meta-box-order_{value}'
            ];

            // Our meta key found
            $qs_param = false;
            $qs_value = false;

            // Iter the query strings
            foreach ( $_GET as $param => $value ) {

                // Check if it's one of ours
                if ( array_key_exists( sanitize_text_field( $param ), $meta_keys ) ) {

                    // Set the key/value
                    $qs_param = sanitize_text_field( $param );
                    $qs_value = sanitize_text_field( $param );

                    // Stop at first instance
                    break;
                }
            }

            // Stop if we didn't include one of our keys
            if ( !$qs_param && !$qs_value ) {
                return;
            }

            // If only a specified area
            if ( $value != 'all' ) {

                // Get the full meta key
                $meta_key_pattern = $meta_keys[ $qs_param ];

                // Replace the value
                $meta_key = str_replace( '{value}', $qs_value, $meta_key_pattern );

                // Delete the key
                delete_user_meta( $user_id, $meta_key );

            // Otherwise we need to reset all of the user metakeys that match
            } else {

                // Get the user
                $user_meta = get_user_meta( $user_id );

                // Split meta key regex
                $meta_key_pattern = $meta_keys[ $qs_param ];
                $meta_key_parts = explode( '{value}', $meta_key_pattern );

                // Iter to find what we are looking for
                foreach ( $user_meta as $key => $v ) {

                    // Check for a meta key in the user meta
                    if ( str_starts_with( $key, $meta_key_parts[0] ) && 
                         ( isset( $meta_key_parts[1] ) && str_ends_with( $key, $meta_key_parts[1] ) ) ) {
                        
                        // Delete the key
                        delete_user_meta( $user_id, $key );
                    }
                }
            }

            // Remove the query string
            helpdocs_remove_qs_without_refresh( $qs_param );
        }
    } // End reset()
}