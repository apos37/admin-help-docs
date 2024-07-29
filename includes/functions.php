<?php
/**
 * Functions that can be used globally.
 */

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Check if a user has a role
 *
 * @param string $role
 * @param int $user_id
 * @return bool
 */
function helpdocs_has_role( $role, $user_id = null ) {
    // First verify that $role is not null
    if ( $role == false || is_null( $role ) ){
        return false;
    }

    // Get the user id
    if ( is_null( $user_id ) ) {
        $user_id = get_current_user_id();
    }

    // Get the user
    if ( $user = get_user_by( 'id', $user_id ) ) {

        // Get their roles
        $roles = $user->roles;

        // Check if role exists
        if ( is_array( $roles ) && in_array( $role, $roles ) ) {
            return true;

        } else {
            return false;
        }
    } else {
        return false;
    }
} // End helpdocs_has_role()


/**
 * Check if a user has permission to add/edit help sections
 *
 * @param int $user_id
 * @return bool
 */
function helpdocs_user_can_edit( $user_id = null ) {
    // Get the user id
    if ( is_null( $user_id ) ) {
        $user_id = get_current_user_id();
    }

    // Get the user
    if ( $user = get_userdata( $user_id ) ) {

        // If they are an admin, then return true
        if ( helpdocs_has_role( 'administrator' ) ) {
            return true;
        }

        // Get the allowed edit roles
        $edit_roles = get_option( HELPDOCS_GO_PF.'edit_roles', [] );

        // If no edit roles have been set, and they are not an admin, then return false
        if ( empty( $edit_roles ) ) {
            return false;
        }

        // Get the user's roles
        $user_roles = $user->roles;

        // Iter the allowed edit roles
        foreach ( $user_roles as $user_role ) {
            if ( array_key_exists( $user_role, $edit_roles ) ) {
                return true;
            }
        }

        // Otherwise return false
        return false;
    } else {
        return false;
    }
} // End helpdocs_user_can_edit()


/**
 * Check if a user has permission to view help sections
 *
 * @param int $user_id
 * @return bool
 */
function helpdocs_user_can_view( $user_id = null ) {
    // Get the user id
    if ( is_null( $user_id ) ) {
        $user_id = get_current_user_id();
    }

    // Get the capability
    $capability = 'manage_options';
    if ( !helpdocs_has_role( 'administrator' ) ) {
        if ( get_option( HELPDOCS_GO_PF.'user_view_cap' ) && get_option( HELPDOCS_GO_PF.'user_view_cap' ) != '' ) {
            $capability = get_option( HELPDOCS_GO_PF.'user_view_cap' );
        }
    }

    // Check it
    if ( current_user_can( $capability ) ) {
        return true;
    } else {
        return false;
    }
} // End helpdocs_user_can_view()


/**
 * Get current URL with query string
 *
 * @param boolean $params
 * @param boolean $domain
 * @return string
 */
function helpdocs_get_current_url( $params = true, $domain = true ){
    // Are we including the domain?
    if ( $domain == true ) {

        // Get the protocol
        $protocol = isset( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] !== 'off' ? 'https' : 'http';

        // Get the domain
        $domain_without_protocol = sanitize_text_field( $_SERVER[ 'HTTP_HOST' ] );

        // Domain with protocol
        $domain = $protocol.'://'.$domain_without_protocol;

    } elseif ( $domain == 'only' ) {

        // Get the domain
        $domain = sanitize_text_field( $_SERVER[ 'HTTP_HOST' ] );
        return $domain;

    } else {
        $domain = '';
    }

    // Get the URI
    $uri = filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL );

    // Put it together
    $full_url = $domain.$uri;

    // Are we including query string params?
    if ( !$params ) {
        return strtok( $full_url, '?' );
        
    } else {
        return $full_url;
    }
} // End helpdocs_get_current_url()


/**
 * Get current admin URL with query string
 *
 * @return string
 */
function helpdocs_get_current_admin_url( $params = true ) {
    $uri = filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL );
    if ( !$params ) {
        $uri = strtok( $uri, '?' );
    }
    return admin_url( basename( $uri ) );
} // End helpdocs_get_current_admin_url()


/**
 * Check if two urls match while ignoring order of params
 * Also allow ignoring addtional params that $url1 has that $url2 does not
 *
 * @param string $url1
 * @param string $url2
 * @return bool
 */
function helpdocs_do_urls_match( $url1, $url2, $ignore_addt_params = true ) {
    // Parse urls

    $parts1 = wp_parse_url( $url1 );
    $parts2 = wp_parse_url( $url2 );
    
    // Scheme and host are case-insensitive.
    $scheme1 = strtolower( $parts1[ 'scheme' ] ?? '' );
    $scheme2 = strtolower( $parts2[ 'scheme' ] ?? '' );
    $host1 = strtolower( $parts1[ 'host' ] ?? '' );
    $host2 = strtolower( $parts2[ 'host' ] ?? '' );
    
    // URL scheme mismatch (http <-> https): URLs are not identical.
    if ( $scheme1 !== $scheme2 ) {
        return false;
    }
    
    // Different host (domain name): Not identical.
    if ( $host1 !== $host2 ) {
        return false;
    }
    
    // Remove leading/trailing slashes, url-decode special characters.
    $path1 = trim( urldecode( $parts1[ 'path' ] ?? '' ), '/' );
    $path2 = trim( urldecode( $parts2[ 'path' ] ?? '' ), '/' );

    // The request-path is different: Different URLs.
    if ( $path1 !== $path2 ) {
        return false;
    }

    // Convert the query-params into arrays.
    parse_str( $parts1['query'] ?? '', $query1 );
    parse_str( $parts2['query'] ?? '', $query2 );

    // Both URLs have a different number of params: They cannot match.
    if ( !$ignore_addt_params && count( $query1 ) !== count( $query2 ) ) {
        return false;
    }

    // Only compare the query-arrays when params are present.
    if ( count( $query1 ) > 0 ) {
        ksort( $query1 );
        ksort( $query2 );

        // We are not ignoring additional params, and query arrays have differencs: URLs do not match.
        if ( !$ignore_addt_params && array_diff( $query1, $query2 ) ) {
            return false;

        // We are ignoring additional params, but url2 has params that url1 does not
        } elseif ( $ignore_addt_params && !empty( array_diff( $query2, $query1 ) ) ) {
            return false;
        }
    }

    // All checks passed, URLs are identical.
    return true;
} // End helpdocs_do_urls_match()


/**
 * Base64 Encoding Functions
 */
function helpdocs_base64url_encode( $data ) {
    return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
} // End helpdocs_base64url_encode()
function helpdocs_base64url_decode( $data ) {
    return base64_decode( str_pad( strtr( $data, '-_', '+/' ), strlen( $data ) % 4, '=', STR_PAD_RIGHT ) );
} // End helpdocs_base64url_decode()


/**
 * Remove query strings from url without refresh
 */
function helpdocs_remove_qs_without_refresh( $qs = null, $is_admin = true ) {
    // Get the current title
    $page_title = get_the_title();

    // Get the current url without the query string
    if ( !is_null( $qs ) ) {

        // Check if $qs is an array
        if ( !is_array( $qs ) ) {
            $qs = [ $qs ];
        }
        $new_url = remove_query_arg( $qs, helpdocs_get_current_url() );

    } else {
        $new_url = helpdocs_get_current_url( false );
    }

    // Write the script
    $args = [ 
        'title' => $page_title,
        'url' => $new_url
    ];

    // Admin or not
    if ( $is_admin ) {
        $hook = 'admin_footer';
    } else {
        $hook = 'wp_footer';
    }

    // Add the script to the admin footer
    add_action( $hook, function() use ( $args ) {
        echo '<script id="helpdocs_remove_qs_without_refresh">
        if ( history.pushState ) { 
            var url = window.location.href; 
            var obj = { Title: "'.esc_html( $args[ 'title' ] ).'", Url: "'.esc_url_raw( $args[ 'url' ] ).'"}; 
            window.history.pushState( obj, obj.Title, obj.Url ); 
        }
        </script>';
    } );

    // Return
    return;
} // End helpdocs_remove_qs_without_refresh()


/**
 * Add a query string from url without refresh
 */
function helpdocs_add_qs_without_refresh( $qs, $value, $is_admin = true ) {
    // Get the current title
    $page_title = get_the_title();

    // Get the current url with the qs added
    $new_url = add_query_arg( $qs, $value, helpdocs_get_current_url() );

    // Write the script
    $args = [ 
        'title' => $page_title,
        'url' => $new_url
    ];

    // Admin or not
    if ( $is_admin ) {
        $hook = 'admin_footer';
    } else {
        $hook = 'wp_footer';
    }

    // Add the script to the admin footer
    add_action( $hook, function() use ( $args ) {
        echo '<script id="helpdocs_remove_qs_without_refresh">
        if ( history.pushState ) { 
            var url = window.location.href; 
            var obj = { Title: "'.esc_html( $args[ 'title' ] ).'", Url: "'.esc_url_raw( $args[ 'url' ] ).'"}; 
            window.history.pushState( obj, obj.Title, obj.Url ); 
        }
        </script>';
    } );

    // Return
    return;
} // End helpdocs_add_qs_without_refresh()


/**
 * Convert timezone
 * 
 * @param string $date
 * @param string $format
 * @param string $timezone
 * @return string
 */
function helpdocs_convert_timezone( $date = null, $format = 'F j, Y g:i A T', $timezone = null ) {
    // Get today as default
    if ( is_null( $date ) ) {
        $date = gmdate( 'Y-m-d H:i:s' );
    }

    // Get the date in UTC time
    $date = new DateTime( $date, new DateTimeZone( 'UTC' ) );
    

    // Get the timezone string
    if ( !is_null( $timezone ) ) {
        $timezone_string = $timezone;
    } else {
        $timezone_string = wp_timezone_string();
    }

    // Set the timezone to the new one
    $date->setTimezone( new DateTimeZone( $timezone_string ) );

    // Format it the way we way
    $new_date = $date->format( $format );

    // Return it
    return $new_date;
} // End helpdocs_convert_timezone()


/**
 * Simplified/sanitized version of $_GET
 *
 * @param string $qs_param
 * @param string $comparison
 * @param string $equal_to
 * @return string|false
 */
function helpdocs_get( $qs_param, $comparison = '!=', $equal_to = '', $nonce_action = null, $nonce_value = null ) {
    // Check nonce if action and value are provided
    if ( $nonce_action && $nonce_value ) {
        if ( !isset( $_GET[ $nonce_value ] ) || !wp_verify_nonce( $_GET[ $nonce_value ], $nonce_action ) ) {
            error_log( 'Nonce verification failed' );
            return false;
        }
    }
    
    // Get if the query string exists at all
    if ( isset( $_GET[ $qs_param ] ) ) {
        $fitered_get = filter_input_array( INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $get = $fitered_get[ $qs_param ];

        // How are we comparing?
        if ( $comparison == '!=' ) {
            return ( $get != $equal_to ) ? $get : false;
        } elseif ( $comparison == '==' ) {
            return ( $get == $equal_to ) ? $get : false;
        } else {
            return false;
        }
    } else {
        return false;
    }
} // End helpdocs_get()


/**
 * Click to Copy
 *
 * @param string $unique_link_id
 * @param string $link_text
 * @param string $unique_copy_id
 * @param string $copy_text
 * @param boolean $include_copied_span
 * @return string
 */
function helpdocs_click_to_copy( $unique_link_id, $link_text, $unique_copy_id = null, $copy_text = null, $include_copied_span = false ) {
    // First check if we are copying text
    if ( $copy_text != null ) {
        $content = "let content = '".$copy_text."';";

    // If not, let's see if we're copying another div
    } elseif ( $unique_copy_id != null ) {
        $content = 'var e = document.getElementById("'.$unique_copy_id.'");
        let content = e;
        if (e instanceof HTMLElement) {
        	content = e.innerHTML;
        }';

    // Finally we will just copy the current text
    } else {
        $content = 'var e = document.getElementById("'.$unique_link_id.'");
        let content = e;
        if (e instanceof HTMLElement) {
        	content = e.innerHTML;
        }';
    }
    
    // Are we including the "- Copied" span?
    if ( $include_copied_span ) {
        $incl_copied_span = ' <span id="copied_'.$unique_link_id.'" class="click-to-copy"><strong>- Copied!</strong></span>';
    } else {
        $incl_copied_span = '';
    }

    // Add additional class if unique id starts with string_
    if ( preg_match( '/[a-zA-Z0-9]+\_/', $unique_link_id, $matches ) ) {
        $add_class = ' '.str_replace( '_', '', $matches[0] );
    } else {
        $add_class = '';
    }

    // Create the link
    $results = '<a class="click-to-copy-link'.$add_class.'" href="#" id="'.$unique_link_id.'" style="cursor: pointer;">'.$link_text.'</a>'.$incl_copied_span;

    // The script
    $results .= '<script>
    document.getElementById("'.$unique_link_id.'").onclick = function(e) {
        e.preventDefault();
        var tempItem = document.createElement( "input" );
        tempItem.setAttribute( "type", "text" );
        tempItem.setAttribute( "display", "none" );
        '.$content.'
        tempItem.setAttribute( "value", htmlEntities( content ) );
        document.body.appendChild( tempItem );
        tempItem.select();
        document.execCommand( "Copy" );
        tempItem.parentElement.removeChild( tempItem );
        var c = document.getElementById( "copied_'.$unique_link_id.'" );
        c.style.display="inline-block";
        setTimeout( function () {
            c.style.display="none"
        }, 3000 );
        console.log( "Copied: " + htmlEntities( content ) );
    }
    function htmlEntities( str ) {
        return String( str ).replace( "<!--?php", "<?php" ).replace( "?-->", "?>" ).replaceAll( "&amp;", "&" );
    }
    </script>';
    
    return $results;
} // End helpdocs_click_to_copy()


/**
 * Get contrast color (black or white) from hex color
 */
function helpdocs_get_contrast_color( $hexColor ) {
    // hexColor RGB
    $R1 = hexdec(substr($hexColor, 1, 2));
    $G1 = hexdec(substr($hexColor, 3, 2));
    $B1 = hexdec(substr($hexColor, 5, 2));

    // Black RGB
    $blackColor = "#000000";
    $R2BlackColor = hexdec(substr($blackColor, 1, 2));
    $G2BlackColor = hexdec(substr($blackColor, 3, 2));
    $B2BlackColor = hexdec(substr($blackColor, 5, 2));

     // Calc contrast ratio
     $L1 = 0.2126 * pow($R1 / 255, 2.2) +
           0.7152 * pow($G1 / 255, 2.2) +
           0.0722 * pow($B1 / 255, 2.2);

    $L2 = 0.2126 * pow($R2BlackColor / 255, 2.2) +
          0.7152 * pow($G2BlackColor / 255, 2.2) +
          0.0722 * pow($B2BlackColor / 255, 2.2);

    $contrastRatio = 0;
    if ($L1 > $L2) {
        $contrastRatio = (int)(($L1 + 0.05) / ($L2 + 0.05));
    } else {
        $contrastRatio = (int)(($L2 + 0.05) / ($L1 + 0.05));
    }

    // If contrast is more than 5, return black color
    if ($contrastRatio > 5) {
        return '#000000';
    } else { 
        // if not, return white color.
        return '#FFFFFF';
    }
} // End helpdocs_get_contrast_color()


/**
 * Add string comparison functions to earlier versions of PHP
 *
 * @param string $haystack
 * @param string $needle
 * @return bool
 */
if ( version_compare( PHP_VERSION, 8.0, '<=' ) && !function_exists( 'str_starts_with' ) ) {
    function str_starts_with ( $haystack, $needle ) {
        return strpos( $haystack , $needle ) === 0;
    }
}
if ( version_compare( PHP_VERSION, 8.0, '<=' ) && !function_exists('str_ends_with') ) {
    function str_ends_with($haystack, $needle) {
        return $needle !== '' && substr($haystack, -strlen($needle)) === (string)$needle;
    }
}
if ( version_compare( PHP_VERSION, 8.0, '<=' ) && !function_exists('str_contains') ) {
    function str_contains($haystack, $needle) {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}


/**
 * Get just the domain without the https://
 * Option to capitalize the first part
 *
 * @param boolean $capitalize
 * @return string
 */
function helpdocs_get_domain( $capitalize = false, $remove_ext = false ) {
    // Get the domain
    $domain = sanitize_text_field( $_SERVER[ 'SERVER_NAME' ] );

    // Are we capitalizing
    if ( $capitalize || $remove_ext ) {

        // Get the position of the ext
        $pos = strrpos( $domain, '.' );

        // Make the first part uppercase
        if ( $capitalize ) {
            $prefix = strtoupper( substr( $domain, 0, $pos ) );
        } else {
            $prefix = substr( $domain, 0, $pos );
        }
        
        // Get the extension
        $suffix = substr( $domain, $pos + 1 );

        // Put it back together
        if ( !$remove_ext ) {
            $domain = $prefix.'.'.$suffix;
        } else {
            $domain = $prefix;
        }
    }

    // Return it
    return $domain;
} // End helpdocs_get_domain()


/**
 * Check if the admin page is using gutenberg editor
 *
 * @return boolean
 */
function is_gutenberg() {
    // If is_gutenberg page exists
    if ( function_exists( 'is_gutenberg_page' ) && is_gutenberg_page() ) {
        return true;
    }
    
    // Otherwise get the current screen
    $current_screen = get_current_screen();
    if ( method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) {
        return true;
    }
    return false;
} // End is_gutenberg()


/**
 * Convert time to elapsed string
 *
 * @param [type] $datetime
 * @param boolean $full
 * @return string
 */
function helpdocs_time_elapsed_string( $datetime, $full = false ) {
    $now = new DateTime;
    $ago = new DateTime( $datetime );
    $diff = $now->diff( $ago );

    $diff->w = floor( $diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ( $string as $k => &$v ) {
        if ( $diff->$k ) {
            $v = $diff->$k . ' ' . $v . ( $diff->$k > 1 ? 's' : '' );
        } else {
            unset( $string[$k] );
        }
    }

    if ( !$full ) $string = array_slice( $string, 0, 1 );
    return $string ? implode( ', ', $string ) . ' ago' : 'just now';
} // End helpdocs_time_elapsed_string()


/**
 * THE END
 */