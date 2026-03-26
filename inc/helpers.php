<?php

namespace PluginRx\AdminHelpDocs;

if ( ! defined( 'ABSPATH' ) ) exit;

class Helpers {

    /**
     * Safe print_r with <pre> tags.
     * Displays output only for developer or specified user IDs.
     *
     * @param mixed $var                         Data to print.
     * @param string|int|bool|null $left_margin  Left margin (px, string value, or true for 200px).
     * @param int|array|null $user_id            Single user ID or array of IDs allowed to see the output.
     * @param bool $write_bool                   Convert boolean to "TRUE"/"FALSE".
     *
     * @return void
     */
    public static function print_r( $var, $left_margin = null, $user_id = null, $write_bool = true ) {
        $current_user_id = get_current_user_id();

        // Permission check
        if ( $user_id !== null ) {
            if ( is_array( $user_id ) ) {
                if ( ! in_array( $current_user_id, array_map( 'intval', $user_id ), true ) ) {
                    return;
                }
            } elseif ( intval( $user_id ) !== $current_user_id ) {
                return;
            }
        } elseif ( $current_user_id !== 1 ) {
            return;
        }

        // Margin calculation
        if ( is_numeric( $left_margin ) ) {
            $margin = intval( $left_margin ) . 'px';
        } elseif ( is_string( $left_margin ) ) {
            $margin = sanitize_text_field( $left_margin );
        } elseif ( $left_margin === true ) {
            $margin = '180px';
        } else {
            $margin = '0';
        }

        // Boolean conversion
        if ( $write_bool && is_bool( $var ) ) {
            $var = $var ? 'TRUE' : 'FALSE';
        }

        // Output
        echo '<pre class="ddtt_print_r" style="margin-left:' . esc_attr( $margin ) . ';overflow-x:unset;">';
        wp_kses_post( print_r( $var ) ); // phpcs:ignore
        echo '</pre>';
    } // End print_r()


    /**
     * Get the admin role that should have access to help docs settings
     *
     * @return string Role name
     */
    public static function admin_role() : string {
        // Allow filtering the admin role, in case a site uses a custom role for admins
        return apply_filters( 'helpdocs_admin_role', 'administrator' );
    } // End admin_role()


    /**
     * Check if the current user can manage help docs
     *
     * @return bool 
     */
    public static function user_can_edit( $user_id = null ) : bool {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        if ( ! $user_id ) {
            return false;
        }

        $test_mode = Bootstrap::is_test_mode();
        $cache_key = '';

        if ( ! $test_mode ) {
            $cache_key = 'helpdocs_perm_edit_' . absint( $user_id );
            $cached    = get_transient( $cache_key );
            if ( false !== $cached ) {
                return (bool) $cached;
            }
        }

        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return false;
        }

        $user_roles = (array) $user->roles;
        $can_edit   = false;

        // Admins should always have access
        $admin_role = self::admin_role();
        if ( in_array( $admin_role, $user_roles, true ) ) {
            $can_edit = true;
        } else {
            // Check if any of the user's roles are in the edit roles option
            $edit_roles = (array) get_option( 'helpdocs_edit_roles', [] );
            
            if ( ! empty( $edit_roles ) ) {
                // Using array_intersect for a cleaner check than a foreach loop
                if ( array_intersect( $user_roles, array_keys( $edit_roles ) ) ) {
                    $can_edit = true;
                }
            }
        }

        if ( ! $test_mode && ! empty( $cache_key ) ) {
            set_transient( $cache_key, ( $can_edit ? 1 : 0 ), 12 * HOUR_IN_SECONDS );
        }

        return $can_edit;
    } // End user_can_edit()


    /**
     * Check if the current user can view help docs
     *
     * @param int|null $doc_id Optional doc ID to check per-doc access
     * @param int|null $user_id Optional user ID (defaults to current user)
     * @return bool 
     */
    public static function user_can_view( $doc_id = null, $user_id = null ) : bool {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }
        
        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return false;
        }

        $user_roles = (array) $user->roles;

        // Admin shortcut
        if ( in_array( self::admin_role(), $user_roles, true ) ) {
            return true;
        }

        // 1. If checking a specific doc, keep the direct meta check for performance
        if ( $doc_id ) {
            $doc_roles = maybe_unserialize( get_post_meta( $doc_id, 'helpdocs_view_roles', true ) );
            
            if ( is_array( $doc_roles ) && ! empty( $doc_roles ) ) {
                return (bool) array_intersect( $user_roles, $doc_roles );
            }

            // Fallback to global plugin settings if no specific doc roles
            $access_type = get_option( 'helpdocs_user_view_type', 'capability' );
            if ( $access_type === 'capability' ) {
                $cap = get_option( 'helpdocs_user_view_cap', 'manage_options' );
                return user_can( $user_id, $cap );
            } else {
                $roles = (array) get_option( 'helpdocs_view_roles', [] );
                return (bool) array_intersect( $user_roles, $roles );
            }
        }

        // 2. If no doc_id, check if the user has permission to see ANY docs in the 'main' location
        // We use get_docs() here to leverage the existing permission/cache logic you just built
        $docs = self::get_docs( [ 'site_location' => 'main' ] );
        
        return ! empty( $docs );
    } // End user_can_view()


    /**
     * Get an array of available user roles for use in settings options
     *
     * @return array Array of role options with 'label' and 'value' keys
     */
    public static function get_role_options() : array {
        $roles = get_editable_roles();
        $role_options = [];
        foreach ( $roles as $key => $role ) {
            if ( $key != 'administrator' ) {
                $role_options[] = [
                    'label' => $role[ 'name' ],
                    'value' => $key
                ];
            }
        }
        return $role_options;
    } // End get_roles()


    /**
     * Get all admin user emails, which can be used for notifications or other purposes
     *
     * @return array Array of unique admin email addresses
     */
    public static function get_all_admin_emails() : array {
        $users = get_users( [
            'fields' => [ 'user_email' ],
            'role__in' => [ self::admin_role() ],
        ] );

        $emails = [];
        foreach ( $users as $user ) {
            if ( is_email( $user->user_email ) ) {
                $emails[] = $user->user_email;
            }
        }

        return array_unique( $emails );
    } // End get_all_admin_emails()


    /**
     * Check if we're on any of the plugin's screens
     *
     * @param string|null $textdomain
     * @return bool
     */
    public static function is_our_screen( $textdomain = null ) : bool {
        $current_screen = get_current_screen();
        if ( ! $current_screen ) {
            return false;
        }

        $text_domain = $textdomain ?? Bootstrap::textdomain();
        $is_helpdocs_screen = $current_screen->id === 'toplevel_page_' . $text_domain || $current_screen->id === 'toplevel_page_' . $text_domain . '-network';
        $is_post_type_list_screen = $current_screen->id === 'edit-' . HelpDocs::$post_type || $current_screen->id === 'edit-' . Imports::$post_type;
        $is_taxonomy_list_screen = $current_screen->id === 'edit-' . Folders::$taxonomy;
        $is_our_dashboard = $current_screen->id === 'dashboard_page_admin-help-dashboard';

        return $is_helpdocs_screen || $is_post_type_list_screen || $is_taxonomy_list_screen || $is_our_dashboard;
    } // End is_our_screen()


    /**
     * Check if plugin is in test mode
     * 
     * @param bool $params Whether to include query parameters in the returned URL
     * @param bool|string $domain Whether to include the domain in the returned URL. If 'only', returns only the domain without protocol.     *
     * @return string
     */
    public static function get_current_url( $params = true, $domain = true ) : string {
        $host = '';
        if ( isset( $_SERVER[ 'HTTP_HOST' ] ) ) {
            $host = sanitize_text_field( wp_unslash( $_SERVER[ 'HTTP_HOST' ] ) );
        }

        if ( $domain === true ) {

            $protocol = ( isset( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] !== 'off' ) ? 'https' : 'http';
            $domain = $host ? $protocol . '://' . $host : '';

        } elseif ( $domain === 'only' ) {

            return $host;

        } else {

            $domain = '';
        }

        $uri = '';
        if ( isset( $_SERVER[ 'REQUEST_URI' ] ) ) {
            $uri = esc_url_raw( wp_unslash( $_SERVER[ 'REQUEST_URI' ] ) );
        }

        $full_url = $domain . $uri;

        if ( ! $params ) {
            return strtok( $full_url, '?' );
        }

        return $full_url;
    } // End get_current_url()


    /**
     * Get the current admin page URL, with options to include query parameters
     *
     * @param bool $params Whether to include query parameters in the returned URL
     * @return string
     */
    public static function get_current_admin_url( $params = true ) : string {
        $uri = filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL );
        if ( ! $params ) {
            $uri = strtok( $uri, '?' );
        }
        return Bootstrap::admin_url( basename( $uri ) );
    } // End get_current_admin_url()


    /**
     * Check if two urls match while ignoring order of params
     * Also allow ignoring addtional params that $url1 has that $url2 does not
     * 
     * @param string $url1
     * @param string $url2
     * @param bool
     * @return bool
     */
    public static function do_urls_match( $url1, $url2, $ignore_addt_params = true ) : bool {
        $parts1 = wp_parse_url( $url1 );
        $parts2 = wp_parse_url( $url2 );
        
        $scheme1 = strtolower( $parts1[ 'scheme' ] ?? '' );
        $scheme2 = strtolower( $parts2[ 'scheme' ] ?? '' );
        if ( $scheme1 !== $scheme2 ) {
            return false;
        }

        $host1 = strtolower( $parts1[ 'host' ] ?? '' );
        $host2 = strtolower( $parts2[ 'host' ] ?? '' );
        if ( $host1 !== $host2 ) {
            return false;
        }
        
        $path1 = trim( urldecode( $parts1[ 'path' ] ?? '' ), '/' );
        $path2 = trim( urldecode( $parts2[ 'path' ] ?? '' ), '/' );
        if ( $path1 !== $path2 ) {
            return false;
        }

        parse_str( $parts1[ 'query' ] ?? '', $query1 );
        parse_str( $parts2[ 'query' ] ?? '', $query2 );
        if ( ! $ignore_addt_params && count( $query1 ) !== count( $query2 ) ) {
            return false;
        }

        if ( count( $query1 ) > 0 ) {
            ksort( $query1 );
            ksort( $query2 );

            if ( ! $ignore_addt_params && array_diff( $query1, $query2 ) ) {
                return false;

            } elseif ( $ignore_addt_params && ! empty( array_diff( $query2, $query1 ) ) ) {
                return false;
            }
        }

        return true;
    } // End do_urls_match()


    /**
     * Convert a UTC date string to the site's local timezone and format it
     *
     * @param string|null $date Date string in UTC (defaults to current time)
     * @param string $format Date format (default: 'F j, Y g:i A T')
     * @param string|null $timezone Timezone identifier (defaults to site's timezone)
     * @return string Formatted date string in local timezone
     */
    public static function convert_timezone( $date = null, $format = 'F j, Y g:i A T', $timezone = null ) : string {
        $date_string = $date ?? gmdate( 'Y-m-d H:i:s' );

        $timezone_string = $timezone ?: wp_timezone_string();

        $datetime = new \DateTime( $date_string, new \DateTimeZone( 'UTC' ) );
        $datetime->setTimezone( new \DateTimeZone( $timezone_string ) );

        return $datetime->format( $format );
    } // End convert_timezone()


    /**
     * Get the appropriate contrast color (black or white) for a given hex color
     *
     * @param string $hex_color Hex color code (e.g. '#ff0000' or 'ff0000')
     * @return string Contrast color ('#000000' or '#FFFFFF')
     */
    public static function get_contrast_color( $hex_color ) : string {
        $hex = ltrim( $hex_color, '#' );

        if ( strlen( $hex ) === 3 ) {
            $hex = $hex[ 0 ] . $hex[ 0 ] . $hex[ 1 ] . $hex[ 1 ] . $hex[ 2 ] . $hex[ 2 ];
        }

        if ( strlen( $hex ) !== 6 ) {
            return '#000000';
        }

        $r = hexdec( substr( $hex, 0, 2 ) ) / 255;
        $g = hexdec( substr( $hex, 2, 2 ) ) / 255;
        $b = hexdec( substr( $hex, 4, 2 ) ) / 255;

        $r = ( $r <= 0.03928 ) ? $r / 12.92 : pow( ( $r + 0.055 ) / 1.055, 2.4 );
        $g = ( $g <= 0.03928 ) ? $g / 12.92 : pow( ( $g + 0.055 ) / 1.055, 2.4 );
        $b = ( $b <= 0.03928 ) ? $b / 12.92 : pow( ( $b + 0.055 ) / 1.055, 2.4 );

        $luminance = ( 0.2126 * $r ) + ( 0.7152 * $g ) + ( 0.0722 * $b );

        $contrast_with_black = ( $luminance + 0.05 ) / 0.05;
        $contrast_with_white = 1.05 / ( $luminance + 0.05 );

        return ( $contrast_with_black >= $contrast_with_white ) ? '#000000' : '#FFFFFF';
    } // End get_contrast_color()


    /**
     * Get the site's domain name, with options to capitalize and/or remove the extension
     *
     * @param bool $capitalize Whether to capitalize the domain name
     * @param bool $remove_ext Whether to remove the domain extension (e.g. .com)
     * @return string The processed domain name
     */
    public static function get_domain( $capitalize = false, $remove_ext = false ) : string {
        $host = wp_parse_url( home_url(), PHP_URL_HOST );
        if ( ! $host ) {
            return '';
        }

        $host = strtolower( $host );

        if ( ! $capitalize && ! $remove_ext ) {
            return $host;
        }

        $pos = strrpos( $host, '.' );
        if ( $pos === false ) {
            return $capitalize ? strtoupper( $host ) : $host;
        }

        $prefix = substr( $host, 0, $pos );
        $suffix = substr( $host, $pos + 1 );

        if ( $capitalize ) {
            $prefix = strtoupper( $prefix );
        }

        return $remove_ext ? $prefix : $prefix . '.' . $suffix;
    } // End get_domain()


    /**
     * Check if we're currently on a Gutenberg page
     *
     * @return bool
     */
    public static function is_gutenberg() : bool {
        if ( function_exists( 'is_gutenberg_page' ) && is_gutenberg_page() ) {
            return true;
        }

        if ( ! function_exists( 'get_current_screen' ) ) {
            return false;
        }

        $screen = get_current_screen();
        if ( ! $screen ) {
            return false;
        }

        return method_exists( $screen, 'is_block_editor' ) && $screen->is_block_editor();
    } // End is_gutenberg()


    /**
     * Get a human-readable time difference string (e.g. "2 hours ago") from a given date
     *
     * @param string $datetime Date string in a format recognized by strtotime()
     * @return string Human-readable time difference
     */
    public static function time_elapsed_string( $datetime ) : string {
        $timestamp = strtotime( $datetime );
        if ( ! $timestamp ) {
            return 'just now';
        }

        $time_diff = human_time_diff( $timestamp );
        return $time_diff . ' ago';
    } // End time_elapsed_string()


    /**
     * Base64 URL-safe encoding
     *
     * @param string $data Data to encode
     * @return string URL-safe Base64 encoded string
     */
    public static function base64url_encode( $data ) : string {
        return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
    } // End base64url_encode()


    /**
     * Base64 URL-safe decoding
     *
     * @param string $data URL-safe Base64 encoded string
     * @return string Decoded data
     */
    public static function base64url_decode( $data ) : string {
        return base64_decode( str_pad( strtr( $data, '-_', '+/' ), strlen( $data ) % 4, '=', STR_PAD_RIGHT ) );
    } // End base64url_decode()


    /**
     * Remove query strings from url without refresh
     *
     * @param null|string|array $qs Query strings to remove; can be a string or array of keys.     * 
     * @return void
     */
    public static function remove_qs_without_refresh( $qs = null ) {
        if ( ! is_null( $qs ) ) {
            if ( ! is_array( $qs ) ) {
                $qs = [ $qs ];
            }
            $new_url = remove_query_arg( $qs, self::get_current_url() );
        } else {
            $new_url = self::get_current_url( false );
        }
        
        $args = [
            'title' => is_admin() ? get_admin_page_title() : get_the_title(),
            'url'   => $new_url,
        ];

        $handle = 'helpdocs-remove-qs';

        wp_enqueue_script(
            $handle,
            Bootstrap::url( 'inc/js/qs-remove.js' ),
            [ 'jquery' ],
            Bootstrap::script_version(),
            true
        );

        wp_localize_script( $handle, 'helpdocs_remove_qs', $args );
    } // End remove_qs_without_refresh()


    /**
     * Add query strings to url without refresh
     *
     * @param string|array $qs
     * @param string|array $value
     * @return void
     */
    public static function add_qs_without_refresh( $qs, $value ) {
        $new_url = add_query_arg( $qs, $value, self::get_current_url() );
        
        $args = [
            'title' => is_admin() ? get_admin_page_title() : get_the_title(),
            'url'   => $new_url,
        ];

        $handle = 'helpdocs-add-qs';

        wp_enqueue_script(
            $handle,
            Bootstrap::url( 'inc/js/qs-add.js' ),
            [ 'jquery' ],
            Bootstrap::script_version(),
            true
        );

        wp_localize_script( $handle, 'helpdocs_add_qs', $args );
    } // End add_qs_without_refresh()


    /**
     * Get the plugin page title, which can be customized in settings
     *
     * @return string The page title
     */
    public static function page_title() {
        return sanitize_text_field( get_option( 'helpdocs_page_title', Bootstrap::name() ) );
    } // End page_title()


    /**
     * Get the list of available Dashicons, which can be filtered by other code
     *
     * @return array List of Dashicon class names
     */
    public static function get_dashicons() {
        $dashicons = [
            'menu', 'admin-site', 'dashboard', 'admin-media', 'admin-page', 'admin-comments', 'admin-appearance', 'admin-plugins', 'admin-users', 'admin-tools', 'admin-settings',
            'admin-network', 'admin-generic', 'admin-home', 'admin-collapse', 'filter', 'admin-customizer', 'admin-multisite', 'admin-links', 'format-links', 'admin-post',
            'format-standard', 'format-image', 'format-gallery', 'format-audio', 'format-video', 'format-chat', 'format-status', 'format-aside', 'format-quote', 'welcome-write-blog',
            'welcome-edit-page', 'welcome-add-page', 'welcome-view-site', 'welcome-widgets-menus', 'welcome-comments', 'welcome-learn-more', 'image-crop', 'image-rotate', 'image-rotate-left',
            'image-rotate-right', 'image-flip-vertical', 'image-flip-horizontal', 'image-filter', 'undo', 'redo', 'editor-bold', 'editor-italic', 'editor-ul', 'editor-ol', 'editor-quote',
            'editor-alignleft', 'editor-aligncenter', 'editor-alignright', 'editor-insertmore', 'editor-spellcheck', 'editor-distractionfree', 'editor-expand', 'editor-contract',
            'editor-kitchensink', 'editor-underline', 'editor-justify', 'editor-textcolor', 'editor-paste-word', 'editor-paste-text', 'editor-removeformatting', 'editor-video',
            'editor-customchar', 'editor-outdent', 'editor-indent', 'editor-help', 'editor-strikethrough', 'editor-unlink', 'editor-rtl', 'editor-break', 'editor-code', 'editor-paragraph',
            'editor-table', 'align-left', 'align-right', 'align-center', 'align-none', 'lock', 'unlock', 'calendar', 'calendar-alt', 'visibility', 'hidden', 'post-status', 'edit',
            'post-trash', 'trash', 'sticky', 'external', 'arrow-up', 'arrow-down', 'arrow-left', 'arrow-right', 'arrow-up-alt', 'arrow-down-alt', 'arrow-left-alt', 'arrow-right-alt',
            'arrow-up-alt2', 'arrow-down-alt2', 'arrow-left-alt2', 'arrow-right-alt2', 'leftright', 'sort', 'randomize', 'list-view', 'excerpt-view', 'grid-view', 'hammer', 'art', 'migrate',
            'performance', 'universal-access', 'universal-access-alt', 'tickets', 'nametag', 'clipboard', 'heart', 'megaphone', 'schedule', 'wordpress', 'wordpress-alt', 'pressthis', 'update',
            'screenoptions', 'cart', 'feedback', 'cloud', 'translation', 'tag', 'category', 'archive', 'tagcloud', 'text', 'media-archive', 'media-audio', 'media-code', 'media-default',
            'media-document', 'media-interactive', 'media-spreadsheet', 'media-text', 'media-video', 'playlist-audio', 'playlist-video', 'controls-play', 'controls-pause', 'controls-forward',
            'controls-skipforward', 'controls-back', 'controls-skipback', 'controls-repeat', 'controls-volumeon', 'controls-volumeoff', 'yes', 'no', 'no-alt', 'plus', 'plus-alt',
            'plus-alt2', 'minus', 'dismiss', 'marker', 'star-filled', 'star-half', 'star-empty', 'flag', 'info', 'warning', 'share', 'share1', 'share-alt', 'share-alt2', 'twitter', 'rss',
            'email', 'email-alt', 'facebook', 'facebook-alt', 'networking', 'googleplus', 'location', 'location-alt', 'camera', 'images-alt', 'images-alt2', 'video-alt', 'video-alt2', 'video-alt3',
            'vault', 'shield', 'shield-alt', 'sos', 'search', 'slides', 'analytics', 'chart-pie', 'chart-bar', 'chart-line', 'chart-area', 'groups', 'businessman', 'id', 'id-alt', 'products',
            'awards', 'forms', 'testimonial', 'portfolio', 'book', 'book-alt', 'download', 'upload', 'backup', 'clock', 'lightbulb', 'microphone', 'desktop', 'tablet', 'smartphone', 'phone',
            'smiley', 'index-card', 'carrot', 'building', 'store', 'album', 'palmtree', 'tickets-alt', 'money', 'thumbs-up', 'thumbs-down', 'layout', 'align-pull-left', 'align-pull-right',
            'block-default', 'cloud-saved', 'cloud-upload', 'columns', 'cover-image', 'embed-audio', 'embed-generic', 'embed-photo', 'embed-post', 'embed-video', 'exit', 'html', 'info-outline',
            'insert-after', 'insert-before', 'insert', 'remove', 'shortcode', 'table-col-after', 'table-col-before', 'table-col-delete', 'table-row-after', 'table-row-before', 'table-row-delete',
            'saved', 'amazon', 'google', 'linkedin', 'pinterest', 'podio', 'reddit', 'spotify', 'twitch', 'whatsapp', 'xing', 'youtube', 'database-add', 'database-export', 'database-import',
            'database-remove', 'database-view', 'database', 'bell', 'airplane', 'car', 'calculator', 'ames', 'printer', 'beer', 'coffee', 'drumstick', 'food', 'bank', 'hourglass', 'money-alt',
            'open-folder', 'pdf', 'pets', 'privacy', 'superhero', 'superhero-alt', 'edit-page', 'fullscreen-alt', 'fullscreen-exit-alt'
        ];

        return apply_filters( 'helpdocs_dashicons', $dashicons );
    } // End get_dashicons()


    /**
     * Get the currently selected Dashicon for the plugin's menu, with a default fallback
     *
     * @return string The Dashicon class name (e.g. 'dashicons-admin-site')
     */
    public static function get_icon() {
        $icon = sanitize_text_field( get_option( 'helpdocs_dashicon', 'dashicons-editor-help' ) );
        if ( ! str_starts_with( $icon, 'dashicons-' ) ) {
            $icon = 'dashicons-' . $icon;
        }
        return $icon;
    } // End get_icon()


    /**
     * Output the logo image HTML if the option to include the logo is enabled
     *
     * @return void
     */
    public static function maybe_include_logo() {
        if ( ! get_option( 'helpdocs_include_logo_on_docs', true ) ) {
            return;
        }
        $logo_url = sanitize_text_field( get_option( 'helpdocs_doc_logo', self::get_page_or_default_logo_url() ) );
        if ( $logo_url ) {
            return '<div class="helpdocs-doc-logo"><img src="' . esc_url( $logo_url ) . '" alt="' . __( 'Help Doc Logo', 'admin-help-docs' ) . ' Logo"></div>';
        }
        return;
    } // End maybe_include_logo()


    /**
     * Get an array of help docs for use in settings options
     *
     * @return array Array of help doc options with 'label' and 'value' keys
     */
    public static function get_main_helpdoc_options() : array {
        $docs = self::get_docs( [ 'site_location' => 'main' ] );

        $options = [];
        $options[] = [
            'label' => __( '-- None --', 'admin-help-docs' ),
            'value' => '',
        ];
        if ( ! empty( $docs ) ) {
            foreach ( $docs as $doc ) {
                $options[] = [
                    'label' => get_the_title( $doc->ID ),
                    'value' => $doc->ID,
                ];
            }
        }
        return $options;
    } // End get_main_helpdoc_options()


    /**
     * Get the default logo URL for the plugin
     *
     * @return string URL of the default logo image
     */
    public static function get_default_logo_url() : string {
        return Bootstrap::url( 'inc/img/logo.png' );
    } // End get_default_logo_url()


    /**
     * Get the logo URL to use on the docs page, either the custom one set in options or the default
     *
     * @return string URL of the logo image to use
     */
    public static function get_page_or_default_logo_url() : string {
        return sanitize_text_field( get_option( 'helpdocs_logo', self::get_default_logo_url() ) );
    } // End get_page_or_default_logo_url()


    /**
     * Get the menu title for the plugin's admin menu, which can be customized in settings
     *
     * @return string The menu title
     */
    public static function get_menu_title() : string {
        return sanitize_text_field( get_option( 'helpdocs_menu_title', 'Help Docs' ) );
    } // End get_menu_title()


    /**
     * Check if the current request is valid for saving a help doc, including nonce verification and capability checks
     *
     * @param string $post_type The expected post type (e.g. 'helpdoc')
     * @param string $nonce_action The expected nonce action string
     * @param string $nonce_name The expected nonce field name in $_POST
     * @return bool True if the post can be saved, false otherwise
     */
    public static function can_save_post( $post_type, $nonce_action, $nonce_name ) : bool {
        // Check if our nonce is set.
        if ( ! isset( $_POST[ $nonce_name ] ) ) {
            return false;
        }
     
        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST[ $nonce_name ] ) ), $nonce_action ) ) {
            return false;
        }
     
        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return false;
        }
     
        // Check the user's permissions.
        if ( isset( $_POST[ 'post_type' ] ) && $_POST[ 'post_type' ] != $post_type ) {
            return false;
        }

        // Capability check
        if ( ! current_user_can( 'edit_posts' ) ) {
            return false;
        }

        return true;
    } // End can_save_post()


    /**
     * Get all relevant docs for the current screen based on location
     * 
     * @param object $screen Optional WP_Screen object to use instead of current screen
     * @param string $page_location 'top', 'bottom', or 'contextual'
     * @return array Combined array of unique WP_Post objects
     */
    public static function get_current_screen_docs( $screen = null, $page_location = null ) : array {
        $screen = $screen ?? get_current_screen();
        if ( ! $screen ) {
            return [];
        }

        global $pagenow;
        $relative_path = ( in_array( $pagenow, [ 'post.php', 'post-new.php' ] ) ) ? 'post.php' : ( ( $pagenow === 'edit.php' ) ? 'edit.php' : $pagenow );
        $should_group = empty( $page_location );

        // 1. Get standard location-based docs
        $args = [
            'site_location' => $relative_path,
            'post_type'     => $screen->post_type,
        ];
        if ( ! $should_group ) {
            $args[ 'page_location' ] = $page_location;
        }
        $standard_docs = self::get_docs( $args, $should_group );

        // 2. Get custom URL-based docs
        $current_full_url = admin_url( $pagenow );
        if ( ! empty( $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $sanitized_get    = map_deep( wp_unslash( $_GET ), 'sanitize_text_field' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $current_full_url = add_query_arg( $sanitized_get, $current_full_url );
        }

        $custom_args = [
            'site_location' => 'custom',
            'custom'        => $current_full_url,
            'post_type'     => $screen->post_type,
        ];
        if ( ! $should_group ) {
            $custom_args[ 'page_location' ] = $page_location;
        }
        $custom_docs = self::get_docs( $custom_args, $should_group );

        // 4. Merge and Unique-ify
        if ( ! $should_group ) {
            // Handle flat array merge for specific location request
            $all_docs  = array_merge( $standard_docs, $custom_docs );
            $unique    = [];
            $added_ids = [];
            foreach ( $all_docs as $doc ) {
                if ( ! in_array( $doc->ID, $added_ids ) ) {
                    $unique[]    = $doc;
                    $added_ids[] = $doc->ID;
                }
            }
            return $unique;
        }

        // Grouped Merge: Track unique IDs PER location
        $merged = $standard_docs;
        
        foreach ( $custom_docs as $loc => $docs ) {
            if ( ! isset( $merged[ $loc ] ) ) {
                $merged[ $loc ] = $docs;
                continue;
            }

            // Get IDs already present in THIS specific location bucket
            $existing_ids = wp_list_pluck( $merged[ $loc ], 'ID' );

            foreach ( $docs as $doc ) {
                if ( ! in_array( $doc->ID, $existing_ids ) ) {
                    $merged[ $loc ][] = $doc;
                    $existing_ids[]   = $doc->ID; // Update local tracker
                }
            }
        }

        return $merged;
    } // End get_current_screen_docs()


    /**
     * Retrieves help documents based on location settings, categories, and tags.
     *
     * @param array $loc_args {
     * Optional. Arguments to filter the docs.
     * 
     * @type string $site_location Required. The admin page slug or unique key.
     * @type string $page_location Optional. The specific area (top, side, bottom).
     * @type string $custom        Optional. The custom URL to match.
     * @type bool   $addt_params   Optional. Whether to match docs with additional parameters.
     * @type bool   $toc           Optional. Whether to match docs marked for Table of Contents.
     * @type string $post_type     Optional. The post type context being viewed.
     * @type int    $category      Optional. Category ID to filter by.
     * @type string $tag           Optional. Tag slug to filter by.
     * }
     * @return array List of matching post objects.
     */
    public static function get_docs( $loc_args = [], $group_by_location = false ) {
        $site_location = $loc_args[ 'site_location' ] ?? '';
        if ( empty( $site_location ) ) {
            return [];
        }

        $test_mode = Bootstrap::is_test_mode();
        $cache_key = '';
        
        $current_user       = wp_get_current_user();
        $current_user_roles = (array) $current_user->roles;
        $is_admin           = in_array( self::admin_role(), $current_user_roles, true );

        if ( ! $test_mode ) {
            $user_seed = $is_admin ? 'admin' : implode( '-', $current_user_roles );
            $cache_key = 'helpdocs_loc_' . md5( wp_json_encode( $loc_args ) . $user_seed );
            $cached    = get_transient( $cache_key );
            if ( false !== $cached ) {
                return $cached;
            }
        }

        $encoded_location = base64_encode( $site_location );

        $query_args = [
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'post_type'      => HelpDocs::$post_type,
            'meta_query'     => [
                'relation' => 'OR',
                [
                    'key'     => 'helpdocs_site_location',
                    'value'   => $encoded_location,
                    'compare' => '=',
                ],
                [
                    'key'     => 'helpdocs_locations',
                    'compare' => 'EXISTS',
                ],
            ],
        ];

        if ( ! empty( $loc_args[ 'category' ] ) ) {
            $query_args[ 'category' ] = absint( $loc_args[ 'category' ] );
        }

        if ( ! empty( $loc_args[ 'tag' ] ) ) {
            $query_args[ 'tag' ] = sanitize_text_field( $loc_args[ 'tag' ] );
        }

        $query_results = get_posts( $query_args );
        $imports       = self::get_imports( $loc_args );
        $all_potential = array_merge( $query_results, $imports );

        $global_access_type  = get_option( 'helpdocs_user_view_type', 'capability' );
        $global_access_cap   = get_option( 'helpdocs_user_view_cap', 'manage_options' );
        $global_access_roles = (array) get_option( 'helpdocs_view_roles', [] );
        
        $filtered_docs = [];

        foreach ( $all_potential as $doc ) {

            $has_permission = false;
            $is_import = str_starts_with( $doc->ID, 'import_' );

            if ( $is_admin ) {
                $has_permission = true;
            } else {
                // Check specific doc roles
                $raw_doc_roles = $is_import ? $doc->helpdocs_view_roles : get_post_meta( $doc->ID, 'helpdocs_view_roles', true );
                $doc_roles     = maybe_unserialize( $raw_doc_roles );

                if ( is_array( $doc_roles ) && ! empty( $doc_roles ) ) {
                    if ( array_intersect( $current_user_roles, $doc_roles ) ) {
                        $has_permission = true;
                    }
                } else {
                    if ( 'capability' === $global_access_type ) {
                        if ( current_user_can( $global_access_cap ) ) {
                            $has_permission = true;
                        }
                    } elseif ( 'role' === $global_access_type ) {
                        if ( array_intersect( $current_user_roles, $global_access_roles ) ) {
                            $has_permission = true;
                        }
                    }
                }
            }

            if ( ! $has_permission ) {
                continue;
            }
            
            $locations = $is_import ? $doc->helpdocs_locations : get_post_meta( $doc->ID, 'helpdocs_locations', true );
            if ( ! is_array( $locations ) ) {
                $locations = [];
            }
            $locations = map_deep( wp_unslash( $locations ), 'sanitize_text_field' );

            $doc_added_to_flat = false;
            if ( is_array( $locations ) && ! empty( $locations ) ) {
                foreach ( $locations as $loc ) {
                    $match = true;

                    if ( ( $loc[ 'site_location' ] ?? '' ) !== $encoded_location ) {
                        $match = false;
                    }

                    if ( $match && isset( $loc_args[ 'page_location' ] ) && ( $loc[ 'page_location' ] ?? '' ) !== $loc_args[ 'page_location' ] ) {
                        $match = false;
                    }

                    if ( $match && $site_location === 'custom' && isset( $loc_args[ 'custom' ] ) ) {
                        $saved_url    = $loc[ 'custom' ] ?? '';
                        $passed_url   = $loc_args[ 'custom' ];
                        $ignore_extra = ! empty( $loc[ 'addt_params' ] );

                        if ( ! self::compare_urls( $saved_url, $passed_url, $ignore_extra ) ) {
                            $match = false;
                        }
                    } elseif ( $match && isset( $loc_args[ 'custom' ] ) && ( $loc[ 'custom' ] ?? '' ) !== $loc_args[ 'custom' ] ) {
                        $match = false;
                    }

                    if ( $match && isset( $loc_args[ 'toc' ] ) && ! empty( $loc[ 'toc' ] ) !== (bool) $loc_args[ 'toc' ] ) {
                        $match = false;
                    }

                    if ( $match && isset( $loc_args[ 'post_type' ] ) && ! empty( $loc[ 'post_types' ] ) ) {
                        if ( ! in_array( $loc_args[ 'post_type' ], (array) $loc[ 'post_types' ] ) ) {
                            $match = false;
                        }
                    }

                    if ( $match ) {
                        $matched_location_key = $loc[ 'page_location' ] ?? 'contextual';

                        $doc->helpdocs_order = $doc->helpdocs_order ?? ( isset( $loc[ 'order' ] ) ? (int) $loc[ 'order' ] : 0 );

                        // Check if this is an 'element' location and grab the selector
                        if ( $matched_location_key === 'element' && ! empty( $loc[ 'css_selector' ] ) ) {
                            $doc->css_selector = sanitize_text_field( $loc[ 'css_selector' ] );
                        }
                        
                        if ( $group_by_location ) {
                            $filtered_docs[ $matched_location_key ][] = $doc;
                        } elseif ( ! $doc_added_to_flat ) {
                            $filtered_docs[]   = $doc;
                            $doc_added_to_flat = true;
                        }
                    }
                }
            } else {
                // Legacy Fallback Logic
                $match = true;
                $legacy_site = sanitize_text_field( get_post_meta( $doc->ID, 'helpdocs_site_location', true ) );
                
                if ( $legacy_site !== $encoded_location ) {
                    $match = false;
                }

                if ( $match && isset( $loc_args[ 'page_location' ] ) ) {
                    $val = sanitize_text_field( get_post_meta( $doc->ID, 'helpdocs_page_location', true ) );
                    if ( $val !== $loc_args[ 'page_location' ] ) {
                        $match = false;
                    }
                }

                if ( $match && isset( $loc_args[ 'custom' ] ) ) {
                    $saved_url    = sanitize_text_field( get_post_meta( $doc->ID, 'helpdocs_custom', true ) );
                    $passed_url   = $loc_args[ 'custom' ];
                    $ignore_extra = ! empty( get_post_meta( $doc->ID, 'helpdocs_addt_params', true ) );

                    if ( ! self::compare_urls( $saved_url, $passed_url, $ignore_extra ) ) {
                        $match = false;
                    }
                }

                if ( $match && isset( $loc_args[ 'toc' ] ) ) {
                    $val = get_post_meta( $doc->ID, 'helpdocs_toc', true );
                    if ( filter_var( $val, FILTER_VALIDATE_BOOLEAN ) !== (bool) $loc_args[ 'toc' ] ) {
                        $match = false;
                    }
                }

                if ( $match && isset( $loc_args[ 'post_type' ] ) ) {
                    $raw_pts = get_post_meta( $doc->ID, 'helpdocs_post_types', true );
                    $pts     = self::normalize_meta_array( $raw_pts );
                    
                    if ( ! empty( $pts ) && ! in_array( $loc_args[ 'post_type' ], $pts ) ) {
                        $match = false;
                    }
                }

                if ( $match ) {
                    $legacy_order = get_post_meta( $doc->ID, 'helpdocs_order', true );
                    $doc->helpdocs_order = $doc->helpdocs_order ?? ( $legacy_order !== '' ? (int) $legacy_order : 0 );

                    if ( $group_by_location ) {
                        $matched_location_key = sanitize_text_field( get_post_meta( $doc->ID, 'helpdocs_page_location', true ) ) ?: 'contextual';
                        $filtered_docs[ $matched_location_key ][] = $doc;
                    } else {
                        $filtered_docs[] = $doc;
                    }
                }
            }
        }

        if ( ! empty( $filtered_docs ) && ! $group_by_location ) {
            usort( $filtered_docs, function( $a, $b ) {
                // 1. Get Normalized Orders
                $order_a = isset( $a->helpdocs_order ) ? (int) $a->helpdocs_order : 0;
                $order_b = isset( $b->helpdocs_order ) ? (int) $b->helpdocs_order : 0;

                // 2. Primary Sort: helpdocs_order (Numerical)
                if ( $order_a !== $order_b ) {
                    return $order_a <=> $order_b;
                }

                // 3. Secondary Sort: Origin Tie-breaker (Local vs Import)
                // We cast to string to ensure str_starts_with doesn't choke on integers
                $a_is_import = str_starts_with( (string) $a->ID, 'import_' );
                $b_is_import = str_starts_with( (string) $b->ID, 'import_' );

                if ( $a_is_import !== $b_is_import ) {
                    return $a_is_import ? 1 : -1; // Local first if orders match
                }

                // 4. Tertiary Sort: Alphabetical
                return strcasecmp( $a->post_title, $b->post_title );
            } );
        }

        if ( ! $test_mode && ! empty( $cache_key ) ) {
            set_transient( $cache_key, $filtered_docs, 12 * HOUR_IN_SECONDS );
        }

        return $filtered_docs;
    } // End get_docs()


    /**
     * Get imported docs based on location settings, categories, and tags.
     *
     * @param array $loc_args
     * @return array List of matching post objects.
     */
    public static function get_imports( $loc_args = [] ) : array {
        $site_location = $loc_args[ 'site_location' ] ?? '';
        if ( empty( $site_location ) ) {
            return [];
        }

        $encoded_location = base64_encode( $site_location );

        $import_posts = get_posts( [
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'post_type'      => Imports::$post_type,
        ] );

        if ( empty( $import_posts ) ) {
            return [];
        }

        $filtered_remote_docs = [];

        foreach ( $import_posts as $import ) {
            $url     = get_post_meta( $import->ID, 'helpdocs_url', true );
            $api_key = get_post_meta( $import->ID, 'helpdocs_api_key', true );

            $remote_data = ImportEditor::get_all_import_data( $url, $api_key );
            if ( empty( $remote_data[ 'docs' ] ) ) {
                continue;
            }

            $remote_version = $remote_data[ 'version' ] ?? 'v1';
            $selected_docs  = get_post_meta( $import->ID, 'helpdocs_docs', true ) ?: [];
            $import_all     = get_post_meta( $import->ID, 'helpdocs_all', true );

            $local_customs = get_post_meta( $import->ID, 'helpdocs_local_customizations', true ) ?: [];

            foreach ( $remote_data[ 'docs' ] as $doc ) {

                $remote_id = absint( $doc->ID );
                $custom    = $local_customs[ $remote_id ] ?? null;
                $local_order  = ( $custom && isset( $custom[ 'order' ] ) ) ? absint( $custom[ 'order' ] ) : null;
                $local_folder = ( $custom && isset( $custom[ 'folder' ] ) ) ? absint( $custom[ 'folder' ] ) : 0;

                $view_roles = get_post_meta( $doc->ID, 'view_roles', true ) ?: [];
                
                // 1. Initial Selection Filter
                if ( ! $import_all && ! in_array( $doc->ID, (array) $selected_docs ) ) {
                    continue;
                }

                // 2. Inline Taxonomy Filtering
                if ( ! empty( $loc_args[ 'category' ] ) ) {
                    $doc_cats = $doc->taxonomies->category ?? [];
                    $cat_ids  = wp_list_pluck( (array) $doc_cats, 'term_id' );
                    if ( ! in_array( absint( $loc_args[ 'category' ] ), $cat_ids ) ) {
                        continue;
                    }
                }

                if ( ! empty( $loc_args[ 'tag' ] ) ) {
                    $doc_tags  = $doc->taxonomies->post_tag ?? [];
                    $tag_slugs = wp_list_pluck( (array) $doc_tags, 'slug' );
                    if ( ! in_array( sanitize_text_field( $loc_args[ 'tag' ] ), $tag_slugs ) ) {
                        continue;
                    }
                }

                // 3. Inline Normalization (Standardize to V2 structure)
                $object = (object) [
                    'ID'                  => 'import_' . absint( $doc->ID ),
                    'post_title'          => sanitize_text_field( $doc->title ),
                    'post_content'        => wp_kses_post( $doc->content ),
                    'auto_feed'           => $import->post_title,
                    'feed_id'             => $import->ID,
                    'created_by'          => sanitize_text_field( $doc->created_by ?? 'an unknown author' ),
                    'helpdocs_view_roles' => $view_roles,
                    'helpdocs_locations'  => [],
                    'local_folder_id'     => $local_folder,
                    'taxonomies'          => $doc->taxonomies ?? new \stdClass(),
                ];

                if ( 'v2' === $remote_version && isset( $doc->locations ) ) {
                    $object->helpdocs_locations = array_map( function( $location ) {
                        // Cast to object if it's currently a stdClass to ensure property access works
                        $loc = (object) $location;

                        return [
                            'site_location' => sanitize_text_field( $loc->site_location ?? '' ),
                            'page_location' => sanitize_text_field( $loc->page_location ?? 'contextual' ),
                            'custom'        => filter_var( $loc->custom ?? '', FILTER_SANITIZE_URL ),
                            'addt_params'   => ! empty( $loc->addt_params ),
                            'post_types'    => (array) ( $loc->post_types ?? [] ),
                            'order'         => absint( $loc->order ?? 0 ),
                            'toc'           => ! empty( $loc->toc ),
                        ];
                    }, $doc->locations );
                } else {
                    $object->helpdocs_locations = [ [
                        'site_location' => sanitize_text_field( $doc->site_location ?? '' ),
                        'page_location' => sanitize_text_field( $doc->page_location ?? 'contextual' ),
                        'custom'        => filter_var( $doc->custom ?? '', FILTER_SANITIZE_URL ),
                        'addt_params'   => ! empty( $doc->addt_params ),
                        'post_types'    => $doc->post_types ?? [],
                        'order'         => absint( $doc->order ?? 0 ),
                        'toc'           => ! empty( $doc->toc ),
                    ] ];
                }

                // 4. Location Filtering (Repeater Logic)
                $matched = false;
                foreach ( (array) $object->helpdocs_locations as $loc ) {
                    $loc = map_deep( wp_unslash( $loc ), 'sanitize_text_field' );
                    $inner_match = true;
                    
                    if ( ( $loc[ 'site_location' ] ?? '' ) !== $encoded_location ) {
                        $inner_match = false;
                    }

                    if ( $inner_match && isset( $loc_args[ 'page_location' ] ) && ( $loc[ 'page_location' ] ?? '' ) !== $loc_args[ 'page_location' ] ) {
                        $inner_match = false;
                    }

                    if ( $inner_match && $site_location === 'custom' && isset( $loc_args[ 'custom' ] ) ) {
                        $saved_url    = $loc[ 'custom' ] ?? '';
                        $passed_url   = $loc_args[ 'custom' ];
                        $ignore_extra = ! empty( $loc[ 'addt_params' ] );

                        if ( ! self::compare_urls( $saved_url, $passed_url, $ignore_extra ) ) {
                            $inner_match = false;
                        }
                    }

                    if ( $inner_match && isset( $loc_args[ 'toc' ] ) && ( ! empty( $loc[ 'toc' ] ) ) !== (bool) $loc_args[ 'toc' ] ) {
                        $inner_match = false;
                    }

                    if ( $inner_match && isset( $loc_args[ 'post_type' ] ) && ! empty( $loc[ 'post_types' ] ) ) {
                        if ( ! in_array( $loc_args[ 'post_type' ], (array) $loc[ 'post_types' ] ) ) {
                            $inner_match = false;
                        }
                    }

                    if ( $inner_match ) {
                        if ( ( $loc[ 'page_location' ] ?? '' ) === 'element' && ! empty( $loc[ 'css_selector' ] ) ) {
                            $object->css_selector = $loc[ 'css_selector' ];
                        }

                        if ( null !== $local_order ) {
                            $object->helpdocs_order = $local_order;
                        } else {
                            $object->helpdocs_order = isset( $loc[ 'order' ] ) ? absint( $loc[ 'order' ] ) : 0;
                        }

                        $matched = true;
                        break;
                    }
                }

                if ( $matched ) {                    
                    $filtered_remote_docs[] = $object;
                }
            }
        }

        return $filtered_remote_docs;
    } // End get_imports()


    /**
     * Clean and format doc content for Gutenberg display, including handling line breaks and escaping
     *
     * @param array $docs Array of WP_Post objects representing the docs
     * @return array Array of cleaned docs with 'id', 'title', and 'content' keys
     */
    public static function clean_docs_for_gutenberg( $docs ) {
        $cleaned_docs = [];
        foreach ( $docs as $doc ) {

            $content = str_replace( "'", "\\'", $doc->post_content );           // Add \ to single quotes
            $content = preg_replace( "/\r\n<ul>\r\n/", "<ul>", $content );      // Replace line breaks wrapping <ul>
            $content = preg_replace( "/\r\n<\/ul>/", "</ul>", $content );       // Replace line breaks before </ul>
            $content = preg_replace( "/<\/li>\r\n/", "</li>", $content );       // Replace line breaks before </ul>
            $content = preg_replace( "/\r\n/", "<br>", $content );              // Replace double line breaks with single
            $content = nl2br( $content ); 

            // $content = apply_filters( 'the_content', $doc->post_content );
            // $content = nl2br( $content );

            $cleaned_docs[] = [
                'id'           => $doc->ID,
                'title'        => $doc->post_title,
                'content'      => wp_kses_post( $content ),
                'css_selector' => $doc->css_selector ?? ''
            ];
        }

        return $cleaned_docs;
    } // End clean_docs_for_gutenberg()


    /**
     * Retrieves a single help doc by ID
     * 
     * @param int $id The help doc ID
     * @return \WP_Post|null Returns the post object if valid, null otherwise
     */
    public static function get_doc( $id ) {
        $id = absint( $id );
        if ( ! self::user_can_view( $id ) ) {
            return null;
        }

        $doc = get_post( $id );

        if ( ! $doc || $doc->post_type !== HelpDocs::$post_type || $doc->post_status !== 'publish' ) {
            return null;
        }

        $encoded_function_loc = base64_encode( 'function' );

        // 1. Check New Method (helpdocs_locations array)
        $locations = map_deep( get_post_meta( $doc->ID, 'helpdocs_locations', true ), 'sanitize_text_field' );
        
        if ( is_array( $locations ) && ! empty( $locations ) ) {
            foreach ( $locations as $loc ) {
                
                // Verify that this location entry is for the 'function' site_location
                if ( ( $loc[ 'site_location' ] ?? '' ) === $encoded_function_loc ) {
                    return $doc;
                }
            }
        }

        // 2. Check Legacy Method
        $legacy_site = sanitize_text_field( get_post_meta( $doc->ID, 'helpdocs_site_location', true ) );
        if ( $legacy_site === $encoded_function_loc ) {
            return $doc;
        }

        return null;
    } // End get_doc()


    /**
     * Output the HTML for a help doc on the frontend, including title and content, with proper escaping
     *
     * @param int $doc_id The ID of the doc post
     * @param string $doc_content The raw content of the doc post
     * @param string $page_location The location on the page where this doc is being output (e.g. 'top', 'side', 'bottom')
     * @return void Outputs the HTML directly
     */
    public static function output_doc( $doc_id, $doc_title, $doc_content, $page_location ) {
        $title = ( '{doc_id}' === $doc_id ) ? '{doc_title}' : $doc_title;
        $logo  = self::maybe_include_logo();
        $location = esc_attr( $page_location );
        $safe_title = esc_html( $title );

        return "<div class='helpdocs-doc-wrapper helpdocs-{$location}-doc'>
            <div class='helpdocs-doc-title'>
                {$logo}
                <h2>{$safe_title}</h2>
            </div>
            <div class='helpdocs-doc-content'>
                {$doc_content}
            </div>
        </div>";
    } // End output_doc()


    /**
     * Allow additional tags for content feed embeds
     *
     * @param array $tags The allowed tags
     * @return array The modified allowed tags
     */
    public static function allow_addt_tags( $tags ) {
        $tags = array_merge( $tags, [
            'script' => [
                'type'                      => true,
                'src'                       => true,
                'async'                     => true,
                'defer'                     => true,
                'crossorigin'               => true,
                'integrity'                 => true,
            ],
            'video' => [
                'src'                       => true,
                'controls'                  => true,
                'autoplay'                  => true,
                'loop'                      => true,
                'muted'                     => true,
                'poster'                    => true,
                'width'                     => true,
                'height'                    => true,
            ],
            'source' => [
                'src'                       => true,
                'type'                      => true,
            ],
            'iframe' => [
                'src'                       => true,
                'width'                     => true,
                'height'                    => true,
                'frameborder'               => true,
                'allow'                     => true,
                'allowfullscreen'           => true,
                'title'                     => true,
                'referrerpolicy'            => true,
                'webkitallowfullscreen'     => true,
                'mozallowfullscreen'        => true,
                'loading'                   => true,
                'style'                     => true,
            ],
        ] );

        return apply_filters( 'helpdocs_allowed_html', $tags );
    } // End allow_addt_tags()


    /**
     * Flush all help document location transients
     */
    public static function flush_location_cache() : void {
        global $wpdb;

        // Delete the transients and their timeout entries from the options table
        $wpdb->query( // phpcs:ignore
            $wpdb->prepare( // phpcs:ignore
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
                '_transient_helpdocs_loc_%',
                '_transient_timeout_helpdocs_loc_%'
            ) 
        );

        self::flush_permissions_cache();
    } // End flush_location_cache()


    /**
     * Flush all help document permissions transients
     */
    public static function flush_permissions_cache() : void {
        global $wpdb;

        // Delete the transients and their timeout entries from the options table
        $wpdb->query( // phpcs:ignore
            $wpdb->prepare( // phpcs:ignore
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
                '_transient_helpdocs_perm_%',
                '_transient_timeout_helpdocs_perm_%'
            ) 
        );
    } // End flush_permissions_cache()


    /**
     * Compare two URLs for equivalence, with options to ignore additional query parameters
     *
     * @param string $saved_url The URL saved in the doc settings
     * @param string $passed_url The URL to compare against (e.g. current page URL)
     * @param bool $ignore_extra Whether to ignore additional query parameters in the passed URL
     * @return bool True if the URLs match according to the criteria, false otherwise
     */
    public static function compare_urls( $saved_url, $passed_url, $ignore_extra = false ) : bool {
        $saved  = wp_parse_url( $saved_url );
        $passed = wp_parse_url( $passed_url );

        // Ensure the base path (e.g., /wp-admin/options-reading.php) matches
        if ( ( $saved[ 'path' ] ?? '' ) !== ( $passed[ 'path' ] ?? '' ) ) {
            return false;
        }

        $saved_query  = [];
        $passed_query = [];

        if ( ! empty( $saved[ 'query' ] ) ) {
            parse_str( $saved[ 'query' ], $saved_query );
        }

        if ( ! empty( $passed[ 'query' ] ) ) {
            parse_str( $passed[ 'query' ], $passed_query );
        }

        if ( $ignore_extra ) {
            // All params in $saved_query MUST exist in $passed_query with same values
            foreach ( $saved_query as $key => $value ) {
                if ( ! isset( $passed_query[ $key ] ) || $passed_query[ $key ] !== $value ) {
                    return false;
                }
            }
            return true;
        }

        // Strict Mode: Queries must match exactly regardless of order
        ksort( $saved_query );
        ksort( $passed_query );

        return $saved_query === $passed_query;
    } // End compare_urls()


    /**
     * Convert merge tags in a string to their corresponding user data values
     *
     * Supported merge tags include:
     * {first_name}, {last_name}, {display_name}, {user_email}, {user_login}, {user_id}
     *
     * @param string $string The input string containing merge tags
     * @return string The string with merge tags replaced by user data
     */
    public static function convert_merge_tags( $string ) : string {
        if ( strpos( $string, '{' ) === false || strpos( $string, '}' ) === false ) {
            return $string;
        }

        $current_user = wp_get_current_user();
        $replacements = [
            '{first_name}'   => $current_user->user_firstname,
            '{last_name}'    => $current_user->user_lastname,
            '{display_name}' => $current_user->display_name,
            '{user_email}'   => $current_user->user_email,
            '{user_login}'   => $current_user->user_login,
            '{user_id}'      => $current_user->ID,
        ];

        return str_replace( array_keys( $replacements ), array_values( $replacements ), $string );
    } // End convert_merge_tags()


    /**
     * Get admin page title from url
     *
     * @param string $url
     * @return string|false
     */
    public static function get_admin_page_title_from_url( $encoded_url ) {
        $url = base64_decode( $encoded_url );
        
        $all_locations = HelpDocs::site_locations();

        if ( isset( $all_locations[ $url ] ) ) {
            return $all_locations[ $url ][ 'label' ];
        }

        return str_replace( [ 'admin.php?page=', '.php' ], '', $url );
    } // End get_admin_page_title_from_url()


    /**
     * Get admin page link from site location entry
     *
     * @param array $loc
     * @return string|false URL if linkable, false if not linkable or invalid
     */
    public static function get_link_from_site_location( $post_id, $loc ) {
        if ( empty( $loc[ 'site_location' ] ) ) {
            return false;
        }

        $key = base64_decode( $loc[ 'site_location' ] );

        switch ( $key ) {
            case 'custom':
                return ! empty( $loc[ 'custom' ] ) ? esc_url( $loc[ 'custom' ] ) : false;

            case 'main':
                return add_query_arg( 'id', $post_id, Bootstrap::tab_url( 'documentation' ) );

            case 'replace_dashboard':
            case 'index.php':
                return admin_url( 'index.php' );

            case 'admin_bar':
            case 'function':
                return false;

            default:
                // Standard admin pages (e.g., edit.php, plugins.php)
                return admin_url( $key );
        }
    } // End get_link_from_site_location()


    /**
     * Normalize meta value to array for old checkboxes
     *
     * @param mixed $value
     * @return array
     */
    public static function normalize_meta_array( $value ) {
        if ( empty( $value ) ) {
            return [];
        } else {
            while ( ! is_array( $value ) ) {
                $unserialized = maybe_unserialize( $value );
                if ( $unserialized === $value ) {
                    break;
                }
                $value = $unserialized;
            }
        }

        return is_array( $value ) ? $value : [];
    } // End normalize_meta_array()

}