<?php
/**
 * Migrate Tab Loader
 */

namespace PluginRx\AdminHelpDocs;

if ( ! defined( 'ABSPATH' ) ) exit;

class Migrate {

    /**
     * The single instance of the class
     *
     * @var self|null
     */
    private static ?Migrate $instance = null;


    /**
     * Get the singleton instance
     *
     * @return self
     */
    public static function instance() : self {
        return self::$instance ??= new self();
    } // End instance()


    /**
     * Migrate constructor.
     *
     * Private to enforce singleton pattern.
     */
    private function __construct() {

        add_action( 'wp_ajax_helpdocs_migrate_list_posts', [ $this, 'ajax_list_posts' ] );
        add_action( 'wp_ajax_helpdocs_migrate_import_posts', [ $this, 'ajax_import_posts' ] );
        add_action( 'wp_ajax_helpdocs_dismiss_migrate_notice', [ $this, 'ajax_dismiss_notice' ] );
        add_action( 'admin_notices', [ $this, 'render_migration_notices' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_notice_script' ] );

    } // End __construct()


    /**
     * Known source plugins eligible for a migration notice.
     *
     * Each entry:
     * - post_type    The source plugin's post type slug
     * - plugin_label The plugin's display name, used in the notice sentence
     * - doc_label    The label to use for this post type in the migrate dropdown
     * - pages        admin.php ?page= slugs where the notice should render
     * - screens      get_current_screen()->id values where the notice should render
     *
     * @return array
     */
    public static function known_sources() : array {
        $sources = [
            'wp-help' => [
                'post_type'      => 'wp-help',
                'plugin_label'   => __( 'WP Help', 'admin-help-docs' ),
                'doc_label'      => __( 'WP Help Documents', 'admin-help-docs' ),
                'pages'          => [ 'wp-help-documents' ],
                'screens'        => [ 'edit-wp-help' ],
                'notice_message' => __( 'WP Help has not been updated in a couple of years. You can migrate your existing docs to Admin Help Docs in a couple of clicks, no need to recreate them.', 'admin-help-docs' ),
            ],
        ];

        return apply_filters( 'helpdocs_migrate_known_sources', $sources );
    } // End known_sources()


    /**
     * Get post types eligible as a migration source (excludes our own types and non-public built-ins)
     *
     * @return array Associative array of post_type => label
     */
    public static function eligible_post_types() : array {
        $excluded = [
            HelpDocs::$post_type,
            Imports::$post_type,
            'attachment',
            'revision',
            'nav_menu_item',
            'wp_block',
            'wp_template',
            'wp_template_part',
            'wp_navigation',
            'wp_font_family',
            'wp_font_face',
            'wp_global_styles',
            'customize_changeset',
            'oembed_cache',
            'user_request',
        ];

        $label_overrides = [];
        foreach ( self::known_sources() as $source ) {
            if ( ! empty( $source[ 'doc_label' ] ) ) {
                $label_overrides[ $source[ 'post_type' ] ] = $source[ 'doc_label' ];
            }
        }

        $post_types = get_post_types( [ 'show_ui' => true ], 'objects' );
        $eligible   = [];

        foreach ( $post_types as $post_type ) {
            if ( in_array( $post_type->name, $excluded, true ) ) {
                continue;
            }
            $eligible[ $post_type->name ] = $label_overrides[ $post_type->name ] ?? $post_type->label;
        }

        return apply_filters( 'helpdocs_migrate_eligible_post_types', $eligible );
    } // End eligible_post_types()


    /**
     * Render migration notices for any known source plugin that's active and on a matching screen
     *
     * @return void
     */
    public function render_migration_notices() {
        $current_page = isset( $_GET[ 'page' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'page' ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $screen = get_current_screen();
        $screen_id = $screen ? $screen->id : '';

        foreach ( self::known_sources() as $key => $source ) {
            if ( ! post_type_exists( $source[ 'post_type' ] ) ) {
                continue;
            }

            $matches_page = in_array( $current_page, $source[ 'pages' ] ?? [], true );
            $matches_screen = in_array( $screen_id, $source[ 'screens' ] ?? [], true );

            if ( ! $matches_page && ! $matches_screen ) {
                continue;
            }

            if ( get_user_meta( get_current_user_id(), 'helpdocs_dismissed_migrate_notice_' . $key, true ) ) {
                continue;
            }

            $migrate_url = add_query_arg( [ 'source_post_type' => $source[ 'post_type' ] ], Bootstrap::tab_url( 'migrate' ) );

            $message = $source[ 'notice_message' ] ?? sprintf(
                /* translators: %s: plugin name */
                __( '%s docs can be migrated to Admin Help Docs in a couple of clicks, no need to recreate them.', 'admin-help-docs' ),
                $source[ 'plugin_label' ]
            );

            echo '<div class="notice notice-info is-dismissible helpdocs-migrate-notice" data-source-key="' . esc_attr( $key ) . '">
                <p>' . esc_html( $message ) . '</p>
                <p><a href="' . esc_url( $migrate_url ) . '" class="button button-primary">' . esc_html__( 'Migrate to Admin Help Docs', 'admin-help-docs' ) . '</a></p>
            </div>';
        }
    } // End render_migration_notices()


    /**
     * Enqueue the notice-dismiss script on screens where a migration notice may render
     *
     * @return void
     */
    public function enqueue_notice_script() {
        if ( ! self::current_screen_has_notice() ) {
            return;
        }

        $text_domain = Bootstrap::textdomain();
        $version = Bootstrap::script_version();

        wp_enqueue_style( $text_domain . '-migrate', Bootstrap::url( 'inc/tabs/css/migrate.css' ), [ $text_domain . '-docs' ], $version );
        wp_enqueue_script( $text_domain . '-migrate-notice', Bootstrap::url( 'inc/tabs/js/migrate-notice.js' ), [ 'jquery' ], $version, true );
        wp_localize_script( $text_domain . '-migrate-notice', 'helpdocs_migrate_notice', [
            'nonce' => wp_create_nonce( 'helpdocs_migrate_nonce' ),
        ] );
    } // End enqueue_notice_script()


    /**
     * Whether the current screen matches any known source's notice targets
     *
     * @return bool
     */
    public static function current_screen_has_notice() : bool {
        $current_page = isset( $_GET[ 'page' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'page' ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $screen = get_current_screen();
        $screen_id = $screen ? $screen->id : '';

        foreach ( self::known_sources() as $source ) {
            if ( ! post_type_exists( $source[ 'post_type' ] ) ) {
                continue;
            }

            if ( in_array( $current_page, $source[ 'pages' ] ?? [], true ) || in_array( $screen_id, $source[ 'screens' ] ?? [], true ) ) {
                return true;
            }
        }

        return false;
    } // End current_screen_has_notice()


    /**
     * AJAX: dismiss a migration notice for the current user
     *
     * @return void
     */
    public function ajax_dismiss_notice() {
        check_ajax_referer( 'helpdocs_migrate_nonce', 'nonce' );

        $key = isset( $_POST[ 'source_key' ] ) ? sanitize_key( wp_unslash( $_POST[ 'source_key' ] ) ) : '';

        if ( $key ) {
            update_user_meta( get_current_user_id(), 'helpdocs_dismissed_migrate_notice_' . $key, 1 );
        }

        wp_send_json_success();
    } // End ajax_dismiss_notice()


    /**
     * Render the tab
     */
    public function render_tab() {
        $text_domain = Bootstrap::textdomain();
        $version = Bootstrap::script_version();

        wp_enqueue_style( $text_domain . '-migrate', Bootstrap::url( 'inc/tabs/css/migrate.css' ), [ $text_domain . '-docs' ], $version );
        wp_enqueue_script( $text_domain . '-migrate', Bootstrap::url( 'inc/tabs/js/migrate.js' ), [ 'jquery' ], $version, true );
        wp_localize_script( $text_domain . '-migrate', 'helpdocs_migrate', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'helpdocs_migrate_nonce' ),
            'docs_url' => Bootstrap::tab_url( 'documentation' ),
            'i18n'     => [
                'no_posts'         => __( 'No posts found for this post type.', 'admin-help-docs' ),
                'already_migrated' => __( 'Migrated', 'admin-help-docs' ),
                'migrating'        => __( 'Migrating…', 'admin-help-docs' ),
                'loading'          => __( 'Loading posts…', 'admin-help-docs' ),
                'error'            => __( 'Something went wrong loading these posts. Please try again.', 'admin-help-docs' ),
                'done'             => __( 'Migration completed successfully. Migrated %migrated%, skipped %skipped%.', 'admin-help-docs' ),
                'go_to_docs'       => __( 'Go to Main Documentation Page!', 'admin-help-docs' ),
                'select_all'       => __( 'Select All', 'admin-help-docs' ),
                'deselect_all'     => __( 'Deselect All', 'admin-help-docs' ),
            ],
        ] );

        $preselect = isset( $_GET[ 'source_post_type' ] ) ? sanitize_key( wp_unslash( $_GET[ 'source_post_type' ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $post_types = self::eligible_post_types();
        ?>
        <div class="helpdocs-full-width-box" id="helpdocs-migrate-app" data-preselect="<?php echo esc_attr( $preselect ); ?>">
            <p><?php esc_html_e( 'Copy documents from another post type into Admin Help Docs. Originals are left untouched. Migrated docs are published to the Main Documentation Page and filed into a folder named after the source post type.', 'admin-help-docs' ); ?></p>

            <div class="helpdocs-migrate-source-row">
                <label for="helpdocs-migrate-source"><?php esc_html_e( 'Source Post Type', 'admin-help-docs' ); ?></label>
                <select id="helpdocs-migrate-source" <?php disabled( empty( $preselect ), false ); ?>>
                    <option value=""><?php esc_html_e( '— Select —', 'admin-help-docs' ); ?></option>
                    <?php foreach ( $post_types as $slug => $label ) : ?>
                        <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $preselect, $slug ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="helpdocs-migrate-list">
                <?php if ( $preselect ) : ?>
                    <div class="spinner-row"><span class="spinner is-active" style="float:none;"></span> <?php esc_html_e( 'Loading posts…', 'admin-help-docs' ); ?></div>
                <?php endif; ?>
            </div>

            <div id="helpdocs-migrate-actions-row" style="display:none;">
                <button type="button" class="helpdocs-button button-secondary" id="helpdocs-migrate-select-all"><?php esc_html_e( 'Select All', 'admin-help-docs' ); ?></button>
                <button type="button" class="helpdocs-button" id="helpdocs-migrate-submit" disabled><?php esc_html_e( 'Migrate Selected', 'admin-help-docs' ); ?></button>
                <span id="helpdocs-migrate-status"></span>
            </div>
        </div>
        <?php
    } // End render_tab()


    /**
     * AJAX: list posts of the chosen source post type
     *
     * @return void
     */
    public function ajax_list_posts() {
        check_ajax_referer( 'helpdocs_migrate_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( [ 'message' => __( 'Insufficient permissions.', 'admin-help-docs' ) ] );
        }

        $source_post_type = isset( $_POST[ 'source_post_type' ] ) ? sanitize_key( wp_unslash( $_POST[ 'source_post_type' ] ) ) : '';

        if ( ! $source_post_type || ! array_key_exists( $source_post_type, self::eligible_post_types() ) ) {
            wp_send_json_error( [ 'message' => __( 'Invalid post type.', 'admin-help-docs' ) ] );
        }

        $source_posts = get_posts( [
            'post_type'      => $source_post_type,
            'post_status'    => [ 'publish', 'draft' ],
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ] );

        $already_migrated = self::get_imported_source_ids( $source_post_type );

        $rows = [];
        foreach ( $source_posts as $source_post ) {
            $rows[] = [
                'id'       => $source_post->ID,
                'title'    => get_the_title( $source_post ),
                'imported' => in_array( $source_post->ID, $already_migrated, true ),
            ];
        }

        wp_send_json_success( [ 'posts' => $rows ] );
    } // End ajax_list_posts()


    /**
     * AJAX: import the selected posts into Admin Help Docs
     *
     * @return void
     */
    public function ajax_import_posts() {
        check_ajax_referer( 'helpdocs_migrate_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( [ 'message' => __( 'Insufficient permissions.', 'admin-help-docs' ) ] );
        }

        $source_post_type = isset( $_POST[ 'source_post_type' ] ) ? sanitize_key( wp_unslash( $_POST[ 'source_post_type' ] ) ) : '';
        $source_ids = isset( $_POST[ 'post_ids' ] ) ? array_map( 'absint', (array) wp_unslash( $_POST[ 'post_ids' ] ) ) : [];
        $eligible = self::eligible_post_types();

        if ( ! $source_post_type || ! array_key_exists( $source_post_type, $eligible ) || empty( $source_ids ) ) {
            wp_send_json_error( [ 'message' => __( 'Nothing to import.', 'admin-help-docs' ) ] );
        }

        $folder_term_id = self::get_or_create_folder_term( $eligible[ $source_post_type ] );

        global $wpdb;

        $imported = 0;
        $skipped  = 0;
        $last_id  = 0;

        foreach ( $source_ids as $source_id ) {
            $source_post = get_post( $source_id );

            if ( ! $source_post || $source_post_type !== $source_post->post_type ) {
                $skipped++;
                continue;
            }

            if ( self::already_imported( $source_post_type, $source_id ) ) {
                $skipped++;
                continue;
            }

            $new_id = wp_insert_post( [
                'post_type'    => HelpDocs::$post_type,
                'post_title'   => $source_post->post_title,
                'post_content' => wp_kses_post( $source_post->post_content ),
                'post_excerpt' => $source_post->post_excerpt,
                'post_status'  => 'publish',
            ], true );

            if ( is_wp_error( $new_id ) ) {
                $skipped++;
                continue;
            }

            $wpdb->update(
                $wpdb->posts,
                [
                    'post_date'         => $source_post->post_date,
                    'post_date_gmt'     => $source_post->post_date_gmt,
                    'post_modified'     => $source_post->post_modified,
                    'post_modified_gmt' => $source_post->post_modified_gmt,
                ],
                [ 'ID' => $new_id ]
            );
            clean_post_cache( $new_id );

            update_post_meta( $new_id, 'helpdocs_locations', [
                [
                    'site_location' => base64_encode( 'main' ),
                    'page_location' => '',
                    'custom'        => '',
                    'post_types'    => [],
                    'order'         => '',
                    'css_selector'  => '',
                ],
            ] );

            if ( $folder_term_id ) {
                wp_set_object_terms( $new_id, [ $folder_term_id ], Folders::$taxonomy, false );
            }

            update_post_meta( $new_id, 'helpdocs_migrated_from', $source_post_type );
            update_post_meta( $new_id, 'helpdocs_migrated_at', current_time( 'mysql' ) );
            update_post_meta( $new_id, 'helpdocs_migrated_from_id', $source_id );

            do_action( 'helpdocs_migrate_post_imported', $new_id, $source_post );

            $last_id = $new_id;
            $imported++;
        }

        wp_send_json_success( [
            'imported' => $imported,
            'skipped'  => $skipped,
            'last_id'  => $last_id,
        ] );
    } // End ajax_import_posts()


    /**
     * Check whether a given source post has already been migrated
     *
     * @param string $source_post_type
     * @param int $source_id
     * @return bool
     */
    public static function already_imported( $source_post_type, $source_id ) : bool {
        return in_array( absint( $source_id ), self::get_imported_source_ids( $source_post_type ), true );
    } // End already_imported()


    /**
     * Get the source IDs already migrated for a given source post type
     *
     * @param string $source_post_type
     * @return array
     */
    public static function get_imported_source_ids( $source_post_type ) : array {
        global $wpdb;

        $ids = $wpdb->get_col( $wpdb->prepare(
            "SELECT pm2.meta_value FROM {$wpdb->postmeta} pm1
             INNER JOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id
             WHERE pm1.meta_key = %s AND pm1.meta_value = %s AND pm2.meta_key = %s",
            'helpdocs_migrated_from',
            $source_post_type,
            'helpdocs_migrated_from_id'
        ) );

        return array_map( 'absint', $ids );
    } // End get_imported_source_ids()


    /**
     * Get the folder term for a source post type's label, creating it if it doesn't exist
     *
     * @param string $folder_name
     * @return int|null Term ID, or null on failure
     */
    public static function get_or_create_folder_term( $folder_name ) {
        $existing = get_term_by( 'name', $folder_name, Folders::$taxonomy );

        if ( $existing ) {
            return $existing->term_id;
        }

        $created = wp_insert_term( $folder_name, Folders::$taxonomy );

        if ( is_wp_error( $created ) ) {
            return null;
        }

        return $created[ 'term_id' ];
    } // End get_or_create_folder_term()

}

Migrate::instance();