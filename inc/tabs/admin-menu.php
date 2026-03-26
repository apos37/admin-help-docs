<?php
/**
 * Admin Menu Tab Loader
 */

namespace PluginRx\AdminHelpDocs;

if ( ! defined( 'ABSPATH' ) ) exit;

class AdminMenu {

    /**
     * Returns all settings boxes.
     */
    public static function setting_boxes() : array {
        $boxes = [
            'menu'    => __( 'Parent Menu Items', 'admin-help-docs' ),
            'options' => __( 'Options', 'admin-help-docs' ),
        ];

        return apply_filters( 'helpdocs_settings_boxes', $boxes );
    } // End setting_boxes()


    /**
     * Returns all settings fields.
     */
    public static function setting_fields( $defaults_only = false ) : array {
        $fields = [
            // Menu
            [
                'name'        => 'admin_menu_order',
                'label'       => __( 'Parent Menu Items', 'admin-help-docs' ),
                'type'        => 'menu_order',
                'box'         => 'menu',
            ],

            // Options
            [
                'name'        => 'enable_admin_menu_sorting',
                'label'       => __( 'Enable Admin Menu Sorting', 'admin-help-docs' ),
                'desc'        => __( 'This enables the drag-and-drop functionality for the admin menu.', 'admin-help-docs' ),
                'type'        => 'checkbox',
                'sanitize'    => 'sanitize_checkbox',
                'box'         => 'options',
                'default'     => false,
            ],
            [
                'name'        => 'show_menu_item_slugs',
                'label'       => __( 'Show Menu Item Slugs', 'admin-help-docs' ),
                'desc'        => __( 'Displays the slugs of menu items in the sorter.', 'admin-help-docs' ),
                'type'        => 'checkbox',
                'sanitize'    => 'sanitize_checkbox',
                'box'         => 'options',
                'default'     => false,
            ],
            [
                'name'        => 'colorize_separators',
                'label'       => __( 'Colorize Separators', 'admin-help-docs' ),
                'desc'        => __( 'Adds color to the separators in the admin menu.', 'admin-help-docs' ),
                'type'        => 'checkbox',
                'sanitize'    => 'sanitize_checkbox',
                'box'         => 'options',
                'default'     => false,
            ],
            [
                'name'        => 'color_admin_menu_sep',
                'label'       => __( 'Separator Color', 'admin-help-docs' ),
                'type'        => 'color',
                'sanitize'    => 'sanitize_text_field',
                'box'         => 'options',
                'default'     => '#D1D1D1',
            ],
        ];

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

        return $fields;
    } // End setting_fields()


    /**
     * The single instance of the class
     *
     * @var self|null
     */
    private static ?AdminMenu $instance = null;


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
        add_action( 'admin_menu', [ $this, 'separators' ], PHP_INT_MAX  );
        add_filter( 'menu_order', [ $this, 'apply_menu_order' ], PHP_INT_MAX );
        add_filter( 'custom_menu_order', [ $this, 'enable_custom_menu_order' ] );
        add_filter( 'admin_body_class', [ $this, 'add_body_class' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_separator_styles' ] );
        add_action( 'wp_ajax_helpdocs_save_menu_order', [ $this, 'ajax_save_settings' ] );
    } // End __construct()


    /**
     * Render a reminder to save settings after changes are made
     *
     * @param string $current_tab The current active tab
     */
    public function render_save_reminder( $current_tab ) {
        if ( $current_tab === 'admin-menu' ) {
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
                } elseif ( method_exists( Settings::class, $render_fn ) ) {
                    Settings::instance()->{$render_fn}( $field );
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
     * Render the menu order field
     *
     * @param array $field The field definition array
     */
    private function render_field_menu_order( $field ) {
        // Get the parent menu items
        global $menu;

        // Default WP separators
        $default_separators = [
            'separator1',
            'separator2',
            'separator-last'
        ];

        // Your extra separators
        $extra_separators = [
            'separator-helpdocs-extra1',
            'separator-helpdocs-extra2',
            'separator-helpdocs-extra3'
        ];

        // Keep only allowed separators
        $allowed_separators = array_merge( $default_separators, $extra_separators );

        // Build current menu items array
        $admin_menu_items = [];
        foreach ( $menu as $menu_item ) {
            $slug = $menu_item[2] ?? '';

            // Skip any separator not in allowed list
            if ( str_starts_with( (string) $slug, 'separator' ) && ! in_array( $slug, $allowed_separators, true ) ) {
                continue;
            }

            if ( str_starts_with( (string) $slug, 'separator' ) ) {
                $label = '-- Separator -- (place at bottom to hide)';
            } else {
                $label = isset( $menu_item[0] ) ? wp_strip_all_tags( $menu_item[0] ) : '';
                $label = preg_replace( '/\s+\d+.*$/', '', $label );
                $label = trim( $label );
            }

            $admin_menu_items[ $slug ] = [
                'label'    => $label,
                'sublabel' => $slug,
                'value'    => $slug
            ];
        }

        // Ensure default WP separators are included
        foreach ( $default_separators as $slug ) {
            if ( ! isset( $admin_menu_items[ $slug ] ) ) {
                $admin_menu_items[ $slug ] = [
                    'label'    => '-- Separator -- (place at bottom to hide)',
                    'sublabel' => $slug,
                    'value'    => $slug
                ];
            }
        }

        // Add your extra separators
        foreach ( $extra_separators as $slug ) {
            $admin_menu_items[ $slug ] = [
                'label'    => '-- Separator -- (place at bottom to hide)',
                'sublabel' => $slug,
                'value'    => $slug
            ];
        }

        // Apply saved order
        $saved_order = get_option( 'helpdocs_admin_menu_order', [] );

        if ( ! empty( $saved_order ) ) {
            $ordered_items = [];
            // First, add items in saved order if they exist
            foreach ( $saved_order as $slug ) {
                if ( isset( $admin_menu_items[ $slug ] ) ) {
                    $ordered_items[ $slug ] = $admin_menu_items[ $slug ];
                }
            }
            // Then, add any new items not in saved order
            foreach ( $admin_menu_items as $slug => $data ) {
                if ( ! isset( $ordered_items[ $slug ] ) ) {
                    $ordered_items[ $slug ] = $data;
                }
            }
            $admin_menu_items = $ordered_items;
        }

        $slug_display = get_option( 'helpdocs_show_menu_item_slugs', false ) ? 'block' : 'none';
        ?>
        <ul class="helpdocs-sorter">
            <?php foreach ( $admin_menu_items as $data ) : ?>
                <li class="helpdocs-sorter-item" draggable="true" data-value="<?php echo esc_attr( $data[ 'value' ] ); ?>">
                    <span class="dashicons dashicons-menu helpdocs-sort-handle"></span>
                    <span class="helpdocs-sort-text">
                        <span class="helpdocs-sort-label">
                            <?php echo esc_html( wp_strip_all_tags( $data[ 'label' ] ) ); ?>
                        </span>
                    </span>

                    <?php if ( ! empty( $data[ 'sublabel' ] ) ) : ?>
                        <code class="helpdocs-sort-sublabel" style="display: <?php echo esc_attr( $slug_display ); ?>;">
                            <?php echo esc_html( $data[ 'sublabel' ] ); ?>
                        </code>
                    <?php endif; ?>

                    <input type="hidden" name="admin_menu_order[]" value="<?php echo esc_attr( $data[ 'value' ] ); ?>">
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
    } // End render_field_menu_order()


    /**
     * Render a color picker field
     *
     * @param array $field The field definition array
     */
    public function render_field_color( $field ) {
        $value = get_option( 'helpdocs_' . $field[ 'name' ], null );
        if ( null === $value ) {
            $value = $field[ 'default' ] ?? '';
        }
        $value = sanitize_text_field( $value );
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
     * Enable custom menu order
     */
    public function enable_custom_menu_order( $enabled ) {
        return filter_var( get_option( 'helpdocs_enable_admin_menu_sorting', false ), FILTER_VALIDATE_BOOLEAN ) ? true : $enabled;
    } // End enable_custom_menu_order()


    /**
     * Add/remove separators on the admin menu based on saved order
     */
    public function separators() {
        if ( $this->enable_custom_menu_order( false ) === false ) {
            return;
        }

        global $menu;

        $saved_order = get_option( 'helpdocs_admin_menu_order' );
        if ( empty( $saved_order ) || ! is_array( $saved_order ) ) {
            return;
        }

        $allowed = [
            'separator1',
            'separator2',
            'separator-last',
            'separator-helpdocs-extra1',
            'separator-helpdocs-extra2',
            'separator-helpdocs-extra3'
        ];

        foreach ( $saved_order as $slug ) {
            if (
                str_starts_with( $slug, 'separator' ) &&
                in_array( $slug, $allowed, true ) &&
                ! $this->separator_exists( $slug )
            ) {
                $menu[] = [
                    '',
                    'read',
                    $slug,
                    '',
                    'wp-menu-separator ' . $slug
                ];
            }
        }
    } // End separators()

    
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
        if ( $this->enable_custom_menu_order( false ) === false ) {
            return $menu_order;
        }

        $saved_order = get_option( 'helpdocs_admin_menu_order', [] );

        if ( empty( $saved_order ) || ! is_array( $saved_order ) ) {
            return $menu_order;
        }

        $final = [];
        $seen  = [];

        // Add items in saved order if they exist in current menu
        foreach ( $saved_order as $slug ) {
            if ( in_array( $slug, $menu_order, true ) ) {
                $final[] = $slug;
                $seen[ $slug ] = true;
            }
        }

        // Append any items not in saved order
        foreach ( $menu_order as $slug ) {
            if ( ! isset( $seen[ $slug ] ) ) {
                $final[] = $slug;
            }
        }

        return $final;
    } // End apply_menu_order()


    /**
     * Add body class if separator coloring is enabled
     */
    public function add_body_class( $classes ) {
        if ( get_option( 'helpdocs_colorize_separators' ) ) {
            $classes .= ' helpdocs-separator-enabled';
        }
        return $classes;
    } // End add_body_class()


    /**
     * Enqueue styles for colored separators
     */
    public function enqueue_separator_styles() {
        $sep_color = get_option( 'helpdocs_color_admin_menu_sep', '#d1d1d1' );
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


    /**
     * Handle AJAX request to save settings
     */
    public function ajax_save_settings() {
        check_ajax_referer( 'helpdocs_admin_menu_nonce', 'nonce' );
        if ( ! current_user_can( Helpers::admin_role() ) ) {
            wp_send_json_error( 'Insufficient permissions.' );
        }

        $fields = self::setting_fields();

        $errors = [];

        foreach ( $fields as $field ) {
            $name = $field[ 'name' ];
            $post_key = 'helpdocs_' . $name;

            if ( $name === 'menu_order' ) {
                continue;
            }

            if ( isset( $_POST[ 'settings' ][ $post_key ] ) ) {
                // Sanitize below
                $raw_value = wp_unslash( $_POST[ 'settings' ][ $post_key ] ); // phpcs:ignore
            } else {
                // If it's a checkbox and nothing is checked, default to 0
                if ( $field[ 'type' ] === 'checkbox' ) {
                    $raw_value = 0;
                } else {
                    continue;
                }
            }

            // Determine type and sanitize accordingly
            if ( $field[ 'type' ] === 'checkbox' ) {
                $value = ( $raw_value == '1' ? '1' : '' );
            } else {
                $value = sanitize_text_field( $raw_value );
            }

            $updated = update_option( $post_key, $value );
            if ( $updated === false && get_option( $post_key ) != $value ) {
                $errors[] = $name;
            }
        }

        $menu_order = wp_unslash( $_POST[ 'menu_order' ] ?? [] ); // phpcs:ignore
        if ( is_array( $menu_order ) ) {
            $menu_order = array_map( 'sanitize_text_field', $menu_order );
            $updated = update_option( 'helpdocs_admin_menu_order', $menu_order );
            if ( $updated === false && get_option( 'helpdocs_admin_menu_order' ) != $menu_order ) {
                $errors[] = 'admin_menu_order';
            }
        }

        if ( empty( $errors ) ) {
            wp_send_json_success();
        } else {
            wp_send_json_error( 'Failed to save: ' . implode( ', ', $errors ) );
        }
    } // End ajax_save_settings()

    
    /**
     * Prevent cloning and unserializing
     */
    public function __clone() {}
    public function __wakeup() {}

}


AdminMenu::instance();