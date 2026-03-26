<?php
/**
 * WordPress Dashboard
 */

namespace PluginRx\AdminHelpDocs;

if ( ! defined( 'ABSPATH' ) ) exit;

class WPDashboard {

    /**
     * The single instance of the class
     *
     * @var self|null
     */
    private static ?WPDashboard $instance = null;


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
        // Only proceed if we're on the dashboard and not already on our custom dashboard page
        $is_custom_dashboard = isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] === 'admin-help-dashboard'; // phpcs:ignore 
        if ( $is_custom_dashboard ) {
            return; 
        }

        // Add dashboard widgets for each doc with a site location of index.php
        add_action( 'wp_dashboard_setup', [ $this, 'doc_widgets' ] );

        // Table of Contents widget
        if ( get_option( 'helpdocs_dashboard_toc' ) ) {
            add_action( 'wp_dashboard_setup', [ $this, 'toc_widget' ] );
        }
    } // End __construct()


    /**
     * The single rendering point for the custom dashboard page
     */
    public static function render_replacement_page() : void {
        if ( ! get_option( 'helpdocs_replace_dashboard' ) ) {
            wp_safe_redirect( admin_url() );
            exit;
        }
        $docs = Helpers::get_docs( [
            'site_location' => 'replace_dashboard',
        ] );

        $doing_toc = false;
        ?>
        <div id="<?php echo esc_attr( Bootstrap::textdomain() ); ?>" class="wrap">
            <?php
            if ( get_option( 'helpdocs_dashboard_toc' ) && Helpers::user_can_view() ) {
                $doing_toc = true;
                ?>
                <div id="helpdocs-dashboard-toc">
                    <?php self::instance()->toc_widget_content(); ?>
                </div>
                <?php
            }
            ?>
            <div id="helpdocs-custom-dashboard">
                <?php if ( ! empty( $docs ) ) : ?>
                    <?php foreach ( $docs as $doc ) : ?>
                        <div class="helpdocs-box" data-doc-id="<?php echo esc_attr( $doc->ID ); ?>">
                            <?php
                            if ( Helpers::user_can_edit() ) {
                                $edit_url = get_edit_post_link( $doc->ID );
                                $incl_edit = ' <span class="edit-link"><a href="' . esc_url( $edit_url ) . '">✎ ' . __( 'Edit', 'admin-help-docs' ) . '</a></span>';
                            } else {
                                $incl_edit = '';
                            }
                            $post_content = $doc->post_content;
                            ?>
                            <div class="helpdoc-header">
                                <h2><?php echo wp_kses_post( Helpers::convert_merge_tags( $doc->post_title ) ); ?></h2><?php echo wp_kses_post( $incl_edit ); ?>
                            </div>
                            <div class="helpdoc-content"><?php 
                            $allowed_tags = wp_kses_allowed_html( 'post' );
                            $allowed_tags = Helpers::allow_addt_tags( $allowed_tags );
                            
                            echo wp_kses( apply_filters( 'the_content', Helpers::convert_merge_tags( $post_content ) ), $allowed_tags );
                            ?></div>
                        </div>
                    <?php endforeach; ?>
                    
                <?php elseif ( ! $doing_toc ) : ?>
                    <?php
                    /* translators: Welcome, name! */
                    $welcome_text = sprintf( __( 'Welcome, %s!', 'admin-help-docs' ), '{first_name}' );
                    ?>
                    <div id="dashboard-no-docs">
                        <h1><?php echo wp_kses_post( Helpers::convert_merge_tags( $welcome_text ) ); ?></h1>
                        <p><?php
                        $no_content_text = __( 'Everything you need is in the left menu.', 'admin-help-docs' );
                        echo wp_kses_post( apply_filters( 'helpdocs_dashboard_no_docs_text', $no_content_text ) );
                        if ( Helpers::user_can_edit() ) {
                            $create_doc_url = Bootstrap::tab_url( 'manage' );
                            echo '<br>';
                            printf(
                                wp_kses(
                                    /* translators: %1$s: URL to the Manage Docs screen. */
                                    __( 'To add content here, <a href="%1$s">create a doc</a> and set its location to "WordPress Dashboard (Replaces Dashboard Entirely)".', 'admin-help-docs' ),
                                    [ 'a' => [ 'href' => [] ] ]
                                ),
                                esc_url( $create_doc_url )
                            );
                        }
                        ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    } // End render_replacement_page()


    /**
     * Dashboard content callback
     *
     * @param WP_Post $post The post object for the widget
     */
    public function doc_widgets() {
        $docs = Helpers::get_docs( [
            'site_location' => 'index.php',
        ] );
        if ( ! empty( $docs ) ) {

            $user_can_edit = Helpers::user_can_edit();

            foreach ( $docs as $doc ) {
                if ( ! Helpers::user_can_view( $doc->ID ) ) {
                    continue;
                }

                if ( $user_can_edit ) {
                    if ( isset( $doc->feed_id ) && $doc->feed_id != '' ) {
                        $post_id = $doc->feed_id;
                    } else {
                        $post_id = $doc->ID;
                    }
                    $url = add_query_arg( [
                        'post'   => absint( $post_id ),
                        'action' => 'edit',
                    ], admin_url( 'post.php' ) );

                    $incl_edit = '<span class="helpdocs-dashboard-widget-link">
                        <a href="' . $url . '">✎ ' . __( 'Edit', 'admin-help-docs' ) . '</a>
                    </span>';
                } else {
                    $incl_edit = '';
                }

                $title = $doc->post_title . $incl_edit;

                wp_add_dashboard_widget( 'helpdocs_' . absint( $doc->ID ), $title, [ $this, 'doc_widget_content' ], null, $doc, 'normal', 'high' );
            }
        }
    } // End doc_widgets()


    /**
     * Dashboard content
     *
     * @return void
     */
    public function doc_widget_content( $var, $args ) {
        $doc = $args[ 'args' ];
        echo wp_kses_post( $doc->post_content );
    } // End doc_widget_content()


    /**
     * Table of Contents widget
     *
     * @return void
     */
    public function toc_widget() {
        $view_all_docs_link = '<span class="helpdocs-dashboard-widget-link">
            <a href="' . Bootstrap::tab_url( 'documentation' ) . '">' . __( 'View All Docs', 'admin-help-docs' ) . '</a>
        </span>';

        $title = Helpers::get_menu_title() . ' ' . $view_all_docs_link;

        if ( Helpers::user_can_view() ) {
            wp_add_dashboard_widget( Bootstrap::textdomain(), $title, [ $this, 'toc_widget_content' ], null, null, 'normal', 'high' );
        }
    } // End toc_widget()


    /**
     * Table of Contents content
     *
     * @return void
     */
    public function toc_widget_content() {
        $docs = Helpers::get_docs( [
            'site_location' => 'main',
            'toc'           => true,
        ] );

        if ( ! empty( $docs ) ) {
            usort( $docs, function( $a, $b ) { return strcmp( $a->helpdocs_order, $b->helpdocs_order ) ; } );

            $results = '<div class="toc-cont"><ul>';

                foreach ( $docs as $doc ) {
                    $toc_mk = 'helpdocs_toc';
                    if ( isset( $doc->$toc_mk ) && !$doc->$toc_mk ) {
                        continue;
                    }

                    $link_params = [ 'id'   => absint( $doc->ID ) ];

                    if ( isset( $doc->auto_feed ) && $doc->auto_feed != '' ) {
                        $link_params[ 'feed' ] = 'true';
                        $icon = 'dashicons-cloud';
                    } else {
                        $icon = Helpers::get_icon();
                    }
                    if ( ! str_starts_with( $icon, 'dashicons-' ) ) {
                        $icon = 'dashicons-' . $icon;
                    }
                    
                    $link = add_query_arg( $link_params, Bootstrap::tab_url( 'documentation' ) );
                    $title = $doc->post_title;

                    $results .= '<li><a class="toc-item" href="' . $link . '"><span class="dashicons ' . $icon . '"></span> ' . $title . '</a></li>';
                }

            $results .= '</ul></div>';
            echo wp_kses_post( $results );

        } else {
            echo esc_html__( 'You have not added any help docs. To do so, edit the docs you want to edit and choose "Add to Dashboard Table of Contents" under "Location" settings.', 'admin-help-docs' );
        }
    } // End toc_widget_content()

    
    /**
     * Prevent cloning and unserializing
     */
    public function __clone() {}
    public function __wakeup() {}

}


WPDashboard::instance();