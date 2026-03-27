<?php
/**
 * Settings Tab Loader
 */

namespace PluginRx\AdminHelpDocs;

if ( ! defined( 'ABSPATH' ) ) exit;

class Settings {


    /**
     * Returns all settings boxes.
     */
    public static function setting_boxes() : array {
        $boxes = [
            'interface'      => __( 'Interface', 'admin-help-docs' ),
            'content_output' => __( 'Content & Output', 'admin-help-docs' ),
            'access_control' => __( 'Access Control', 'admin-help-docs' ),
            'advanced'       => __( 'Advanced', 'admin-help-docs' ),
        ];

        return apply_filters( 'helpdocs_settings_boxes', $boxes );
    } // End setting_boxes()


    /**
     * Returns all settings fields.
     */
    public static function setting_fields( $defaults_only = false ) : array {
        $themes = [];
        foreach ( Colors::themes() as $key => $theme ) {
            $themes[ $key ] = $theme[ 'label' ];
        }
        $themes[ 'custom' ] = __( 'Custom (Use the color pickers below to customize)', 'admin-help-docs' );

        $top_placements = [];
        foreach ( self::top_placements() as $key => $label ) {
            $top_placements[] = [
                'value' => $key,
                'label' => $label,
            ];
        }

        $legacy_cap   = get_option( 'helpdocs_user_view_cap' );
        $current_type = get_option( 'helpdocs_user_view_type' );
        $view_default = ( false !== $legacy_cap && false === $current_type ) ? 'capability' : 'role';

        $fields = [
            // Interface
            [
                'name'        => 'menu_title',
                'label'       => __( 'Menu Title', 'admin-help-docs' ),
                'type'        => 'text',
                'sanitize'    => 'sanitize_text_field',
                'box'         => 'interface',
                'default'     => 'Help Docs',
            ],
            [
                'name'        => 'dashicon',
                'label'       => __( 'Menu Icon', 'admin-help-docs' ) . ' — <a id="view-dashicons-link" href="https://developer.wordpress.org/resource/dashicons/#editor-help" target="_blank">View Dashicons <span class="dashicons dashicons-external"></span></a>',
                'type'        => 'select',
                'choices'     => Helpers::get_dashicons(),
                'sanitize'    => 'sanitize_text_field',
                'box'         => 'interface',
                'default'     => 'editor-help',
            ],
            [
                'name'        => 'menu_position',
                'label'       => __( 'Menu Position', 'admin-help-docs' ),
                'desc'        => __( '1 = Above Dashboard, 2 = Under Dashboard, 999 = Bottom, etc.', 'admin-help-docs' ),
                'type'        => 'number',
                'sanitize'    => 'absint',
                'box'         => 'interface',
                'default'     => Menu::$default_menu_position,
            ],
            [
                'name'        => 'page_title',
                'label'       => __( 'Page Title', 'admin-help-docs' ),
                'type'        => 'text',
                'sanitize'    => 'sanitize_text_field',
                'box'         => 'interface',
                'default'     => Bootstrap::name(),
            ],
            [
                'name'        => 'logo',
                'label'       => __( 'Page Logo', 'admin-help-docs' ),
                'desc'        => __( 'Preferred size: 100x100 pixels. Accepted formats: jpg | jpeg | png | webp', 'admin-help-docs' ),
                'type'        => 'text',
                'sanitize'    => 'sanitize_text_field',
                'box'         => 'interface',
                'default'     => Helpers::get_default_logo_url(),
            ],
            [
                'name'        => 'doc_logo',
                'label'       => __( 'Help Doc Logo', 'admin-help-docs' ),
                'desc'        => __( 'You can also change the logo on all of the help docs, which may differ from the page logo if the background is contrasted. For example a light logo on dark background at the top of the page, and a dark logo on a light background on the help docs themselves. You can also disable the help doc logo from the Content & Output settings on the right. The preferred size of the image is 100x100 pixels. Accepted formats: jpg | jpeg | png | webp', 'admin-help-docs' ),
                'type'        => 'text',
                'sanitize'    => 'sanitize_text_field',
                'box'         => 'interface',
                'default'     => Helpers::get_default_logo_url(),
            ],
            [
                'name'        => 'themes',
                'label'       => __( 'Color Theme', 'admin-help-docs' ),
                'type'        => 'select',
                'choices'     => $themes,
                'sanitize'    => 'sanitize_text_field',
                'box'         => 'interface',
                'default'     => 'custom',
            ],
            [
                'name'        => 'contact_form',
                'label'       => __( 'Enable Support Contact Form', 'admin-help-docs' ),
                'desc'          => sprintf(
                    /* translators: %s is the current site admin email address. */
                    __( 'Adds a simple contact form within the Help Docs menu, allowing clients to reach out for support directly. Emails will be sent from %s. This feature utilizes wp_mail() for delivery. To ensure reliable inbox placement, using a dedicated provider like WP Mail SMTP (with Brevo) is advised, along with WP Mail Logging for tracking sent messages.', 'admin-help-docs' ),
                    '<strong>' . esc_html( get_option( 'admin_email' ) ) . '</strong>'
                ),
                'type'        => 'checkbox',
                'sanitize'    => 'sanitize_checkbox',
                'box'         => 'interface',
                'default'     => false,
                'has_condition' => true,
            ],
            [
                'name'        => 'contact_name',
                'label'       => __( 'Support Contact Name', 'admin-help-docs' ),
                'desc'        => __( 'Enter the name that clients can use to reach you. This will be displayed on the contact form page. Can be a business or personal name.', 'admin-help-docs' ),
                'type'        => 'text',
                'sanitize'    => 'sanitize_text_field',
                'box'         => 'interface',
                'default'     => '',
                'condition'   => [ 'contact_form' => true ],
            ],
            [
                'name'        => 'contact_emails',
                'label'       => __( 'Support Contact Email(s)', 'admin-help-docs' ),
                'desc'        => __( 'Enter the email address(es) that will receive messages from the contact form. Separate multiple emails with commas.', 'admin-help-docs' ),
                'type'        => 'text',
                'sanitize'    => 'sanitize_text_field',
                'box'         => 'interface',
                'default'     => implode( ', ', Helpers::get_all_admin_emails() ),
                'condition'   => [ 'contact_form' => true ],
            ],
            [
                'name'        => 'contact_phone',
                'label'       => __( 'Support Contact Phone Number', 'admin-help-docs' ),
                'desc'        => __( 'Enter a phone number that clients can use to reach you. This will be displayed on the contact form page.', 'admin-help-docs' ),
                'type'        => 'text',
                'sanitize'    => 'sanitize_text_field',
                'box'         => 'interface',
                'default'     => '',
                'condition'   => [ 'contact_form' => true ],
            ],
            [
                'name'        => 'admin_bar',
                'label'       => __( 'Enable Admin Bar Menu for Backend', 'admin-help-docs' ),
                'type'        => 'checkbox',
                'sanitize'    => 'sanitize_checkbox',
                'box'         => 'interface',
                'default'     => false,
            ],
            [
                'name'        => 'admin_bar_frontend',
                'label'       => __( 'Enable Admin Bar Menu for Frontend', 'admin-help-docs' ),
                'type'        => 'checkbox',
                'sanitize'    => 'sanitize_checkbox',
                'box'         => 'interface',
                'default'     => false,
            ],
            [
                'name'        => 'replace_dashboard',
                'label'       => __( 'Replace WordPress Dashboard with a Help Doc', 'admin-help-docs' ),
                'desc'        => __( 'Replace the default WordPress dashboard with a custom help docs page. To add docs, set their locations to "WordPress Dashboard (Replaces Dashboard Entirely)". Warning: Replacing the WordPress dashboard entirely may affect other plugins and functionality. No other widgets or dashboard elements will be displayed.', 'admin-help-docs' ),
                'type'        => 'checkbox',
                'sanitize'    => 'sanitize_checkbox',
                'box'         => 'interface',
                'default'     => false,
            ],
            [
                'name'        => 'dashboard_toc',
                'label'       => __( 'Enable Dashboard Table of Contents Widget', 'admin-help-docs' ),
                'desc'        => __( 'Adds a dashboard widget with a table of contents for the docs on the Main Documentation Page.', 'admin-help-docs' ),
                'type'        => 'checkbox',
                'sanitize'    => 'sanitize_checkbox',
                'box'         => 'interface',
                'default'     => false,
            ],
            [
                'name'        => 'gutenberg_editor',
                'label'       => __( 'Use Gutenberg Editor', 'admin-help-docs' ),
                'desc'        => __( 'Adds support for the Gutenberg editor for the documentation. Default is the classic editor.', 'admin-help-docs' ),
                'type'        => 'checkbox',
                'sanitize'    => 'sanitize_checkbox',
                'box'         => 'interface',
                'default'     => false,
            ],

            // Content & Output
            [
                'name'        => 'admin_bar_include_content',
                'label'       => __( 'Include Doc Content in Admin Bar', 'admin-help-docs' ),
                'desc'        => __( 'Includes a snippet of the document content in the admin bar.', 'admin-help-docs' ),
                'type'        => 'checkbox',
                'sanitize'    => 'sanitize_checkbox',
                'box'         => 'content_output',
                'default'     => false,
            ],
            [
                'name'        => 'default_doc',
                'label'       => __( 'Default Document on Main Docs Page', 'admin-help-docs' ),
                'type'        => 'select',
                'choices'     => Helpers::get_main_helpdoc_options(),
                'sanitize'    => 'sanitize_text_field',
                'box'         => 'content_output',
            ],
            [
                'name'        => 'hide_doc_meta',
                'label'       => __( 'Hide Document Meta on Main Docs Page', 'admin-help-docs' ),
                'desc'        => __( 'Includes created and last modified dates and authors.', 'admin-help-docs' ),
                'type'        => 'checkbox',
                'sanitize'    => 'sanitize_checkbox',
                'box'         => 'content_output',
                'default'     => false,
            ],
            [
                'name'        => 'auto_htoc',
                'label'       => __( 'Auto-Generate Table of Contents on Main Docs Page', 'admin-help-docs' ),
                'desc'        => __( 'Automatically generate a table of contents from headings (H2–H6) at the top of each documentation page.', 'admin-help-docs' ),
                'type'        => 'checkbox',
                'sanitize'    => 'sanitize_checkbox',
                'box'         => 'content_output',
                'default'     => false,
            ],
            [
                'name'        => 'curly_quotes',
                'label'       => __( 'Disable Curly Quotes on Main Docs Page', 'admin-help-docs' ),
                'desc'        => __( 'WP automatically converts straight quotes (") to curly quotes (”), which makes sharing code difficult.', 'admin-help-docs' ),
                'type'        => 'checkbox',
                'sanitize'    => 'sanitize_checkbox',
                'box'         => 'content_output',
                'default'     => false,
            ],
            [
                'name'        => 'enqueue_frontend_styles',
                'label'       => __( 'Use Frontend Stylesheets on Main Docs Page', 'admin-help-docs' ),
                'desc'        => __( 'Adds support for your frontend styles in the backend by enqueueing them while on the Main Docs Page.', 'admin-help-docs' ),
                'type'        => 'checkbox',
                'sanitize'    => 'sanitize_checkbox',
                'box'         => 'content_output',
                'default'     => false,
            ],
            [
                'name'        => 'main_docs_css',
                'label'       => __( 'Main Docs Page CSS', 'admin-help-docs' ),
                'desc'        => __( 'Add CSS here to style the Main Documentation Page specifically. You must use CSS selectors.', 'admin-help-docs' ),
                'type'        => 'textarea',
                'sanitize'    => 'sanitize_custom_css',
                'box'         => 'content_output',
                'default'     => '',
            ],
            [
                'name'        => 'include_logo_on_docs',
                'label'       => __( 'Include Logo on Help Docs', 'admin-help-docs' ),
                'desc'        => __( 'Includes the site logo on individual help docs that are not on the Main Docs Page.', 'admin-help-docs' ),
                'type'        => 'checkbox',
                'sanitize'    => 'sanitize_checkbox',
                'box'         => 'content_output',
                'default'     => true,
            ],
            [
                'name'        => 'top_location_type',
                'label'       => __( 'Top Location Placement', 'admin-help-docs' ),
                'type'        => 'select',
                'choices'     => $top_placements,
                'sanitize'    => 'sanitize_key',
                'box'         => 'content_output',
                'default'     => 'admin_notices',
            ],
            [
                'name'        => 'footer_left',
                'label'       => __( 'Left Footer Text', 'admin-help-docs' ),
                'desc'        => __( 'Supports HTML. Default: "Thank you for creating with WordPress."', 'admin-help-docs' ),
                'type'        => 'textarea',
                'sanitize'    => 'wp_kses_post',
                'box'         => 'content_output',
                'default'     => '<span id="footer-thankyou">Thank you for creating with <a href="https://wordpress.org/">WordPress</a>.</span>',
            ],
            [
                'name'        => 'footer_right',
                'label'       => __( 'Right Footer Text', 'admin-help-docs' ),
                'desc'        => __( 'Use <code>{version}</code> to display the current WordPress version', 'admin-help-docs' ),
                'type'        => 'textarea',
                'sanitize'    => 'wp_kses_post',
                'box'         => 'content_output',
                'default'     => 'Version {version}',
            ],

            // Access & Permissions
            [
                'name'        => 'api',
                'label'       => __( 'Allow Public by Default', 'admin-help-docs' ),
                'type'        => 'checkbox',
                'sanitize'    => 'sanitize_checkbox',
                'box'         => 'access_control',
                'default'     => false,
            ],
            [
                'name'        => 'api_key',
                'label'       => __( 'Public Access API Key (Optional)', 'admin-help-docs' ),
                'desc'        => __( 'If you enable "Allow Public" above or on individual docs, you can optionally require an API key for access. This adds a layer of security by ensuring that only users with the key can import your documentation. To use, generate a key here and enter it into the import on your other site where you are importing the docs. Leave empty to allow public access without a key.', 'admin-help-docs' ),
                'type'        => 'api_key',
                'sanitize'    => 'sanitize_text_field',
                'box'         => 'access_control',
                'default'     => false,
            ],
            [
                'name'        => 'user_view_type',
                'label'       => __( 'Requirement Type to View Docs', 'admin-help-docs' ),
                'type'        => 'select',
                'choices'     => [
                    'capability' => __( 'Capability', 'admin-help-docs' ),
                    'role'       => __( 'Role', 'admin-help-docs' ),
                ],
                'sanitize'    => 'sanitize_text_field',
                'box'         => 'access_control',
                'default'     => $view_default,
                'has_condition' => true,
            ],
            [
                'name'        => 'user_view_cap',
                'label'       => __( 'Default Capability Required to View Docs', 'admin-help-docs' ) . ' — <a href="https://wordpress.org/documentation/article/roles-and-capabilities/" target="_blank">View a list of capabilities. <span class="dashicons dashicons-external"></span></a>',
                'desc'        => __( 'Use <code>manage_options</code> for admins only. You can also override this setting on a per-document basis.', 'admin-help-docs' ),
                'type'        => 'text',
                'sanitize'    => 'sanitize_text_field',
                'box'         => 'access_control',
                'default'     => 'manage_options',
                'condition'   => [ 'user_view_type' => 'capability' ],
            ],
            [
                'name'        => 'view_roles',
                'label'       => __( 'Additional Default Roles Required to View Docs', 'admin-help-docs' ),
                'desc'        => __( 'Admins can view all docs regardless of this setting. You can also override this setting on a per-document basis.', 'admin-help-docs' ),
                'type'        => 'checkboxes',
                'choices'     => Helpers::get_role_options(),
                'sanitize'    => 'sanitize_checkboxes',
                'box'         => 'access_control',
                'condition'   => [ 'user_view_type' => 'role' ],
            ],
            [
                'name'        => 'user_edit_type',
                'label'       => __( 'Requirement Type to Edit Docs', 'admin-help-docs' ),
                'type'        => 'select',
                'choices'     => [
                    'capability' => __( 'Capability', 'admin-help-docs' ),
                    'role'       => __( 'Role', 'admin-help-docs' ),
                ],
                'sanitize'    => 'sanitize_text_field',
                'box'         => 'access_control',
                'default'     => 'role',
                'has_condition' => true,
            ],
            [
                'name'        => 'user_edit_cap',
                'label'       => __( 'Capability Required to Edit Docs', 'admin-help-docs' ) . ' — <a href="https://wordpress.org/documentation/article/roles-and-capabilities/" target="_blank">View a list of capabilities. <span class="dashicons dashicons-external"></span></a>',
                'desc'        => __( 'Use <code>manage_options</code> for admins only.', 'admin-help-docs' ),
                'type'        => 'text',
                'sanitize'    => 'sanitize_text_field',
                'box'         => 'access_control',
                'default'     => 'manage_options',
                'condition'   => [ 'user_edit_type' => 'capability' ],
            ],
            [
                'name'        => 'edit_roles',
                'label'       => __( 'Additional Roles Required to Edit Docs', 'admin-help-docs' ),
                'desc'        => __( 'Admins can edit all docs regardless of this setting.', 'admin-help-docs' ),
                'type'        => 'checkboxes',
                'choices'     => Helpers::get_role_options(),
                'sanitize'    => 'sanitize_checkboxes',
                'box'         => 'access_control',
                'condition'   => [ 'user_edit_type' => 'role' ],
            ],

            // Advanced
            [
                'name'        => 'remove_on_uninstall',
                'label'       => __( 'Remove All Plugin Data on Uninstall', 'admin-help-docs' ),
                'desc'        => __( 'Deletes all plugin settings and documentation permanently when the plugin is deleted.', 'admin-help-docs' ),
                'type'        => 'checkbox',
                'sanitize'    => 'sanitize_checkbox',
                'box'         => 'advanced',
                'default'     => false,
            ],
            [
                'name'        => 'flush_cache',
                'label'       => __( 'System Cache Management', 'admin-help-docs' ),
                'type'        => 'html',
                'box'         => 'advanced',
                'content'     => '<button type="button" id="helpdocs-flush-cache" class="button helpdocs-button">' . __( 'Clear Stored Data', 'admin-help-docs' ) . '</button>',
            ],
            [
                'name'        => 'upload_download_colors',
                'label'       => __( 'Upload/Download Colors', 'admin-help-docs' ),
                'desc'        => __( 'Export your current color settings as a JSON file for backup or transfer to another site. You can also import color settings from a JSON file exported from this plugin. Note: Importing settings will overwrite your current color settings.', 'admin-help-docs' ),
                'type'        => 'html',
                'box'         => 'advanced',
                'content'     => '<button type="button" id="helpdocs-download-colors-btn" class="helpdocs-button">' . __( 'Download Colors', 'admin-help-docs' ) . '</button> <div id="helpdocs-upload-colors-button"><label for="helpdocs-upload-colors"><span class="helpdocs-button">' . __( 'Upload Colors', 'admin-help-docs' ) . '</span></label><input type="file" id="helpdocs-upload-colors" name="helpdocs-upload-colors" accept=".json" style="display:none;"></div> <div id="helpdocs-upload-colors-filename"></div>',
            ],
            [
                'name'        => 'upload_download_settings',
                'label'       => __( 'Upload/Download Settings', 'admin-help-docs' ),
                'desc'        => __( 'Export your current settings as a JSON file for backup or transfer to another site. You can also import settings from a JSON file exported from this plugin. Note: Importing settings will overwrite your current settings.', 'admin-help-docs' ),
                'type'        => 'html',
                'box'         => 'advanced',
                'content'     => '<button type="button" id="helpdocs-download-settings-btn" class="helpdocs-button">' . __( 'Download Settings', 'admin-help-docs' ) . '</button> <div id="helpdocs-upload-button"><label for="helpdocs-upload-settings"><span class="helpdocs-button">' . __( 'Upload Settings', 'admin-help-docs' ) . '</span></label><input type="file" id="helpdocs-upload-settings" name="helpdocs-upload-settings" accept=".json" style="display:none;"></div> <div id="helpdocs-upload-filename"></div>',
            ],
            [
                'name'        => 'reset_settings',
                'label'       => __( 'Reset Settings', 'admin-help-docs' ),
                'desc'        => __( 'This will not delete any documentation, but it will reset all settings to their defaults.', 'admin-help-docs' ),
                'type'        => 'html',
                'box'         => 'advanced',
                'content'     => '<button type="button" id="helpdocs-reset-colors" class="helpdocs-button">' . __( 'Reset All Colors', 'admin-help-docs' ) . '</button> <button type="button" id="helpdocs-reset-settings" class="helpdocs-button">' . __( 'Reset All Settings', 'admin-help-docs' ) . '</button>',
            ],
        ];

        // Colors
        $color_fields = [];
        $colors       = Colors::defaults();
        foreach ( $colors as $key => $color ) {
            if ( strpos( $key, 'subheader' ) !== false ) {
                continue;
            }
            $color_fields[] = [
                'name'     => 'color_' . $key,
                'label'    => $color[ 'label' ],
                'type'     => 'color',
                'sanitize' => 'sanitize_text_field',
                'box'      => 'interface',
                'default'  => $color[ 'color' ],
            ];
        }

        $themes_index = false;
        foreach ( $fields as $index => $field ) {
            if ( isset( $field[ 'name' ] ) && 'themes' === $field[ 'name' ] ) {
                $themes_index = $index;
                break;
            }
        }

        if ( false !== $themes_index ) {
            array_splice( $fields, $themes_index + 1, 0, $color_fields );
        } else {
            $fields = array_merge( $fields, $color_fields );
        }

        // Only get the name, type and default for each field
        if ( $defaults_only ) {
            foreach ( $fields as $index => $field ) {
                $fields[ $index ] = [
                    'name'    => $field[ 'name' ],
                    'type'    => $field[ 'type' ],
                    'default' => $field[ 'default' ] ?? null,
                ];
            }
            
        }

        return apply_filters( 'helpdocs_settings_fields', $fields );
    } // End setting_fields()


    /**
     * Returns available placements for top docs.
     *
     * @return array
     */
    public static function top_placements() : array {
        return [
            'admin_notices'   => __( 'Just Above Page Title', 'admin-help-docs' ),
            'in_admin_header' => __( 'Very Top of Page (Above All Content)', 'admin-help-docs' ),
        ];
    } // End top_placements()


    /**
     * The single instance of the class
     *
     * @var self|null
     */
    private static ?Settings $instance = null;


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
        add_action( 'helpdocs_subheader_left', [ $this, 'render_save_reminder' ] );
        add_action( 'wp_ajax_helpdocs_save_settings', [ $this, 'ajax_save_settings' ] );
        add_action( 'wp_ajax_helpdocs_flush_cache', [ $this, 'ajax_flush_cache' ] );
    } // End __construct()


    /**
     * Render a reminder to save settings after changes are made
     *
     * @param string $current_tab The current active tab
     */
    public function render_save_reminder( $current_tab ) {
        if ( $current_tab === 'settings' ) {
            echo '<span id="helpdocs-save-reminder">' . esc_html__( 'Remember to click "Save" after making changes to your settings.', 'admin-help-docs' ) . '</span>';
        }
    } // End render_save_reminder()


    /**
     * Render the tab
     */
    public function render_tab() {
        $fields = $this->setting_fields();
        $boxes  = [];
        $box_labels = $this->setting_boxes();

        // Group fields by box
        foreach ( $fields as $field ) {
            $boxes[ $field[ 'box' ] ][] = $field;
        }

        echo '<div class="helpdocs-settings-grid">';
        foreach ( $boxes as $box_id => $fields ) {
            echo '<div class="helpdocs-settings-box">';
            echo '<div class="helpdocs-settings-header">';
            echo '<h2>' . esc_html( $box_labels[ $box_id ] ?? 'Unknown Box' ) . '</h2>';
            echo '</div>';
            echo '<div class="helpdocs-settings-body">';

            $in_color_group = false;
            foreach ( $fields as $index => $field ) {
                $is_color = ( $field[ 'type' ] === 'color' );
                $render_fn = "render_field_{$field[ 'type' ]}";

                if ( $is_color && ! $in_color_group ) {
                    echo '<div class="helpdocs-color-grid">';
                    $in_color_group = true;
                }

                if ( ! $is_color && $in_color_group ) {
                    echo '</div>';
                    $in_color_group = false;
                }

                if ( method_exists( $this, $render_fn ) ) {
                    $this->{$render_fn}( $field );
                }

                $is_last = ( $index === array_key_last( $fields ) );

                if ( $is_last && $in_color_group ) {
                    echo '</div>';
                    $in_color_group = false;
                }
            }
            echo '</div></div>';
        }
        echo '</div>';
    } // End render_tab()


    /**
     * Get the value of a field, falling back to the default if not set
     *
     * @param string $field_name The name of the field
     * @return mixed The value of the field or its default
     */
    public static function get_field_value( $field_name ) {
        $option = get_option( "helpdocs_{$field_name}", null );

        if ( null !== $option ) {
            return $option;
        }

        $all_fields = self::setting_fields();
        foreach ( $all_fields as $field ) {
            if ( $field[ 'name' ] === $field_name ) {
                return $field[ 'default' ] ?? '';
            }
        }

        return '';
    } // End get_field_value()


    /**
     * Sanitize a field value based on the field's specified sanitize callback
     *
     * @param array $field The field definition array
     * @param mixed $value The value to sanitize
     * @return mixed The sanitized value
     */
    public function sanitize_field( $field, $value ) {
        $sanitize_callback = $field[ 'sanitize' ] ?? null;

        if ( $sanitize_callback && is_callable( [ $this, $sanitize_callback ] ) ) {
            return $this->$sanitize_callback( $value );
        } elseif ( is_callable( $sanitize_callback ) ) {
            return $sanitize_callback( $value );
        } else {
            return sanitize_text_field( $value );
        }
    } // End sanitize_field()


    /**
     * Render a field label with optional description tooltip
     *
     * @param array $field The field definition array
     * @param string $for Optional ID of the associated input for the label's "for" attribute
     */
    public function render_field_label( $field, $for = '' ) {
        ?>
        <label <?php echo $for ? 'for="' . esc_attr( $for ) . '"' : ''; ?> class="helpdocs-field-label">
            <?php echo wp_kses_post( $field[ 'label' ] ); ?>
            
            <?php if ( ! empty( $field[ 'required' ] ) ) : ?>
                <span class="helpdocs-required-field" title="<?php esc_attr_e( 'Required', 'admin-help-docs' ); ?>">*</span>
            <?php endif; ?>

            <?php if ( ! empty( $field[ 'desc' ] ) ) : ?>
                <span class="helpdocs-tooltip">
                    <span class="dashicons dashicons-editor-help"></span>
                    <span class="helpdocs-tooltip-text"><?php echo wp_kses_post( $field[ 'desc' ] ); ?></span>
                </span>
            <?php endif; ?>
        </label>
        <?php
    } // End render_field_label()


    /**
     * Render a text field
     * 
     * @param array $field The field definition array
     */
    public function render_field_text( $field ) {
        $value = $this->get_field_value( $field[ 'name' ] );
        
        // If the value is empty/null, use the default if provided
        if ( ( is_null( $value ) || $value === '' ) && isset( $field[ 'default' ] ) ) {
            $value = $field[ 'default' ];
        }

        $value = $this->sanitize_field( $field, $value );

        $conditional_class    = '';
        $data_condition_field = '';
        $data_condition_value = '';

        if ( ! empty( $field[ 'condition' ] ) ) {
            $condition_field = key( $field[ 'condition' ] );
            $expected_value  = current( $field[ 'condition' ] );
            $target_value    = $this->get_field_value( $condition_field );

            if ( $target_value != $expected_value ) {
                $conditional_class = ' condition-hide';
            }

            $data_condition_field = $condition_field;
            $data_condition_value = $expected_value;
        }

        $required = ! empty( $field[ 'required' ] ) ? ' required' : '';
        ?>
        <div id="helpdocs_field_<?php echo esc_attr( $field[ 'name' ] ); ?>"
            class="helpdocs-field<?php echo esc_attr( $conditional_class ); ?>"
            <?php if ( $data_condition_field && $data_condition_value ) : ?>
                data-condition-field="<?php echo esc_attr( $data_condition_field ); ?>"
                data-condition-value="<?php echo esc_attr( $data_condition_value ); ?>"
            <?php endif; ?>>
            <?php $this->render_field_label( $field, $field[ 'name' ] ); ?>
            <input type="text"
                id="<?php echo esc_attr( $field[ 'name' ] ); ?>"
                name="helpdocs_<?php echo esc_attr( $field[ 'name' ] ); ?>"
                value="<?php echo esc_attr( $value ); ?>"
                <?php echo esc_attr( $required ); ?>
                placeholder="<?php echo isset( $field[ 'default' ] ) ? esc_attr( $field[ 'default' ] ) : ''; ?>">
        </div>
        <?php
    } // End render_field_text()


    /**
     * Render a textarea field
     * 
     * @param array $field The field definition array
     */
    public function render_field_textarea( $field ) {
        $value = $this->get_field_value( $field[ 'name' ] );
        $value = $this->sanitize_field( $field, $value );

        $conditional_class   = '';
        $data_condition_field = '';
        $data_condition_value = '';

        if ( ! empty( $field[ 'condition' ] ) ) {
            $condition_field = key( $field[ 'condition' ] );
            $expected_value  = current( $field[ 'condition' ] );
            $target_value    = $this->get_field_value( $condition_field );

            if ( $target_value != $expected_value ) {
                $conditional_class = ' condition-hide';
            }

            $data_condition_field = $condition_field;
            $data_condition_value = $expected_value;
        }

        $required = ! empty( $field[ 'required' ] ) ? ' required' : '';
        ?>
        <div id="helpdocs_field_<?php echo esc_attr( $field[ 'name' ] ); ?>"
            class="helpdocs-field<?php echo esc_attr( $conditional_class ); ?>"
            <?php if ( $data_condition_field && $data_condition_value ) : ?>
                data-condition-field="<?php echo esc_attr( $data_condition_field ); ?>"
                data-condition-value="<?php echo esc_attr( $data_condition_value ); ?>"
            <?php endif; ?>>
            <?php $this->render_field_label( $field, $field[ 'name' ] ); ?>
            <textarea id="<?php echo esc_attr( $field[ 'name' ] ); ?>"
                name="helpdocs_<?php echo esc_attr( $field[ 'name' ] ); ?>"
                <?php echo esc_attr( $required ); ?>
                placeholder="<?php if ( isset( $field[ 'default' ] ) ) { echo esc_attr( $field[ 'default' ] ); } ?>"><?php echo esc_textarea( $value ); ?></textarea>
        </div>
        <?php
    } // End render_field_textarea()


    /**
     * Render a color field
     * 
     * @param array $field The field definition array
     */
    public function render_field_color( $field ) {
        $value = Colors::get( str_replace( 'color_', '', $field[ 'name' ] ) );
        $value = $this->sanitize_field( $field, $value );
        ?>
        <div id="helpdocs_field_<?php echo esc_attr( $field[ 'name' ] ); ?>" class="helpdocs-field color-picker-field">
            <label for="<?php echo esc_attr( $field[ 'name' ] ); ?>">
                <?php echo esc_html( $field[ 'label' ] ); ?>
            </label>
            <input type="color"
                id="<?php echo esc_attr( $field[ 'name' ] ); ?>"
                name="helpdocs_<?php echo esc_attr( $field[ 'name' ] ); ?>"
                value="<?php echo esc_attr( $value ); ?>">
        </div>
        <?php
    } // End render_field_color()


    /**
     * Render a select field
     * 
     * @param array $field The field definition array, which should include a 'choices' key with an array of options
     */
    public function render_field_select( $field ) {
        $option = get_option( "helpdocs_{$field[ 'name' ]}", null );

        $value = ( null === $option || '' === $option )
            ? ( $field[ 'default' ] ?? '' )
            : $option;

        $value = $this->sanitize_field( $field, $value );

        // Strip dashicons
        $value = str_replace( 'dashicons-', '', $value );

        $has_condition = isset( $field[ 'has_condition' ] ) && $field[ 'has_condition' ];
        $conditional_class = $has_condition ? ' has-condition' : '';

        $required = ! empty( $field[ 'required' ] ) ? ' required' : '';
        ?>
        <div id="helpdocs_field_<?php echo esc_attr( $field[ 'name' ] ); ?>" class="helpdocs-field">
            <?php $this->render_field_label( $field, $field[ 'name' ] ); ?>
            <select id="<?php echo esc_attr( $field[ 'name' ] ); ?>" name="helpdocs_<?php echo esc_attr( $field[ 'name' ] ); ?>" class="<?php echo esc_attr( $conditional_class ); ?>"<?php echo esc_attr( $required ); ?>>
                <?php
                if ( isset( $field[ 'choices' ] ) && is_array( $field[ 'choices' ] ) ) {

                    foreach ( $field[ 'choices' ] as $key => $choice ) {

                        if ( is_array( $choice ) ) {
                            $option_value = $choice[ 'value' ] ?? '';
                            $option_label = $choice[ 'label' ] ?? '';
                        } else {
                            $option_value = is_string( $key ) ? $key : $choice;
                            $option_label = $choice;
                        }
                        ?>
                        <option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $value, $option_value ); ?>>
                            <?php echo esc_html( $option_label ); ?>
                        </option>
                        <?php
                    }
                }
                ?>
            </select>
        </div>
        <?php
    } // End render_field_select()


    /**
     * Render a checkbox field
     *
     * @param array $field The field definition array
     */
    public function render_field_checkbox( $field ) {
        $option = get_option( "helpdocs_{$field[ 'name' ]}", null );

        $value = ( null === $option )
            ? ( $field[ 'default' ] ?? '' )
            : $option;

        $value = $this->sanitize_field( $field, $value );

        $has_condition = isset( $field[ 'has_condition' ] ) && $field[ 'has_condition' ];
        $conditional_class = $has_condition ? ' has-condition' : '';

        $required = ! empty( $field[ 'required' ] ) ? ' required' : '';
        ?>
        <div id="helpdocs_field_<?php echo esc_attr( $field[ 'name' ] ); ?>" class="helpdocs-field helpdocs-field-checkbox<?php echo esc_attr( $conditional_class ); ?>">
            <label class="helpdocs-checkbox-label">
                <input type="checkbox"
                    id="<?php echo esc_attr( $field[ 'name' ] ); ?>"
                    name="helpdocs_<?php echo esc_attr( $field[ 'name' ] ); ?>"
                    value="1" <?php checked( $value, '1' ); ?> <?php echo esc_attr( $required ); ?>>
                <span><?php echo esc_html( $field[ 'label' ] ); ?></span>
                <?php if ( ! empty( $field[ 'desc' ] ) ) : ?>
                    <span class="helpdocs-tooltip">
                        <span class="dashicons dashicons-editor-help"></span>
                        <span class="helpdocs-tooltip-text"><?php echo wp_kses_post( $field[ 'desc' ] ); ?></span>
                    </span>
                <?php endif; ?>
            </label>
        </div>
        <?php
    } // End render_field_checkbox()


    /**
     * Sanitize checkbox
     * 
     * @param mixed $value The value to sanitize
     * @return string '1' if checked, '' if not
     */
    public function sanitize_checkbox( $value ) {
        return ( ( $value == '1' || $value == 'yes' ) ? '1' : '' );
    } // End sanitize_checkbox()


    /**
     * Render a checkboxes field
     * 
     * @param array $field The field definition array, which should include a 'choices' key with an array of options
     */
    public function render_field_checkboxes( $field ) {
        $values = (array) $this->get_field_value( $field[ 'name' ] );
        $values = (array) $this->sanitize_field( $field, $values );

        $conditional_class   = '';
        $data_condition_field = '';
        $data_condition_value = '';

        if ( ! empty( $field[ 'condition' ] ) ) {
            $condition_field = key( $field[ 'condition' ] );
            $expected_value  = current( $field[ 'condition' ] );
            $target_value    = $this->get_field_value( $condition_field );

            if ( $target_value != $expected_value ) {
                $conditional_class = ' condition-hide';
            }

            $data_condition_field = $condition_field;
            $data_condition_value = $expected_value;
        }
        ?>
        <div id="helpdocs_field_<?php echo esc_attr( $field[ 'name' ] ); ?>"
            class="helpdocs-field<?php echo esc_attr( $conditional_class ); ?>"
            <?php if ( $data_condition_field && $data_condition_value ) : ?>
                data-condition-field="<?php echo esc_attr( $data_condition_field ); ?>"
                data-condition-value="<?php echo esc_attr( $data_condition_value ); ?>"
            <?php endif; ?>>
            <?php $this->render_field_label( $field, $field[ 'name' ] ); ?>
            <div class="helpdocs-checkboxes">
                <?php
                if ( isset( $field[ 'choices' ] ) && is_array( $field[ 'choices' ] ) ) {
                    foreach ( $field[ 'choices' ] as $choice ) :
                        $key   = is_array( $choice ) ? ( $choice[ 'value' ] ?? '' ) : ( $choice[ 0 ] ?? '' );
                        $label = is_array( $choice ) ? ( $choice[ 'label' ] ?? '' ) : ( $choice[ 1 ] ?? '' );
                        ?>
                        <label>
                            <input type="checkbox"
                                name="helpdocs_<?php echo esc_attr( $field[ 'name' ] ); ?>[]"
                                value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $values, true ) || in_array( $key, array_keys( $values ), true ) ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </label>
                    <?php
                    endforeach;
                }
                ?>
            </div>
        </div>
        <?php
    } // End render_field_checkboxes()


    /**
     * Sanitize checkboxes
     * 
     * @param mixed $values The values to sanitize, expected to be an array of selected keys
     * @return array An array of sanitized values that are valid choices
     */
    private function sanitize_checkboxes( $values ) {
        if ( ! is_array( $values ) ) {
            return [];
        }
        return array_map( 'strval', $values );
    } // End sanitize_checkboxes()


    /**
     * Render a file upload field
     * 
     * @param array $field The field definition array
     */
    public function render_field_file( $field ) {
        $conditional_class    = '';
        $data_condition_field = '';
        $data_condition_value = '';

        if ( ! empty( $field[ 'condition' ] ) ) {
            $condition_field = key( $field[ 'condition' ] );
            $expected_value  = current( $field[ 'condition' ] );
            $target_value    = $this->get_field_value( $condition_field );

            if ( $target_value != $expected_value ) {
                $conditional_class = ' condition-hide';
            }

            $data_condition_field = $condition_field;
            $data_condition_value = $expected_value;
        }

        // Allow file types to be filterable
        $default_types = '.jpg,.jpeg,.png,.pdf,.zip';
        $accepted_types = apply_filters( 'helpdocs_support_file_types', $default_types, $field );

        $required = ! empty( $field[ 'required' ] ) ? ' required' : '';
        ?>
        <div id="helpdocs_field_<?php echo esc_attr( $field[ 'name' ] ); ?>"
            class="helpdocs-field helpdocs-field-file<?php echo esc_attr( $conditional_class ); ?>"
            <?php if ( $data_condition_field && $data_condition_value ) : ?>
                data-condition-field="<?php echo esc_attr( $data_condition_field ); ?>"
                data-condition-value="<?php echo esc_attr( $data_condition_value ); ?>"
            <?php endif; ?>>
            <?php $this->render_field_label( $field, $field[ 'name' ] ); ?>
            <input type="file"
                id="helpdocs_<?php echo esc_attr( $field[ 'name' ] ); ?>" // Added prefix to ID
                name="helpdocs_<?php echo esc_attr( $field[ 'name' ] ); ?>[]"
                accept="<?php echo esc_attr( $accepted_types ); ?>"
                <?php echo esc_attr( $required ); ?>
                multiple>
        </div>
        <?php
    } // End render_field_file()

    
    /**
     * Sanitize and process file uploads
     * 
     * @param string $field_name The name of the file input
     * @return array List of local file paths for wp_mail attachments
     */
    public function sanitize_files( $field_name, $max_total_mb = 10 ) {
        if ( empty( $_FILES[ "helpdocs_$field_name" ] ) ) { // phpcs:ignore
            return [];
        }

        $files = $_FILES[ "helpdocs_$field_name" ]; // phpcs:ignore
        $attachments = [];
        $total_size = 0;
        $max_total_size = $max_total_mb * 1024 * 1024; // Convert MB to bytes

        if ( is_array( $files[ 'name' ] ) ) {
            // First Pass: Check total size
            foreach ( $files[ 'size' ] as $size ) {
                $total_size += $size;
            }

            if ( $total_size > $max_total_size ) {
                wp_send_json_error( sprintf( __( 'The total size of attachments exceeds %dMB.', 'admin-help-docs' ), $max_total_mb ) );
            }

            // Second Pass: Process uploads
            foreach ( $files[ 'name' ] as $key => $value ) {
                if ( $files[ 'name' ][ $key ] ) {
                    $file = [
                        'name'     => $files[ 'name' ][ $key ],
                        'type'     => $files[ 'type' ][ $key ],
                        'tmp_name' => $files[ 'tmp_name' ][ $key ],
                        'error'    => $files[ 'error' ][ $key ],
                        'size'     => $files[ 'size' ][ $key ],
                    ];

                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
                    
                    $upload_overrides = [ 'test_form' => false ];
                    $movefile = wp_handle_upload( $file, $upload_overrides );

                    if ( $movefile && ! isset( $movefile[ 'error' ] ) ) {
                        $attachments[] = $movefile[ 'file' ];
                    }
                }
            }
        }

        return $attachments;
    } // End sanitize_files()


    /**
     * Render an API Key field with Generate, Copy, and Clear actions
     * * @param array $field The field definition array
     */
    public function render_field_api_key( $field ) {
        $value = $this->get_field_value( $field[ 'name' ] );
        
        if ( ( is_null( $value ) || $value === '' ) && isset( $field[ 'default' ] ) ) {
            $value = $field[ 'default' ];
        }

        $value = $this->sanitize_field( $field, $value );

        $conditional_class    = '';
        $data_condition_field = '';
        $data_condition_value = '';

        if ( ! empty( $field[ 'condition' ] ) ) {
            $condition_field = key( $field[ 'condition' ] );
            $expected_value  = current( $field[ 'condition' ] );
            $target_value    = $this->get_field_value( $condition_field );

            if ( $target_value != $expected_value ) {
                $conditional_class = ' condition-hide';
            }

            $data_condition_field = $condition_field;
            $data_condition_value = $expected_value;
        }
        ?>
        <div id="helpdocs_field_<?php echo esc_attr( $field[ 'name' ] ); ?>"
            class="helpdocs-field<?php echo esc_attr( $conditional_class ); ?>"
            <?php if ( $data_condition_field && $data_condition_value ) : ?>
                data-condition-field="<?php echo esc_attr( $data_condition_field ); ?>"
                data-condition-value="<?php echo esc_attr( $data_condition_value ); ?>"
            <?php endif; ?>>
            
            <?php $this->render_field_label( $field, $field[ 'name' ] ); ?>

            <div class="helpdocs-api-key-wrapper">
                <div id="helpdocs_api_key_display" class="helpdocs-api-key-box <?php echo $value ? 'has-key' : 'no-key'; ?>">
                    <?php echo $value ? esc_html( $value ) : '<em>' . esc_html__( 'No API Key Generated', 'admin-help-docs' ) . '</em>'; ?>
                </div>
                
                <input type="hidden" 
                    id="<?php echo esc_attr( $field[ 'name' ] ); ?>" 
                    name="helpdocs_<?php echo esc_attr( $field[ 'name' ] ); ?>" 
                    value="<?php echo esc_attr( $value ); ?>">

                <div class="helpdocs-api-key-actions">
                    <button type="button" class="helpdocs-button helpdocs-generate-api-key"><?php esc_html_e( 'Generate API Key', 'admin-help-docs' ); ?></button>
                    <button type="button" class="helpdocs-button helpdocs-copy-api-key" <?php echo ! $value ? 'disabled' : ''; ?>><?php esc_html_e( 'Copy', 'admin-help-docs' ); ?></button>
                    <button type="button" class="helpdocs-button helpdocs-clear-api-key" <?php echo ! $value ? 'disabled' : ''; ?>><?php esc_html_e( 'Clear', 'admin-help-docs' ); ?></button>
                </div>
            </div>
        </div>
        <?php
    } // End render_field_api_key()


    /**
     * Sanitize custom CSS input
     * 
     * @param string $css The CSS input to sanitize
     * @return string The sanitized CSS
     */
    public function sanitize_custom_css( $css ) {
        $css = wp_unslash( $css );
        $css = wp_strip_all_tags( $css );
        return $css;
    } // End sanitize_custom_css()


    /**
     * Render an HTML field (for custom content like buttons)
     * 
     * @param array $field The field definition array, which should include a 'content' key with the HTML to render
     */
    public function render_field_html( $field ) {
        ?>
        <div id="helpdocs_field_<?php echo esc_attr( $field[ 'name' ] ); ?>" class="helpdocs-field helpdocs-field-html">
            <label>
                <?php echo wp_kses_post( $field[ 'label' ] ); ?>
                <?php if ( ! empty( $field[ 'desc' ] ) ) : ?>
                    <span class="helpdocs-tooltip">
                        <span class="dashicons dashicons-editor-help"></span>
                        <span class="helpdocs-tooltip-text"><?php echo wp_kses_post( $field[ 'desc' ] ); ?></span>
                    </span>
                <?php endif; ?>
            </label>
            <div class="helpdocs-html-content">
                <?php echo wp_kses_post( $field[ 'content' ] ?? '' ); ?>
            </div>
        </div>
        <?php
    } // End render_field_html()


    /**
     * AJAX handler to save settings
     */
    public function ajax_save_settings() {
        check_ajax_referer( 'helpdocs_settings_nonce', 'nonce' );
        if ( ! current_user_can( Helpers::admin_role() ) ) {
            wp_send_json_error( 'Insufficient permissions.' );
        }

        $fields = self::setting_fields();

        $colors_to_save = [];
        $errors = [];

        foreach ( $fields as $field ) {
            $name = $field[ 'name' ];
            $post_key = 'helpdocs_' . $name;

            if ( strpos( $name, 'color_' ) === 0 ) {
                $key = str_replace( 'color_', '', $name );
                if ( isset( $_POST[ 'settings' ][ $post_key ] ) ) {
                    $raw_value = wp_unslash( $_POST[ 'settings' ][ $post_key ] ); // phpcs:ignore
                    $colors_to_save[ $key ] = $this->sanitize_field( $field, $raw_value );
                }
                continue;
            }

            if ( isset( $_POST[ 'settings' ][ $post_key ] ) ) {
                // Sanitize below based on field type, but first get the raw value for checkboxes
                $raw_value = wp_unslash( $_POST[ 'settings' ][ $post_key ] ); // phpcs:ignore
            } else {
                // If it's a checkbox group and nothing is checked, default to empty array
                if ( $field[ 'type' ] === 'checkboxes' ) {
                    $raw_value = [];
                } elseif ( $field[ 'type' ] === 'checkbox' ) {
                    $raw_value = 0;
                } else {
                    continue;
                }
            }

            // Determine type and sanitize accordingly
            if ( $field[ 'type' ] === 'checkbox' ) {
                $value = $this->sanitize_checkbox( $raw_value );
            } elseif ( $field[ 'type' ] === 'checkboxes' ) {
                $value = $this->sanitize_checkboxes( (array) $raw_value );
            } elseif ( $field[ 'sanitize' ] === 'sanitize_custom_css' ) {
                $value = $this->sanitize_custom_css( $raw_value );
            } else {
                $value = $this->sanitize_field( $field, $raw_value );

                // If text field is empty, fall back to default
                if ( $field[ 'type' ] === 'text' && $value === '' && isset( $field[ 'default' ] ) ) {
                    $value = $field[ 'default' ];
                }
            }

            $updated = update_option( $post_key, $value );
            if ( $updated === false && get_option( $post_key ) != $value ) {
                $errors[] = $name;
            }
        }

        // Before saving all colors, update the CSS variable storage if needed
        Colors::convert_color_storage();

        if ( ! empty( $colors_to_save ) ) {
            $updated_colors = update_option( 'helpdocs_colors', $colors_to_save );
            if ( $updated_colors === false && get_option( 'helpdocs_colors' ) !== $colors_to_save ) {
                $errors[] = 'colors';
            }
        }

        // Flush the cache so changes show immediately
        Helpers::flush_permissions_cache();

        if ( empty( $errors ) ) {
            wp_send_json_success();
        } else {
            wp_send_json_error( 'Failed to save: ' . implode( ', ', $errors ) );
        }
    } // End ajax_save_settings()


    /**
     * AJAX handler to flush system cache
     */
    public function ajax_flush_cache() {
        check_ajax_referer( 'helpdocs_settings_nonce', 'nonce' );
        if ( ! Helpers::user_can_edit() ) {
            wp_send_json_error( __( 'You do not have permission to perform this action.', 'admin-help-docs' ) );
        }

        Helpers::flush_location_cache();

        wp_send_json_success( __( 'Cache cleared successfully.', 'admin-help-docs' ) );
    } // End ajax_flush_cache()

    
    /**
     * Prevent cloning and unserializing
     */
    public function __clone() {}
    public function __wakeup() {}

}


Settings::instance();