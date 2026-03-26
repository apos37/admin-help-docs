<?php
/**
 * Functions that can be used globally.
 */

if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Check if a user has a role
 * 
 * @deprecated Only used for access control, which now uses Helpers::user_can_edit() and Helpers::user_can_view()
 * @return void
 */
function helpdocs_has_role() {
    _deprecated_function( __FUNCTION__, '2.0', 'Use Helpers::user_can_edit() or Helpers::user_can_view() instead.' );
    return false;
} // End helpdocs_has_role()


/**
 * Check if a user has permission to add/edit help sections
 * 
 * @deprecated Use \PluginRx\AdminHelpDocs\Helpers::user_can_edit() instead.
 * @return bool
 */
function helpdocs_user_can_edit( $user_id = null ) {
    _deprecated_function( __FUNCTION__, '2.0', 'Use \PluginRx\AdminHelpDocs\Helpers::user_can_edit() instead.' );
    return \PluginRx\AdminHelpDocs\Helpers::user_can_edit( $user_id );
} // End helpdocs_user_can_edit()


/**
 * Check if a user has permission to view help sections
 *
 * @deprecated Use \PluginRx\AdminHelpDocs\Helpers::user_can_view() instead.
 * @return bool
 */
function helpdocs_user_can_view( $user_id = null ) {
    _deprecated_function( __FUNCTION__, '2.0', 'Use \PluginRx\AdminHelpDocs\Helpers::user_can_view() instead.' );
    return \PluginRx\AdminHelpDocs\Helpers::user_can_view( null, $user_id );
} // End helpdocs_user_can_view()


/**
 * Get current URL with query string
 *
 * @deprecated Use \PluginRx\AdminHelpDocs\Helpers::get_current_url() instead.
 * @return string
 */
function helpdocs_get_current_url( $params = true, $domain = true ) {
    _deprecated_function( __FUNCTION__, '2.0', 'Use \PluginRx\AdminHelpDocs\Helpers::get_current_url() instead.' );
    return \PluginRx\AdminHelpDocs\Helpers::get_current_url( $params, $domain );
} // End helpdocs_get_current_url()


/**
 * Get current admin URL with query string
 *
 * @deprecated Use \PluginRx\AdminHelpDocs\Helpers::get_current_admin_url() instead.
 * @param bool $params Whether to include query parameters in the returned URL
 * @return string
 */
function helpdocs_get_current_admin_url( $params = true ) {
    _deprecated_function( __FUNCTION__, '2.0', 'Use \PluginRx\AdminHelpDocs\Helpers::get_current_admin_url() instead.' );
    return \PluginRx\AdminHelpDocs\Helpers::get_current_admin_url( $params );
} // End helpdocs_get_current_admin_url()


/**
 * Check if two urls match while ignoring order of params
 * Also allow ignoring addtional params that $url1 has that $url2 does not
 *
 * @deprecated Use \PluginRx\AdminHelpDocs\Helpers::do_urls_match() instead.
 * @param string $url1
 * @param string $url2
 * @param bool $ignore_addt_params
 * @return bool
 */
function helpdocs_do_urls_match( $url1, $url2, $ignore_addt_params = true ) {
    _deprecated_function( __FUNCTION__, '2.0', 'Use \PluginRx\AdminHelpDocs\Helpers::do_urls_match() instead.' );
    return \PluginRx\AdminHelpDocs\Helpers::do_urls_match( $url1, $url2, $ignore_addt_params );
} // End helpdocs_do_urls_match()


/**
 * Base64 Encoding
 * 
 * @deprecated Use \PluginRx\AdminHelpDocs\Helpers::base64url_encode() instead.
 * @param $data
 * @return string
 */
function helpdocs_base64url_encode( $data ) {
    _deprecated_function( __FUNCTION__, '2.0', 'Use \PluginRx\AdminHelpDocs\Helpers::base64url_encode() instead.' );
    return \PluginRx\AdminHelpDocs\Helpers::base64url_encode( $data );
} // End helpdocs_base64url_encode()


/**
 * Base64 Decoding
 * 
 * @deprecated Use \PluginRx\AdminHelpDocs\Helpers::base64url_decode() instead.
 * @param $data
 * @return string
 */
function helpdocs_base64url_decode( $data ) {
    _deprecated_function( __FUNCTION__, '2.0', 'Use \PluginRx\AdminHelpDocs\Helpers::base64url_decode() instead.' );
    return \PluginRx\AdminHelpDocs\Helpers::base64url_decode( $data );
} // End helpdocs_base64url_decode()


/**
 * Remove query strings from url without refresh
 * 
 * @deprecated Use \PluginRx\AdminHelpDocs\Helpers::remove_qs_without_refresh() instead.
 * @param string|array $qs Query string(s) to remove
 * @param bool $is_admin Whether the URL is an admin URL (defaults to true)
 * @return void
 */
function helpdocs_remove_qs_without_refresh( $qs = null, $is_admin = true ) {
    _deprecated_function( __FUNCTION__, '2.0', 'Use \PluginRx\AdminHelpDocs\Helpers::remove_qs_without_refresh() instead.' );
    return \PluginRx\AdminHelpDocs\Helpers::remove_qs_without_refresh( $qs, $is_admin );
} // End helpdocs_remove_qs_without_refresh()


/**
 * Add a query string from url without refresh
 * 
 * @deprecated Use \PluginRx\AdminHelpDocs\Helpers::add_qs_without_refresh() instead.
 * @param string|array $qs Query string(s) to add
 * @param string|array $value Value(s) for the query string(s)
 * @return void
 */
function helpdocs_add_qs_without_refresh( $qs, $value ) {
    _deprecated_function( __FUNCTION__, '2.0', 'Use \PluginRx\AdminHelpDocs\Helpers::add_qs_without_refresh() instead.' );
    return \PluginRx\AdminHelpDocs\Helpers::add_qs_without_refresh( $qs, $value );
} // End helpdocs_add_qs_without_refresh()


/**
 * Convert timezone
 * 
 * @deprecated Use \PluginRx\AdminHelpDocs\Helpers::convert_timezone() instead.
 * @return string
 */
function helpdocs_convert_timezone( $date = null, $format = 'F j, Y g:i A T', $timezone = null ) {
    _deprecated_function( __FUNCTION__, '2.0', 'Use \PluginRx\AdminHelpDocs\Helpers::convert_timezone() instead.' );
    return \PluginRx\AdminHelpDocs\Helpers::convert_timezone( $date, $format, $timezone );
} // End helpdocs_convert_timezone()


/**
 * Simplified/sanitized version of $_GET
 *
 * @deprecated Use $_GET[] directly instead.
 * @return false
 */
function helpdocs_get() {
    _deprecated_function( __FUNCTION__, '2.0', 'Use $_GET[] instead.' );
    return false;
} // End helpdocs_get()


/**
 * Click to Copy
 *
 * @deprecated
 * @return string
 */
function helpdocs_click_to_copy() {
    _deprecated_function( __FUNCTION__, '2.0', 'This function is deprecated and no longer available.' );
    return '';
} // End helpdocs_click_to_copy()


/**
 * Get contrast color (black or white) from hex color
 * 
 * @deprecated Use \PluginRx\AdminHelpDocs\Helpers::get_contrast_color() instead.
 * @param string $hexColor
 * @return string
 */
function helpdocs_get_contrast_color( $hexColor ) {
    _deprecated_function( __FUNCTION__, '2.0', 'Use \PluginRx\AdminHelpDocs\Helpers::get_contrast_color() instead.' );
    return \PluginRx\AdminHelpDocs\Helpers::get_contrast_color( $hexColor );
} // End helpdocs_get_contrast_color()


/**
 * Get just the domain without the https://
 * Option to capitalize the first part
 *
 * @deprecated Use \PluginRx\AdminHelpDocs\Helpers::get_domain() instead.
 * @param bool $capitalize Whether to capitalize the domain name
 * @param bool $remove_ext Whether to remove the domain extension (e.g. .com)
 * @return string
 */
function helpdocs_get_domain( $capitalize = false, $remove_ext = false ) {
    _deprecated_function( __FUNCTION__, '2.0', 'Use \PluginRx\AdminHelpDocs\Helpers::get_domain() instead.' );
    return \PluginRx\AdminHelpDocs\Helpers::get_domain( $capitalize, $remove_ext );
} // End helpdocs_get_domain()


/**
 * Check if the admin page is using gutenberg editor
 *
 * @deprecated Use \PluginRx\AdminHelpDocs\Helpers::is_gutenberg() instead.
 * @return boolean
 */
function is_gutenberg() {
    _deprecated_function( __FUNCTION__, '2.0', 'Use \PluginRx\AdminHelpDocs\Helpers::is_gutenberg() instead.' );
    return \PluginRx\AdminHelpDocs\Helpers::is_gutenberg();
} // End is_gutenberg()


/**
 * Convert time to elapsed string
 *
 * @deprecated Use \PluginRx\AdminHelpDocs\Helpers::time_elapsed_string() instead.
 * @param string $datetime
 * @return string
 */
function helpdocs_time_elapsed_string( $datetime ) {
    _deprecated_function( __FUNCTION__, '2.0', 'Use \PluginRx\AdminHelpDocs\Helpers::time_elapsed_string() instead.' );
    return \PluginRx\AdminHelpDocs\Helpers::time_elapsed_string( $datetime );
} // End helpdocs_time_elapsed_string()