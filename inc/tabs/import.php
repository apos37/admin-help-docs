<?php
/**
 * Import Editor Loader
 */

namespace PluginRx\AdminHelpDocs;

if ( ! defined( 'ABSPATH' ) ) exit;

class ImportEditor {
    

    /**
     * The single instance of the class
     *
     * @var self|null
     */
    private static ?ImportEditor $instance = null;


    /**
     * Get the singleton instance
     *
     * @return self
     */
    public static function instance() : self {
        return self::$instance ??= new self();
    } // End instance()


    /**
     * Constructor
     */
    private function __construct() {
        add_action( 'helpdocs_subheader_left', [ $this, 'header_buttons' ] );
        add_action( 'helpdocs_subheader_right', [ $this, 'debug_quick_link' ] );
        add_action( 'admin_head', [ $this, 'pre_cache_locations' ] );
        add_action( 'admin_init', [ $this, 'save_import_settings' ] );
        add_action( 'wp_ajax_helpdocs_fetch_remote_docs', [ $this, 'ajax_fetch_remote_docs' ] );
        add_action( 'wp_ajax_helpdocs_import_individual_doc', [ $this, 'ajax_import_individual_doc' ] );
    } // End __construct()


    /**
     * Add header buttons
     *
     * @param string $current_tab The current tab slug
     */
    public function header_buttons( $current_tab ) {
        if ( $current_tab === 'import' ) {
            $import_id = isset( $_GET[ 'id' ] ) ? absint( $_GET[ 'id' ] ) : 0; // phpcs:ignore
            $status    = get_post_status( $import_id );
            
            $is_active = ( 'publish' === $status );
            $legacy_enabled = get_post_meta( $import_id, 'helpdocs_enabled', true );
            if ( '0' === $legacy_enabled ) {
                $is_active = false;
            }

            $show_success = isset( $_GET[ 'settings-updated' ] ) && 'true' === sanitize_text_field( wp_unslash( $_GET[ 'settings-updated' ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            ?>
            <div class="helpdocs-header-controls">
                <div class="helpdocs-active-toggle">
                    <span class="helpdocs-toggle-label"><?php esc_html_e( 'Active', 'admin-help-docs' ); ?></span>
                    <label class="helpdocs-switch">
                        <input type="checkbox" name="helpdocs_import_active" id="helpdocs_import_active" value="1" <?php checked( $is_active ); ?> form="helpdocs_import_form">
                        <span class="helpdocs-slider"></span>
                    </label>
                </div>
                <span id="helpdocs-inactive-notice" style="display: <?php echo ( $import_id && ! $is_active ) ? 'inline' : 'none'; ?>;">← <?php echo esc_html__( 'Don\'t forget to activate the import.', 'admin-help-docs' ); ?></span>
                <span id="helpdocs-save-reminder"><?php echo esc_html__( 'Remember to click "Save Import Settings" after making changes to your import.', 'admin-help-docs' ); ?></span>
                <span id="helpdocs-saved-success" style="display: <?php echo $show_success ? 'inline' : 'none'; ?>;"><?php echo esc_html__( 'Settings saved successfully.', 'admin-help-docs' ); ?></span>
            </div>
            <?php
        }
    } // End header_buttons()


    /**
     * Add debug quick link for Dev Debug Tools plugin if active
     *
     * @param string $current_tab The current tab slug
     */
    public function debug_quick_link( $current_tab ) {
        if ( $current_tab === 'import' ) {
            if ( is_plugin_active( 'dev-debug-tools/dev-debug-tools.php' ) ) {
                $import_id = isset( $_GET[ 'id' ] ) ? absint( $_GET[ 'id' ] ) : 0; // phpcs:ignore
                if ( ! $import_id ) {
                    return;
                }

                $icon = apply_filters( 'ddtt_quick_link_icon', '&#9889;' );
                $nonce = wp_create_nonce( 'ddtt_metadata_lookup' );
                ?>
                <div class="ddtt-debug-quick-link">
                    <span class="ddtt-icon"><?php echo esc_html( $icon ); ?></span><a href="/wp-admin/admin.php?page=dev-debug-tools&tool=metadata&s=post&lookup=<?php echo esc_attr( $import_id ); ?>&_wpnonce=<?php echo esc_attr( $nonce ); ?>" target="_blank">
                        <?php esc_html_e( 'Debug Import', 'admin-help-docs' ); ?>
                    </a>
                </div>
                <?php
            }
        }
    } // End debug_quick_link()


    /**
     * Pre-cache site locations on the import page to speed up the UI
     */
    public function pre_cache_locations() {
        if ( Menu::get_current_page() !== Bootstrap::textdomain() || Menu::get_current_tab() !== 'import' ) {
            return;
        }
        
        HelpDocs::site_locations();
    } // End pre_cache_locations()


    /**
     * Render the tab
     */
    public function render_tab() {
        $import_id   = isset( $_GET[ 'id' ] ) ? absint( $_GET[ 'id' ] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $post_title  = $import_id ? get_the_title( $import_id ) : '';
        $website_url = sanitize_url( get_post_meta( $import_id, 'helpdocs_url', true ) );
        
        $selected_docs = get_post_meta( $import_id, 'helpdocs_docs', true ) ?: [];
        $selected_tocs = get_post_meta( $import_id, 'helpdocs_tocs', true ) ?: [];

        $all_docs = get_post_meta( $import_id, 'helpdocs_all', true );
        $all_tocs = get_post_meta( $import_id, 'helpdocs_all_tocs', true );

        $api_key = get_post_meta( $import_id, 'helpdocs_api_key', true );

        $table_row_header = '
            <tr>
                <th scope="col" class="manage-column column-feed">' . esc_html__( 'Auto Feed', 'admin-help-docs' ) . '</th>
                <th scope="col" class="manage-column column-title">' . esc_html__( 'Title', 'admin-help-docs' ) . '</th>
                <th scope="col" class="manage-column column-date">' . esc_html__( 'Publish Date', 'admin-help-docs' ) . '</th>
                <th scope="col" class="manage-column column-author">' . esc_html__( 'Created By', 'admin-help-docs' ) . '</th>
                <th scope="col" class="manage-column column-location">' . esc_html__( 'Site Location', 'admin-help-docs' ) . '</th>
                <th scope="col" class="manage-column column-toc">' . esc_html__( 'TOC', 'admin-help-docs' ) . '</th>
                <th scope="col" class="manage-column column-action">' . esc_html__( 'Action', 'admin-help-docs' ) . '</th>
            </tr>';
        ?>
        <div class="helpdocs-full-width-box">

            <p class="helpdocs-instructions"><em><?php echo esc_html__( "You can choose to remotely feed documents from the other website, which will update automatically if they are changed on the other site. This is useful if you manage several sites and want to control them in one spot. You may also import them individually, which will clone them and add them to this website and no longer be linked to the remote site.", 'admin-help-docs' ); ?></em></p>

            <p class="helpdocs-instructions"><em><?php echo esc_html__( "The \"TOC\" option allows you to add the doc to the Dashboard Table of Contents, provided that you have enabled Dashboard TOC in your settings and the feed's site location is set to \"Main Documentation Page.\"", 'admin-help-docs' ); ?></em></p>
            
            <form id="helpdocs_import_form" method="post">
                <input type="hidden" name="action" value="helpdocs_import_docs">
                <input type="hidden" name="id" id="helpdocs_import_post_id" value="<?php echo esc_attr( $import_id ); ?>">
                <?php wp_nonce_field( 'helpdocs_save_import_nonce', 'helpdocs_save_import_nonce' ); ?>

                <div id="helpdocs_title_container">
                    <label for="helpdocs_import_title"><?php esc_html_e( 'Import Name', 'admin-help-docs' ); ?>:</label>
                    <input name="helpdocs_import_title" id="helpdocs_import_title" type="text" value="<?php echo esc_attr( $post_title ); ?>" placeholder="<?php esc_attr_e( 'e.g. My Remote Docs', 'admin-help-docs' ); ?>">
                </div>

                <div id="helpdocs_website_url_container">
                    <label for="helpdocs_website_url"><?php esc_html_e( 'Enter the URL of the website you would like to import help docs from', 'admin-help-docs' ); ?>:</label>
                    <div id="helpdocs_url_field">
                        <input name="helpdocs_website_url" id="helpdocs_website_url" type="url" value="<?php echo esc_url( $website_url ); ?>" placeholder="https://example.com">
                        
                        <div class="helpdocs-api-key-container">
                            <input name="helpdocs_api_key" id="helpdocs_api_key" type="password" value="<?php echo esc_attr( $api_key ); ?>" placeholder="<?php esc_attr_e( 'API Key (Optional)', 'admin-help-docs' ); ?>" style="width: 200px; padding-right: 30px;">
                            <span class="dashicons dashicons-visibility helpdocs-toggle-visibility"></span>
                        </div>
                        
                        <a id="helpdocs_fetch_remote_docs" class="helpdocs-button" href="#"><?php esc_html_e( 'Fetch Docs', 'admin-help-docs' ); ?></a>
                        <?php if ( Bootstrap::is_test_mode() ) : ?>
                            <a href="<?php echo esc_url( $website_url ); ?>/wp-json/admin-help-docs/v1/docs" class="helpdocs-button button-secondary" target="_blank"><?php esc_html_e( 'v1', 'admin-help-docs' ); ?></a>
                            <a href="<?php echo esc_url( $website_url ); ?>/wp-json/admin-help-docs/v2/docs" class="helpdocs-button button-secondary" target="_blank"><?php esc_html_e( 'v2', 'admin-help-docs' ); ?></a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php 
                $import_data = $website_url ? self::get_all_import_data( $website_url, $api_key ) : [];
                $docs        = $import_data[ 'docs' ] ?? [];
                $error       = $import_data[ 'error' ] ?? '';
                $api_version = $import_data[ 'version' ] ?? 'v2';
                $total_items = count( $docs );
                $version_class = $api_version === 'v1' && ! empty( $docs ) ? 'display:block;' : 'display:none;';
                ?>

                <div id="helpdocs_version_notice" class="helpdocs_warning_notice" style="<?php echo esc_attr( $version_class ); ?>"><p>
                    <?php echo esc_html__( 'Note: This remote site is using an older version of the plugin. Some newer features, like multiple locations per document, may be limited until the remote site is updated.', 'admin-help-docs' ); ?>
                </p></div>

                <div id="helpdocs_api_error" class="helpdocs_error_notice" style="<?php echo ( $error === 'unauthorized' ) ? '' : 'display:none;'; ?>">
                    <p><strong><?php esc_html_e( 'Connection Unauthorized:', 'admin-help-docs' ); ?></strong> <?php esc_html_e( 'The remote site requires a valid API Key. Please check your key and try again.', 'admin-help-docs' ); ?></p>
                </div>

                <div id="helpdocs_connection_error" class="helpdocs_error_notice" style="<?php echo ( $error === 'connection_failed' ) ? '' : 'display:none;'; ?>">
                    <p><?php esc_html_e( 'Could not connect to the remote site. Please check the URL.', 'admin-help-docs' ); ?></p>
                </div>

                <div id="helpdocs_remote_docs_wrapper" style="<?php echo empty( $website_url ) ? 'display:none;' : ''; ?>">
                    
                    <div id="helpdocs_tablenav_top" class="tablenav top">
                        <div class="alignleft actions">
                            <button type="button" class="button helpdocs-select-all-toggle" data-type="feed"><?php esc_html_e( 'Select All Auto Feeds', 'admin-help-docs' ); ?></button>
                            <button type="button" class="button helpdocs-select-all-toggle" data-type="toc"><?php esc_html_e( 'Select All TOCs', 'admin-help-docs' ); ?></button>
                            <label class="helpdocs-all-checkbox">
                                <input type="checkbox" name="helpdocs_all" id="helpdocs_all" value="1" <?php checked( $all_docs, '1' ); ?>> 
                                <strong><?php esc_html_e( 'Feed All Documents Automatically', 'admin-help-docs' ); ?></strong>
                            </label>
                            <label class="helpdocs-all-checkbox" id="helpdocs_all_tocs_container" style="<?php echo ( '1' === $all_docs ) ? 'display:inline-flex;' : 'display:none;'; ?>">
                                <input type="checkbox" name="helpdocs_all_tocs" id="helpdocs_all_tocs" value="1" <?php checked( $all_tocs, '1' ); ?>> 
                                <?php esc_html_e( 'Add All Main Docs to Dashboard Table of Contents (Must be Enabled in Settings)', 'admin-help-docs' ); ?>
                            </label>
                        </div>
                        <div class="tablenav-pages">
                            <span class="displaying-num">
                                <?php if ( $total_items ) echo esc_html( sprintf( _n( '%s item', '%s items', $total_items, 'admin-help-docs' ), number_format_i18n( $total_items ) ) ); ?>
                            </span>
                        </div>
                        <br class="clear">
                    </div>

                    <table id="helpdocs_imports_table" class="wp-list-table widefat fixed striped posts" data-import-id="<?php echo esc_attr( $import_id ); ?>">
                        <thead><?php echo wp_kses_post( $table_row_header ); ?></thead>
                        <tbody id="the-list">
                            <?php 
                            if ( ! empty( $docs ) ) {
                                $this->render_table_rows( $docs, $selected_docs, $selected_tocs );
                            }
                            ?>
                        </tbody>
                        <tfoot><?php echo wp_kses_post( $table_row_header ); ?></tfoot>
                    </table>

                    <div id="helpdocs_tablenav_bottom" class="tablenav bottom">
                        <div class="tablenav-pages">
                            <span class="displaying-num">
                                <?php if ( $total_items ) echo esc_html( sprintf( _n( '%s item', '%s items', $total_items, 'admin-help-docs' ), number_format_i18n( $total_items ) ) ); ?>
                            </span>
                        </div>
                        <br class="clear">
                    </div>
                </div>

                <div id="helpdocs_no_docs_found" style="<?php echo ( $website_url && empty( $docs ) ) ? '' : 'display:none;'; ?>">
                    <p><em><?php echo esc_html__( 'No documents found at the provided URL.', 'admin-help-docs' ); ?></em></p>
                </div>
            </form>
        </div>
        <?php
    } // End render_tab()


    /**
     * Render table rows for the import list
     *
     * @param array $docs          Array of document objects.
     * @param array $selected_docs IDs of already selected docs.
     * @param array $selected_tocs IDs of already selected TOCs.
     */
    private function render_table_rows( $docs, $selected_docs = [], $selected_tocs = [] ) {
        if ( empty( $docs ) ) {
            return;
        }

        foreach ( $docs as $doc ) { 
            $doc_id      = absint( $doc->ID );
            $is_imported = in_array( $doc_id, $selected_docs );
            $has_toc     = in_array( $doc_id, $selected_tocs );

            // Handle multi-location logic for remote docs
            $locations = isset( $doc->locations ) ? (array) $doc->locations : [];
            if ( empty( $locations ) && ! empty( $doc->site_location ) ) {
                $locations = [
                    [
                        'site_location' => $doc->site_location,
                        'page_location' => $doc->page_location ?? '',
                        'custom'        => $doc->custom ?? '',
                        'post_types'    => $doc->post_types ?? [],
                        'order'         => $doc->order ?? '',
                        'css_selector'  => $doc->css_selector ?? '',
                    ]
                ];
            }

            $can_have_toc = false;
            foreach ( $locations as $loc ) {
                $loc_array = (array) $loc;
                if ( isset( $loc_array[ 'site_location' ] ) && 'main' === base64_decode( $loc_array[ 'site_location' ] ) ) {
                    $can_have_toc = true;
                    break;
                }
            }
            ?>
            <tr data-doc-id="<?php echo esc_attr( $doc_id ); ?>">
                <td class="column-feed check-column">
                    <input type="checkbox" name="helpdocs_auto_feed[]" value="<?php echo esc_attr( $doc_id ); ?>" class="feed-checkbox" <?php checked( $is_imported ); ?>>
                </td>
                <td class="column-title title">
                    <strong><?php echo esc_html( $doc->title ); ?></strong>
                    <button type="button" class="toggle-row"><span class="screen-reader-text"><?php esc_html_e( 'Show more details', 'admin-help-docs' ); ?></span></button>
                </td>
                <td class="column-date"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $doc->publish_date ) ) ); ?></td>
                <td class="column-author"><?php echo esc_html( $doc->created_by ); ?></td>
                
                <td class="column-location">
                    <?php if ( empty( $locations ) ) : ?>
                        <span class="description"><?php esc_html_e( 'No locations set.', 'admin-help-docs' ); ?></span>
                    <?php else : ?>
                        <ul style="margin:0; padding:0; list-style:none;">
                            <?php foreach ( $locations as $loc ) {
                                $loc_array = (array) $loc;
                                $site_val  = $loc_array[ 'site_location' ] ?? '';
                                if ( empty( $site_val ) ) continue;

                                $label   = Helpers::get_admin_page_title_from_url( $site_val );
                                $details = [];

                                // 1. Page Location
                                if ( ! empty( $loc_array[ 'page_location' ] ) ) {
                                    $page_loc = ucwords( str_replace( '_', ' ', $loc_array[ 'page_location' ] ) );
                                    if ( $page_loc === 'Contextual' ) $page_loc .= ' Help Tab';
                                    $details[] = '<em>' . esc_html( $page_loc ) . '</em>';
                                }

                                // 2. Custom URL
                                if ( ! empty( $loc_array[ 'custom' ] ) ) {
                                    $details[] = '<code style="font-size:10px;">' . esc_html( $loc_array[ 'custom' ] ) . '</code>';
                                }

                                // 3. Post Types (Handled serialized remote data)
                                if ( ! empty( $loc_array[ 'post_types' ] ) ) {
                                    $pt_raw = $loc_array[ 'post_types' ];
                                    $pts = is_serialized( $pt_raw ) ? unserialize( $pt_raw ) : $pt_raw;
                                    $pts = is_array( $pts ) ? $pts : [ $pts ];
                                    
                                    if ( ! empty( $pts ) ) {
                                        $clean_pts = array_map( function( $pt ) {
                                            return ucwords( str_replace( [ '-', '_' ], ' ', $pt ) );
                                        }, $pts );
                                        $details[] = 'Types: ' . esc_html( implode( ', ', $clean_pts ) );
                                    }
                                }

                                echo '<li style="margin-bottom: 8px; border-bottom: 1px solid #eee; padding-bottom: 4px;">';
                                echo '<strong>' . wp_kses_post( $label ) . '</strong>';
                                if ( ! empty( $details ) ) {
                                    echo '<br><span class="description" style="font-size:11px;">' . wp_kses_post( implode( ' | ', $details ) ) . '</span>';
                                }
                                echo '</li>';
                            } ?>
                        </ul>
                    <?php endif; ?>
                </td>

                <td class="column-toc check-column">
                    <?php if ( $can_have_toc ) : ?>
                        <input type="checkbox" name="helpdocs_tocs[]" value="<?php echo esc_attr( $doc_id ); ?>" class="toc-checkbox" <?php checked( $has_toc ); ?>>
                    <?php else : ?>
                        <span class="dash">&mdash;</span>
                    <?php endif; ?>
                </td>
                <td class="column-action">
                    <button type="button" class="button button-secondary helpdocs-clone-individual" data-id="<?php echo esc_attr( $doc_id ); ?>">
                        <?php esc_html_e( 'Import Now', 'admin-help-docs' ); ?>
                    </button>
                </td>
            </tr>
            <?php
        }
    } // End render_table_rows()


    /**
     * Check if the current request is a browser "Hard Refresh"
     * * @return bool
     */
    private static function is_hard_refresh() {
        $cache_control = filter_input( INPUT_SERVER, 'HTTP_CACHE_CONTROL', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $pragma        = filter_input( INPUT_SERVER, 'HTTP_PRAGMA', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

        $is_no_cache = ( 'no-cache' === $cache_control || 'no-cache' === $pragma );
        
        $is_max_age = ( 'max-age=0' === $cache_control );

        return ( $is_no_cache || $is_max_age );
    } // End is_hard_refresh()


    /**
     * Get all import data for a given website
     *
     * @param string $website The website to get imports for
     * @return array Array of import objects
     */
    public static function get_all_import_data( $website_url, $api_key = '' ) {
        $import_id = isset( $_GET[ 'id' ] ) ? absint( $_GET[ 'id' ] ) : 0; // phpcs:ignore

        if ( empty( $api_key ) ) {
            $api_key = isset( $_POST[ 'helpdocs_api_key' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'helpdocs_api_key' ] ) ) : get_post_meta( $import_id, 'helpdocs_api_key', true ); // phpcs:ignore
        }

        $cache_key = 'helpdocs_remote_' . md5( $website_url . $api_key );

        if ( self::is_hard_refresh() ) {
            delete_transient( $cache_key );
        }

        $cached = get_transient( $cache_key );

        if ( false !== $cached ) {
            return $cached;
        }

        $v2_url = API::api_path();
        $v2_url = str_replace( home_url(), $website_url, $v2_url );

        $version_found = 'v2';
        $response      = self::fetch_remote_json( $v2_url, $api_key );
        $status        = wp_remote_retrieve_response_code( $response );

        // If V2 is 404, try V1
        if ( ! is_wp_error( $response ) && $status === 404 ) {
            $version_found = 'v1';
            $v1_url        = str_replace( '/v2/', '/v1/', $v2_url );
            $response      = self::fetch_remote_json( $v1_url, $api_key );
            $status        = wp_remote_retrieve_response_code( $response );
        }

        if ( is_wp_error( $response ) ) {
            return [ 'error' => 'connection_failed' ];
        }

        if ( $status === 401 ) {
            return [ 'error' => 'unauthorized' ];
        }

        $docs = json_decode( wp_remote_retrieve_body( $response ) );

        if ( ! empty( $docs ) && is_array( $docs ) ) {
            $data = [
                'version' => $version_found,
                'docs'    => $docs,
            ];
            set_transient( $cache_key, $data, apply_filters( 'helpdocs_import_cache_duration', 12 * HOUR_IN_SECONDS, $website_url ) );
            return $data;
        }

        return [ 'docs' => [] ];
    } // End get_all_import_data()


    /**
     * Helper to keep the remote request clean
     */
    private static function fetch_remote_json( $url, $api_key = '' ) {
        $args = [
            'timeout'     => 20,
            'httpversion' => '1.1',
            'user-agent'  => 'AdminHelpDocs Importer; ' . home_url(),
            'headers'     => [
                'Accept' => 'application/json',
            ],
        ];

        if ( ! empty( $api_key ) ) {
            $args[ 'headers' ][ 'X-HelpDocs-API-Key' ] = $api_key;
        }

        return wp_remote_get( $url, $args );
    } // End fetch_remote_json()


    /**
     * Save import settings
     */
    public function save_import_settings() {
        if ( Menu::get_current_page() !== Bootstrap::textdomain() || Menu::get_current_tab() !== 'import' ) {
            return;
        }

        if ( ! isset( $_POST[ 'action' ] ) || 'helpdocs_import_docs' !== sanitize_text_field( wp_unslash( $_POST[ 'action' ] ) ) ) {
            return;
        }

        if ( ! isset( $_POST[ 'helpdocs_save_import_nonce' ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'helpdocs_save_import_nonce' ] ) ), 'helpdocs_save_import_nonce' ) ) {
            return;
        }

        if ( ! Helpers::user_can_edit() ) {
            return;
        }

        $website_url = isset( $_POST[ 'helpdocs_website_url' ] ) ? sanitize_url( wp_unslash( $_POST[ 'helpdocs_website_url' ] ) ) : '';
        if ( empty( $website_url ) ) {
            return;
        }

        $import_id   = isset( $_POST[ 'id' ] ) ? absint( $_POST[ 'id' ] ) : 0;
        $is_active   = isset( $_POST[ 'helpdocs_import_active' ] );
        $post_status = $is_active ? 'publish' : 'draft';
        $import_title = isset( $_POST[ 'helpdocs_import_title' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'helpdocs_import_title' ] ) ) : '';

        if ( empty( $import_title ) ) {
            $import_title = ucfirst( str_ireplace( [ 'http://', 'https://', 'www.' ], '', $website_url ) );
            $import_title = untrailingslashit( $import_title );
        }

        $original_title = $import_title;
        $count = 1;
        while ( $this->title_exists( $import_title, $import_id ) ) {
            $count++;
            $import_title = ( $original_title ) . ' (' . $count . ')';
        }

        $post_data = [
            'post_type'   => Imports::$post_type,
            'post_title'  => $import_title,
            'post_status' => $post_status,
        ];

        if ( ! $import_id ) {
            $import_id = wp_insert_post( $post_data );
        } else {
            $post_data[ 'ID' ] = $import_id;
            wp_update_post( $post_data );

            // MIGRATION: Prune the old legacy key now that we're using the post status
            delete_post_meta( $import_id, 'helpdocs_enabled' );
        }

        if ( is_wp_error( $import_id ) || ! $import_id ) {
            return;
        }

        $api_key = isset( $_POST[ 'helpdocs_api_key' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'helpdocs_api_key' ] ) ) : '';

        $all_docs = isset( $_POST[ 'helpdocs_all' ] ) ? '1' : '0';
        $all_tocs = isset( $_POST[ 'helpdocs_all_tocs' ] ) ? '1' : '0';

        $selected_docs = isset( $_POST[ 'helpdocs_auto_feed' ] ) ? array_map( 'absint', (array) wp_unslash( $_POST[ 'helpdocs_auto_feed' ] ) ) : [];
        $selected_tocs = isset( $_POST[ 'helpdocs_tocs' ] ) ? array_map( 'absint', (array) wp_unslash( $_POST[ 'helpdocs_tocs' ] ) ) : [];

        // Save Metadata
        update_post_meta( $import_id, 'helpdocs_api_key', $api_key );
        update_post_meta( $import_id, 'helpdocs_url', $website_url );
        update_post_meta( $import_id, 'helpdocs_all', $all_docs );
        update_post_meta( $import_id, 'helpdocs_all_tocs', $all_tocs );
        update_post_meta( $import_id, 'helpdocs_docs', $selected_docs );
        update_post_meta( $import_id, 'helpdocs_tocs', $selected_tocs );

        // Redirect with ID and Success Message
        $redirect_url = add_query_arg( [
            'id'             => $import_id,
            'import-updated' => 'true',
        ], wp_get_referer() );

        wp_safe_redirect( $redirect_url );
        exit;
    } // End save_import_settings()


    /**
     * Helper to check if title already exists for this CPT
     */
    private function title_exists( $title, $post_id ) {
        global $wpdb;
        return $wpdb->get_var( $wpdb->prepare( // phpcs:ignore
            "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type = %s AND ID != %d LIMIT 1", 
            $title, 
            Imports::$post_type, 
            $post_id 
        ) );
    } // End title_exists()


    /**
     * AJAX handler to fetch remote documents
     */
    public function ajax_fetch_remote_docs() {
        check_ajax_referer( 'helpdocs_import_fetch_nonce', 'nonce' );

        if ( ! Helpers::user_can_edit() ) {
            wp_send_json_error( [ 'message' => __( 'You do not have permission to fetch remote documents.', 'admin-help-docs' ) ] );
        }

        $url     = isset( $_POST[ 'url' ] ) ? sanitize_url( wp_unslash( $_POST[ 'url' ] ) ) : '';
        $api_key = isset( $_POST[ 'api_key' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'api_key' ] ) ) : '';

        if ( empty( $url ) ) {
            wp_send_json_error( [ 'message' => __( 'Please enter a valid URL.', 'admin-help-docs' ) ] );
        }

        // Delete transient using the new cache key logic (url + key)
        delete_transient( 'helpdocs_remote_' . md5( $url . $api_key ) );

        $import_data = self::get_all_import_data( $url, $api_key );
        $docs        = $import_data[ 'docs' ] ?? [];
        $version     = $import_data[ 'version' ] ?? 'v2';
        $error       = $import_data[ 'error' ] ?? '';

        // If we have a specific error (unauthorized or connection_failed)
        if ( ! empty( $error ) ) {
            wp_send_json_success( [
                'error'   => $error,
                'docs'    => [],
                'count'   => 0,
                'version' => $version
            ] );
        }

        if ( empty( $docs ) ) {
            wp_send_json_error( [ 'message' => __( 'No documents found at this URL.', 'admin-help-docs' ) ] );
        }

        ob_start();
        $this->render_table_rows( $docs );
        $html = ob_get_clean();

        wp_send_json_success( [
            'html'    => $html,
            'count'   => count( $docs ),
            'version' => $version,
            'error'   => ''
        ] );
    } // End ajax_fetch_remote_docs()


    /**
     * AJAX handler to import an individual document
     */
    public function ajax_import_individual_doc() {
        check_ajax_referer( 'helpdocs_import_clone_nonce', 'nonce' );
        if ( ! Helpers::user_can_edit() ) {
            wp_send_json_error( __( 'You do not have permission to import documents.', 'admin-help-docs' ) );
        }

        $doc_id    = isset( $_POST[ 'doc_id' ] ) ? absint( $_POST[ 'doc_id' ] ) : 0;
        $import_id = isset( $_POST[ 'import_id' ] ) ? absint( $_POST[ 'import_id' ] ) : 0;
        $url       = isset( $_POST[ 'website_url' ] ) ? sanitize_url( wp_unslash( $_POST[ 'website_url' ] ) ) : '';
        $api_key   = isset( $_POST[ 'api_key' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'api_key' ] ) ) : '';

        if ( ! $doc_id || ! $url ) {
            wp_send_json_error( __( 'Invalid request data.', 'admin-help-docs' ) );
        }

        $cache_key = 'helpdocs_remote_' . md5( $url . $api_key );
        delete_transient( $cache_key );

        $import_data = self::get_all_import_data( $url, $api_key );
        if ( ! empty( $import_data[ 'error' ] ) ) {
            wp_send_json_error( sprintf( __( 'Remote error: %s', 'admin-help-docs' ), $import_data[ 'error' ] ) );
        }

        // Fetch all remote data and find our specific doc
        $remote_docs = $import_data[ 'docs' ] ?? [];
        $raw_target  = null;

        foreach ( (array) $remote_docs as $doc ) {
            if ( isset( $doc->ID ) && absint( $doc->ID ) === $doc_id ) {
                $raw_target = $doc;
                break;
            }
        }

        if ( ! $raw_target ) {
            wp_send_json_error( __( 'Could not find the document in the remote feed.', 'admin-help-docs' ) );
        }

        // Convert the entire stdClass object and all nested objects into associative arrays.
        $target_doc = json_decode( json_encode( $raw_target ), true );

        // 1. Prepare and Insert the Post
        $new_post_id = wp_insert_post( [
            'post_title'   => sanitize_text_field( $target_doc[ 'title' ] ),
            'post_content' => wp_kses_post( $target_doc[ 'content' ] ),
            'post_excerpt' => sanitize_text_field( $target_doc[ 'desc' ] ?? '' ),
            'post_status'  => 'publish',
            'post_type'    => HelpDocs::$post_type,
        ] );

        if ( is_wp_error( $new_post_id ) ) {
            wp_send_json_error( $new_post_id->get_error_message() );
        }

        // 2. Handle Taxonomies
        if ( ! empty( $target_doc[ 'taxonomies' ] ) && is_array( $target_doc[ 'taxonomies' ] ) ) {
            foreach ( $target_doc[ 'taxonomies' ] as $taxonomy => $terms ) {
                if ( ! taxonomy_exists( $taxonomy ) || empty( $terms ) ) {
                    continue;
                }

                $term_ids = [];
                foreach ( $terms as $term_data ) {
                    $term = get_term_by( 'slug', $term_data[ 'slug' ], $taxonomy );
                    if ( ! $term ) {
                        $new_term = wp_insert_term( $term_data[ 'name' ], $taxonomy, [ 'slug' => $term_data[ 'slug' ] ] );
                        if ( ! is_wp_error( $new_term ) ) {
                            $term_ids[] = (int) $new_term[ 'term_id' ];
                        }
                    } else {
                        $term_ids[] = (int) $term->term_id;
                    }
                }
                wp_set_object_terms( $new_post_id, $term_ids, $taxonomy );
            }
        }

        // 3. Normalize Locations
        $all_site_locations = HelpDocs::site_locations();
        $final_locations    = [];
        $source_locations   = [];

        if ( ! empty( $target_doc[ 'locations' ] ) && is_array( $target_doc[ 'locations' ] ) ) {
            $source_locations = $target_doc[ 'locations' ];
        } elseif ( ! empty( $target_doc[ 'site_location' ] ) ) {
            $source_locations = [
                [
                    'site_location' => $target_doc[ 'site_location' ],
                    'page_location' => $target_doc[ 'page_location' ] ?? '',
                    'custom'        => $target_doc[ 'custom' ] ?? '',
                    'post_types'    => $target_doc[ 'post_types' ] ?? [],
                    'order'         => $target_doc[ 'order' ] ?? 0,
                    'toc'           => $target_doc[ 'toc' ] ?? false,
                    'css_selector'  => $target_doc[ 'css_selector' ] ?? '',
                    'addt_params'   => $target_doc[ 'addt_params' ] ?? false,
                ]
            ];
        }

        foreach ( $source_locations as $loc ) {
            // $loc is now guaranteed to be an array because of our json_decode(..., true)
            $site_key_encoded = sanitize_text_field( $loc[ 'site_location' ] ?? '' );
            $site_key_decoded = base64_decode( $site_key_encoded );

            if ( isset( $all_site_locations[ $site_key_decoded ] ) ) {
                $clean          = [ 'site_location' => $site_key_encoded ];
                $allowed_fields = $all_site_locations[ $site_key_decoded ][ 'fields' ] ?? [];

                foreach ( $allowed_fields as $field_rule ) {
                    $field_parts = explode( ':', $field_rule );
                    $field_name  = ( count( $field_parts ) > 1 ) ? end( $field_parts ) : $field_rule;
                    $val         = $loc[ $field_name ] ?? '';

                    switch ( $field_name ) {
                        case 'order': 
                            $clean[ $field_name ] = intval( $val ); 
                            break;
                        case 'toc':
                        case 'addt_params': 
                            $clean[ $field_name ] = ! empty( $val ); 
                            break;
                        case 'post_types':
                            // Ensure this is saved as a clean array of keys
                            $pts = is_serialized( $val ) ? unserialize( $val ) : (array) $val;
                            $clean[ $field_name ] = array_values( array_map( 'sanitize_key', (array) $pts ) );
                            break;
                        case 'custom': 
                            $clean[ $field_name ] = esc_url_raw( $val ); 
                            break;
                        case 'page_location': 
                            $clean[ $field_name ] = sanitize_key( $val ); 
                            break;
                        default: 
                            $clean[ $field_name ] = sanitize_text_field( $val ); 
                            break;
                    }
                }
                $final_locations[] = $clean;
            }
        }

        // 4. Update Meta
        update_post_meta( $new_post_id, 'helpdocs_locations', $final_locations );
        update_post_meta( $new_post_id, 'helpdocs_view_roles', array_map( 'sanitize_key', (array) ( $target_doc->view_roles ?? [] ) ) );
        update_post_meta( $new_post_id, 'helpdocs_imported_doc_id', $doc_id );
        update_post_meta( $new_post_id, 'helpdocs_import_id', $import_id );
        update_post_meta( $new_post_id, 'helpdocs_imported_from', $url );
        update_post_meta( $new_post_id, 'helpdocs_imported_by', get_current_user_id() );
        
        // Compatibility meta for Classic Editor
        if ( ! empty( $target_doc->editor_type ) ) {
            update_post_meta( $new_post_id, 'classic-editor-remember', sanitize_text_field( $target_doc->editor_type ) );
        }

        // Support for extra fields added via 'helpdocs_api_doc_object_out' filter
        $standard_keys = [ 'ID', 'title', 'created_by', 'publish_date', 'modified_date', 'modified_by', 'desc', 'content', 'taxonomies', 'site_location', 'page_location', 'custom', 'post_types', 'order', 'css_selector', 'addt_params', 'view_roles', 'editor_type' ];
        
        foreach ( $target_doc as $key => $value ) {
            if ( ! in_array( $key, $standard_keys ) ) {
                update_post_meta( $new_post_id, 'helpdocs_' . sanitize_key( $key ), is_scalar( $value ) ? sanitize_text_field( $value ) : $value );
            }
        }

        Helpers::flush_location_cache();

        wp_send_json_success( __( 'Document imported successfully.', 'admin-help-docs' ) );
    } // End ajax_import_individual_doc()

    
    /**
     * Prevent cloning and unserializing
     */
    public function __clone() {}
    public function __wakeup() {}

}


ImportEditor::instance();