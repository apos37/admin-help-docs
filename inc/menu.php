<?php
/**
 * Menu Loader
 */

namespace PluginRx\AdminHelpDocs;

if ( ! defined( 'ABSPATH' ) ) exit;

class Menu {

    /**
     * Defaults
     */
    public static $default_menu_position = 2;


    /**
     * Tab slugs and labels
     * 
     * @param string $tab The current tab slug.
     * @return array
     */
    public static function tabs( $tab = null ) : array {
        $post_new_link = Bootstrap::admin_url( 'post-new.php' );
        $author_uri = Bootstrap::author_uri();
        $text_domain = Bootstrap::textdomain();

        $import_header = isset( $_GET[ 'id' ] ) ? __( 'Edit Import', 'admin-help-docs' ) : __( 'Add New Import', 'admin-help-docs' ); // phpcs:ignore 

        $tabs = [
            'documentation' => [
                'label'       => __( 'Documentation', 'admin-help-docs' ),
                'buttons'    => [
                    [
                        'link' => add_query_arg( 'post_type', HelpDocs::$post_type, $post_new_link ),
                        'text' => '<span class="dashicons dashicons-plus"></span> ' . __( 'Add New', 'admin-help-docs' ),
                    ],
                ],
                'class'       => Documentation::class,
            ],
            'manage' => [
                'label'       => __( 'Manage Docs', 'admin-help-docs' ),
                'title'       => __( 'Manage Help Docs', 'admin-help-docs' ),
                'buttons'    => [
                    [
                        'link' => add_query_arg( 'post_type', HelpDocs::$post_type, $post_new_link ),
                        'text' => '<span class="dashicons dashicons-plus"></span> ' . __( 'Add New', 'admin-help-docs' ),
                    ],
                ],
                'link'        => Bootstrap::tab_url( 'manage' ),
            ],
            'folders' => [
                'label'       => __( 'Folders', 'admin-help-docs' ),
                'title'       => __( 'Folders', 'admin-help-docs' ),
                'link'        => Bootstrap::tab_url( 'folders' ),
            ],
            'imports' => [
                'label'       => __( 'Imports', 'admin-help-docs' ),
                'title'       => __( 'Imported Docs', 'admin-help-docs' ),
                'buttons'    => [
                    [
                        'link' => Bootstrap::tab_url( 'import' ),
                        'text' => '<span class="dashicons dashicons-plus"></span> ' . __( 'Add New', 'admin-help-docs' ),
                    ],
                ],
                'link'        => Bootstrap::tab_url( 'imports' ),
            ],
            'import' => [
                'label'  => $import_header,
                'buttons' => [
                    [
                        'id'   => 'save_import_settings',
                        'link' => '#',
                        'text' => __( 'Save Import Settings', 'admin-help-docs' ),
                        'form' => 'helpdocs_import_form',
                        'disabled' => true,
                    ],
                ],
                'class'  => ImportEditor::class,
                'hidden' => true,
            ],
            'admin-menu' => [
                'label' => __( 'Admin Menu', 'admin-help-docs' ),
                'buttons' => [
                    [
                        'link' => '#',
                        'text' => __( 'Save', 'admin-help-docs' ),
                    ],
                ],
                'class' => AdminMenu::class,
            ],
            'settings' => [
                'label' => __( 'Settings', 'admin-help-docs' ),
                'buttons' => [
                    [
                        'link' => '#',
                        'text' => __( 'Save', 'admin-help-docs' ),
                    ],
                ],
                'class' => Settings::class,
            ],
            'faq' => [
                'label' => __( 'FAQ', 'admin-help-docs' ),
                'title' => __( 'Frequently Asked Questions', 'admin-help-docs' ),
                'buttons' => [
                    [
                        'link'   => $author_uri . 'guide/plugin/' . $text_domain,
                        'text'   => __( 'Help Guides', 'admin-help-docs' ),
                        'target' => '_blank',
                    ],
                    [
                        'link'   => $author_uri . 'docs/plugin/' . $text_domain,
                        'text'   => __( 'Developer Docs', 'admin-help-docs' ),
                        'target' => '_blank',
                    ],
                    [
                        'link'   => $author_uri . 'support/plugin/' . $text_domain,
                        'text'   => __( 'Support', 'admin-help-docs' ),
                        'target' => '_blank',
                    ],
                ],
                'class' => FAQ::class,
            ],
            'support' => [
                'label' => __( 'Contact Support', 'admin-help-docs' ),
                'title' => __( 'Contact Support', 'admin-help-docs' ),
                'class' => Support::class,
            ],
        ];

        if ( $tab ) {
            return $tabs[ $tab ] ?? [];
        }
        return $tabs;
    } // End tabs()


    /**
     * The single instance of the class
     *
     * @var self|null
     */
    private static ?Menu $instance = null;


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
        add_action( is_network_admin() ? 'network_admin_menu' : 'admin_menu', [ $this, 'register_menu' ] );
        add_filter( 'parent_file', [ $this, 'submenus' ], 999 );
        add_action( 'admin_body_class', [ $this, 'admin_body_class' ] );
        add_filter( 'admin_title', [ $this, 'admin_title' ], 10, 2 );
        add_action( 'in_admin_header', [ $this, 'admin_header' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    } // End __construct()


    /**
     * Register admin menu
     *
     * @return void
     */
    public function register_menu() : void {
        $title = sanitize_text_field( get_option( 'helpdocs_menu_title', Helpers::get_menu_title() ) );

        $icon = Helpers::get_icon();
        $position = absint( get_option( 'helpdocs_menu_position', self::$default_menu_position ) );

        $parent_slug = Bootstrap::textdomain();

        add_menu_page(
            Bootstrap::name(),
            $title,
            'read',
            $parent_slug,
            [ $this, 'render_tab' ],
            $icon,
            $position
        );

        if ( ! Helpers::user_can_view() ) {
            remove_menu_page( $parent_slug );
            return;
        }

        $user_can_edit = Helpers::user_can_edit();

        global $submenu;

        foreach ( self::tabs() as $slug => $tab ) {
            
            if ( isset( $tab[ 'hidden' ] ) && $tab[ 'hidden' ] ) {
                continue;
            }

            // Documentation tab is always added for users with view access, even if they don't have edit access.
            // Other tabs are only added if the user has edit access.
            if ( $slug === 'documentation' ) {
                $link = Bootstrap::tab_url( $slug );
                $submenu[ $parent_slug ][] = [ $tab[ 'label' ] ?? '', 'read', $link ];
                continue;
            }

            if ( ! $user_can_edit ) {
                continue;
            }

            if ( $slug === 'support' && ! get_option( 'helpdocs_contact_form' ) ) {
                continue;
            }

            if ( isset( $tab[ 'link' ] ) && $tab[ 'link' ] ) {
                $link = $tab[ 'link' ];
            } else {
                $link = Bootstrap::tab_url( $slug );
            }

            $submenu[ $parent_slug ][] = [ $tab[ 'label' ] ?? '', 'read', $link ];
        }

        // Register the hidden dashboard replacement page
        add_submenu_page(
            'index.php',
            __( 'Dashboard', 'admin-help-docs' ),
            __( 'Dashboard', 'admin-help-docs' ),
            'read',
            'admin-help-dashboard',
            [ WPDashboard::class, 'render_replacement_page' ]
        );
    } // End register_menu()


    /**
     * Set the correct submenu file for our pages
     *
     * @param string $parent_file The current parent file slug.
     * @return string The modified parent file slug.
     */
    public function submenus( $parent_file ) {
        global $submenu_file, $current_screen;

        if ( ( 'admin_page_admin-help-dashboard' ) === $current_screen->id ) {
            $parent_file = 'index.php';
            $submenu_file = 'index.php';
            return $parent_file;
        }

        $textdomain = Bootstrap::textdomain();
        $options_page = 'toplevel_page_' . $textdomain;
        
        if ( is_network_admin() ) {
            $options_page .= '-network';
        }

        // Help Docs Tabs
        if ( $current_screen->id === $options_page ) {
            $tab = self::get_current_tab();
            if ( ! $tab ) {
                return $parent_file;
            }

            if ( $tab === 'import' ) {
                $parent_file  = $textdomain;
                $submenu_file = Bootstrap::tab_url( 'imports' );
                return $parent_file;
            }

            $submenu_file = Bootstrap::tab_url( $tab );

        // Folder taxonomy
        } elseif ( $current_screen->id === 'edit-' . Folders::$taxonomy ) {
            $parent_file = $textdomain;
            $submenu_file = Bootstrap::tab_url( 'folders' );

        // Post Type Submenus
        } elseif ( isset( $current_screen->post_type ) && $current_screen->post_type === HelpDocs::$post_type ) {
            $parent_file = $textdomain;
            $submenu_file = Bootstrap::tab_url( 'manage' );
            
        } elseif ( isset( $current_screen->post_type ) && $current_screen->post_type === Imports::$post_type ) {
            $parent_file = $textdomain;
            $submenu_file = Bootstrap::tab_url( 'imports' );
        }
        
        return $parent_file;
    } // End submenus()


    /**
     * Add custom body class on our plugin pages
     *
     * @param string $classes Existing body classes.
     * @return string Modified body classes.
     */
    public function admin_body_class( $classes ) {
        $current_screen = get_current_screen();
        
        $helpdocs_screen_ids = [ 
            'toplevel_page_' . Bootstrap::textdomain(), 
            'toplevel_page_' . Bootstrap::textdomain() . '-network', 
            'edit-' . HelpDocs::$post_type, 
            'edit-' . Imports::$post_type,
            'edit-' . Folders::$taxonomy,
        ];

        if ( in_array( $current_screen->id, $helpdocs_screen_ids, true ) ) {
            $classes .= ' helpdocs-admin-screen';
        }

        return $classes;
    } // End admin_body_class()


    /**
     * Admin title filter
     *
     * @param string $title Current admin title.
     * @param string $page Current page slug.
     * 
     * @return string
     */
    public function admin_title( $title, $page ) {
        if ( $page === 'toplevel_page_' . Bootstrap::textdomain() || $page === 'toplevel_page_' . Bootstrap::textdomain() . '-network' ) {
            $current_tab = self::get_current_tab();
            $tab = self::tabs( $current_tab );
            if ( ! empty( $tab ) ) {
                return $tab[ 'label' ] . ' ‹ ' . Bootstrap::name();
            }
            return Bootstrap::name();
        }

        return $title;
    } // End admin_title()


    /**
     * Admin header action
     *
     * @return void
     */
    public function admin_header() {
        $current_screen = get_current_screen();
        if ( $current_screen->id === 'toplevel_page_' . Bootstrap::textdomain() || $current_screen->id === 'toplevel_page_' . Bootstrap::textdomain() . '-network' ) {
            include Bootstrap::path( 'inc/header.php' );
        }
    } // End admin_header()


    /**
     * Get the current page slug
     *
     * @return string
     */
    public static function get_current_page() : string {
        return isset( $_GET[ 'page' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'page' ] ) ) : ''; // phpcs:ignore
    } // End get_current_page()


    /**
     * Get the current tab slug
     *
     * @return string
     */
    public static function get_current_tab() : string {
        return isset( $_GET[ 'tab' ] ) ? sanitize_key( wp_unslash( $_GET[ 'tab' ] ) ) : ''; // phpcs:ignore
    } // End get_current_tab()


    /**
     * Render tab content
     *
     * @return void
     */
    public function render_tab() : void {
        $tabs = self::tabs();
        $current_tab_slug = self::get_current_tab();
        if ( ! $current_tab_slug || ! isset( $tabs[ $current_tab_slug ] ) || ( $current_tab_slug === 'support' && ! get_option( 'helpdocs_contact_form' ) ) ) {
            wp_safe_redirect( Bootstrap::tab_url( 'documentation' ) );
            exit;
        }
        ?>
        <div id="<?php echo esc_attr( Bootstrap::textdomain() ); ?>">

            <div class="tab-content">
                <?php
                foreach ( $tabs as $key => $tab ) {
                    if ( $current_tab_slug === $key ) {

                        if ( isset( $tab[ 'class' ] ) && class_exists( $tab[ 'class' ] ) ) {
                            ?>
                            <div id="<?php echo esc_attr( $key ); ?>">
                                <?php
                                $class_name = $tab[ 'class' ];
                                $instance   = $class_name::instance();
                                $instance->render_tab();
                                ?>
                            </div>
                            <?php
                        } else {
                            echo 'Tab content not available.';
                        }
                    }
                }
                ?>
            </div>

        </div>
        <?php
    } // End render_tab()


    /**
     * Enqueue admin assets
     *
     * @return void
     */
    public function enqueue_assets() : void {
        $text_domain = Bootstrap::textdomain();
        $script_version = Bootstrap::script_version();

        // All pages in admin area
        wp_enqueue_style( $text_domain . '-docs', Bootstrap::url( 'inc/css/docs.css' ), [], $script_version );
        
        $colors = Colors::get();
        if ( ! empty( $colors ) ) {
            $inline_css = ':root {';
            foreach ( $colors as $key => $color ) {
                $key = str_replace( '_', '-', $key );
                $inline_css .= "--helpdocs-color-{$key}: {$color};";
            }
            $inline_css .= '}';
            wp_add_inline_style( $text_domain . '-docs', $inline_css );
        }

        // Click to copy script
        wp_enqueue_script( $text_domain . '-click-to-copy', Bootstrap::url( 'inc/js/click-to-copy.js' ), [ 'jquery' ], $script_version, true );
        wp_localize_script( $text_domain . "-click-to-copy", "helpdocs_click_to_copy", [
            'copied_text' => __( 'Copied!', 'admin-help-docs' ),
        ] );

        // Limited pages in admin area
        $current_screen = get_current_screen();
        $is_helpdocs_screen = $current_screen->id === 'toplevel_page_' . $text_domain || $current_screen->id === 'toplevel_page_' . $text_domain . '-network';
        $is_post_type_list_screen = $current_screen->id === 'edit-' . HelpDocs::$post_type || $current_screen->id === 'edit-' . Imports::$post_type;
        $is_taxonomy_list_screen = $current_screen->id === 'edit-' . Folders::$taxonomy;
        $is_our_dashboard = $current_screen->id === 'dashboard_page_admin-help-dashboard';

        // Only on our dashboard
        if ( $is_our_dashboard ) {
            wp_enqueue_style( $text_domain . '-our-dashboard', Bootstrap::url( 'inc/css/our-dashboard.css' ), [], $script_version );
        }

        // Only on our plugin pages and related post type list screens
        if ( $is_helpdocs_screen || $is_post_type_list_screen || $is_taxonomy_list_screen ) {
            wp_enqueue_style( $text_domain . '-header', Bootstrap::url( 'inc/css/header.css' ), [], $script_version );
        }

        // Only on our tab pages
        if ( $is_helpdocs_screen ) {
            wp_enqueue_style( $text_domain . '-content', Bootstrap::url( 'inc/css/content.css' ), [], $script_version );
        }

        // Only on our post types and taxonomies list screens
        if ( $is_post_type_list_screen || $is_taxonomy_list_screen ) {
            wp_enqueue_style( $text_domain . '-pt-tax', Bootstrap::url( 'inc/css/pt-tax.css' ), [], $script_version );
        }

        // Only on our post types list screens
        if ( $is_post_type_list_screen ) {
            wp_enqueue_style( $text_domain . '-post-types', Bootstrap::url( 'inc/css/post-types.css' ), [], $script_version );
        }

        // Only on our taxonomy list screens
        if ( $is_taxonomy_list_screen ) {
            wp_enqueue_style( $text_domain . '-taxonomies', Bootstrap::url( 'inc/css/taxonomies.css' ), [], $script_version );
        }

        // Load tab-specific assets
        $current_tab = self::get_current_tab();
        if ( $is_helpdocs_screen && $current_tab ) {
            if ( file_exists( Bootstrap::path( "inc/tabs/js/{$current_tab}.js" ) ) ) {
                wp_enqueue_script( $text_domain . "-tab-{$current_tab}", Bootstrap::url( "inc/tabs/js/{$current_tab}.js" ), [ 'jquery' ], $script_version, true );
            }
            if ( file_exists( Bootstrap::path( "inc/tabs/css/{$current_tab}.css" ) ) ) {
                wp_enqueue_style( $text_domain . "-tab-{$current_tab}", Bootstrap::url( "inc/tabs/css/{$current_tab}.css" ), [], $script_version );
            }

            // Settings tab
            if ( $current_tab === 'settings' ) {
                $editor_settings = wp_enqueue_code_editor( [ 'type' => 'text/css' ] );
                wp_localize_script( $text_domain . "-tab-{$current_tab}", "helpdocs_{$current_tab}", [
                    'nonce'           => wp_create_nonce( "helpdocs_{$current_tab}_nonce" ),
                    'settings'        => Settings::setting_fields( true ),
                    'wp_version'      => get_bloginfo( 'version' ),
                    'themes'          => Colors::themes(),
                    'default_logo'    => Helpers::get_default_logo_url(),
                    'editor_settings' => $editor_settings,
                    'saving_text'     => __( 'Saving', 'admin-help-docs' ),
                    'saved_text'      => __( 'Settings saved successfully.', 'admin-help-docs' ),
                    'error_text'      => __( 'Error saving settings. Please try again.', 'admin-help-docs' ),
                    'flushing_text'   => __( 'Clearing Cache', 'admin-help-docs' ),
                ] );
            }

            // Admin Menu tab
            if ( $current_tab === 'admin-menu' ) {
                $current__tab = str_replace( '-', '_', $current_tab );
                wp_localize_script( $text_domain . "-tab-{$current_tab}", "helpdocs_{$current__tab}", [
                    'nonce'        => wp_create_nonce( "helpdocs_{$current__tab}_nonce" ),
                    'settings'     => AdminMenu::setting_fields( true ),
                ] );
            }

            // Documentation tab
            if ( $current_tab === 'documentation' ) {

                $main_docs_css = get_option( 'helpdocs_main_docs_css' );
                if ( ! empty( $main_docs_css ) ) {
                    wp_add_inline_style( $text_domain . "-tab-{$current_tab}", wp_strip_all_tags( $main_docs_css ) );
                }
                
                wp_localize_script( $text_domain . "-tab-{$current_tab}", "helpdocs_{$current_tab}", [
                    'nonce' => wp_create_nonce( "helpdocs_{$current_tab}_nonce" ),
                ] );
            }

            // Support tab
            if ( $current_tab === 'support' ) {
                wp_localize_script( $text_domain . "-tab-{$current_tab}", "helpdocs_{$current_tab}", [
                    'nonce'              => wp_create_nonce( "helpdocs_{$current_tab}_nonce" ),
                    'clear_logs_confirm' => __( 'Are you sure you want to delete all support logs?', 'admin-help-docs' ),
                    'max_attachment_mb'  => Support::$max_attachment_mb,
                    'files_too_large'    => sprintf( __( 'Total uploads exceed the maximum size of %dMB.', 'admin-help-docs' ), Support::$max_attachment_mb ),
                    'log_date_format'    => Support::$log_date_format,
                    'required_fields'    => __( 'Please fill in all required fields.', 'admin-help-docs' ),
                ] );
            }

            // Import tab
            if ( $current_tab === 'import' ) {
                wp_localize_script( $text_domain . "-tab-{$current_tab}", "helpdocs_{$current_tab}", [
                    'fetch_nonce'    => wp_create_nonce( "helpdocs_{$current_tab}_fetch_nonce" ),
                    'clone_nonce'    => wp_create_nonce( "helpdocs_{$current_tab}_clone_nonce" ),
                    'fetching_text' => __( 'Fetching', 'admin-help-docs' ),
                    'importing_text' => __( 'Cloning', 'admin-help-docs' ),
                    'imported_text'  => __( 'Copy Complete!', 'admin-help-docs' ),
                    'error_text'     => __( 'An error occurred during cloning.', 'admin-help-docs' ),
                    'saving_text'    => __( 'Saving', 'admin-help-docs' ),
                ] );
            }
        }
    } // End enqueue_assets()

    
    /**
     * Prevent cloning and unserializing
     */
    public function __clone() {}
    public function __wakeup() {}

}


Menu::instance();