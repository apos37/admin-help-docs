<?php
/**
 * Documentation Tab Loader
 */

namespace PluginRx\AdminHelpDocs;

if ( ! defined( 'ABSPATH' ) ) exit;

class Documentation {

    /**
     * The single instance of the class
     *
     * @var self|null
     */
    private static ?Documentation $instance = null;


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
        add_action( 'helpdocs_subheader_right', [ $this, 'render_search_box' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_styles' ] );
        add_action( 'wp_ajax_helpdocs_save_docs_order', [ $this, 'ajax_save_docs_order' ] );
        if ( get_option( 'helpdocs_curly_quotes' ) ) {
            add_action( 'current_screen', [ $this, 'remove_curly_quotes' ] );
        }
    } // End __construct()


    /**
     * Add a search box to the subheader on the folders page
     *
     * @param string $current_tab The current admin tab
     * @return void
     */
    public function render_search_box( string $current_tab ) {
        if ( $current_tab !== 'documentation' ) {
            return;
        }

        $search_value = sanitize_text_field( wp_unslash( $_GET[ 'search' ] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        ?>
        <div id="helpdocs-header-action-links" style="display: none;">
            <a id="expand-all" class="action-links" href="#"><?php echo esc_html__( 'Expand Folders', 'admin-help-docs' ); ?></a>
            <a id="collapse-all" class="action-links" href="#"><?php echo esc_html__( 'Collapse Folders', 'admin-help-docs' ); ?></a>
        </div>
        <form method="get" class="helpdocs-tax-search">
            <input type="hidden" name="page" value="<?php echo esc_attr( Bootstrap::textdomain() ); ?>">
            <input type="hidden" name="tab" value="documentation">
            <input type="search"
                name="search"
                value="<?php echo esc_attr( $search_value ); ?>"
                placeholder="<?php echo esc_attr__( 'Search Docs', 'admin-help-docs' ); ?>"
                class="helpdocs-search-input" />

            <input type="submit"
                class="helpdocs-button"
                value="<?php echo esc_attr__( 'Search', 'admin-help-docs' ); ?>" />

            <a href="<?php
                $clear_url = remove_query_arg( 'search' );
                echo esc_url( $clear_url );
            ?>" class="helpdocs-button">
                <?php echo esc_html__( 'Clear', 'admin-help-docs' ); ?>
            </a>
        </form>
        <?php
    } // End render_search_box()


    /**
     * Render the tab
     */
    public function render_tab() {
        $user_can_edit = Helpers::user_can_edit();

        $docs = Helpers::get_docs( [ 
            'site_location' => 'main', 
            'category'      => isset( $_GET[ 'cat' ] ) ? absint( $_GET[ 'cat' ] ) : 0, // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            'tag'           => isset( $_GET[ 'tag' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'tag' ] ) ) : '' // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        ] );

        $docs = array_filter( $docs, fn( $doc ) => Helpers::user_can_view( $doc->ID ) );
        if ( empty( $docs ) ) {
            ?>
            <br><br><br>
            <?php
            if ( $user_can_edit ) {
                ?>
                <em><?php esc_html_e( 'No documents found. Start by clicking "Add New" above!', 'admin-help-docs' ); ?></em>
                <?php
            } else {
                ?>
                <em><?php esc_html_e( 'No documents found.', 'admin-help-docs' ); ?></em>
                <?php
            }
            return;
        }

        $doc_ids = wp_list_pluck( $docs, 'ID' );

        // $docs_for_testing = array_map( function( $doc ) {
        //     return [
        //         'ID' => $doc->ID,
        //         'post_title' => $doc->post_title,
        //         'helpdocs_order' => $doc->helpdocs_order,
        //         // 'feed_id' => $doc->feed_id ?? '',
        //         // 'helpdocs_view_roles' => $doc->helpdocs_view_roles ?? [],
        //         // 'helpdocs_locations' => $doc->helpdocs_locations ?? [],
        //         // 'local_folder_id' => $doc->local_folder_id ?? null,
        //         // 'taxonomies' => $doc->taxonomies ?? (object)[]
        //     ];
        // }, $docs );
        // dpr( $docs_for_testing );

        $current_url = Bootstrap::tab_url( 'documentation' );

        if ( isset( $_GET[ 'search' ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $s = sanitize_text_field( wp_unslash( $_GET[ 'search' ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $current_url = add_query_arg( 'search', $s, $current_url );
        } else {
            $s = '';
        }

        $folder_taxonomy = Folders::$taxonomy;
        $folders = get_terms( [
            'taxonomy'   => $folder_taxonomy,
            'hide_empty' => false,
        ] );

        usort( $folders, function( $a, $b ) {
            $order_a = get_term_meta( $a->term_id, 'helpdocs_order', true );
            $order_b = get_term_meta( $b->term_id, 'helpdocs_order', true );

            $order_a_missing = ( $order_a === '' );
            $order_b_missing = ( $order_b === '' );

            if ( $order_a_missing && $order_b_missing ) {
                return strcasecmp( $a->name, $b->name );
            }

            if ( $order_a_missing ) {
                return 1;
            }

            if ( $order_b_missing ) {
                return -1;
            }

            return (int) $order_a - (int) $order_b;
        } );

        $mapped_folders = [];
        foreach ( $folders as $folder ) {
            $folder_id   = $folder->term_id;
            $folder_name = $folder->name;
            $folder_slug = $folder->slug;

            // 1. Get local docs assigned to this folder
            $folder_doc_args = [
                'post_type'      => HelpDocs::$post_type,
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                'tax_query'      => [
                    [
                        'taxonomy' => $folder_taxonomy,
                        'field'    => 'term_id',
                        'terms'    => $folder_id,
                    ]
                ],
                'fields'         => 'ids'
            ];
            $folder_docs = get_posts( $folder_doc_args );

            // 2. Identify and map imports
            foreach ( $docs as $doc ) {
                if ( ! str_starts_with( (string) $doc->ID, 'import_' ) ) {
                    continue;
                }

                $is_match = false;

                // Check if explicitly mapped via local_folder_id
                if ( isset( $doc->local_folder_id ) && absint( $doc->local_folder_id ) === $folder_id ) {
                    $is_match = true;
                } 
                // Fallback to taxonomy matching if local_folder_id isn't set
                elseif ( isset( $doc->taxonomies ) && ! empty( $doc->taxonomies->$folder_taxonomy ) ) {
                    foreach ( $doc->taxonomies->$folder_taxonomy as $term ) {
                        if ( sanitize_title( $term->slug ) === $folder_slug || sanitize_text_field( $term->name ) === $folder_name ) {
                            $is_match = true;
                            break;
                        }
                    }
                }

                if ( $is_match ) {
                    $folder_docs[] = $doc->ID;
                }
            }

            // Clean up and restrict to current viewable docs
            $folder_docs = array_intersect( $folder_docs, $doc_ids );

            if ( $user_can_edit || ! empty( $folder_docs ) ) {
                $mapped_folders[ $folder_id ] = [
                    'name' => $folder_name,
                    'docs' => $folder_docs
                ];
            }
        }

        $current_doc_id = false;
        $is_import = false;

        if ( isset( $_GET[ 'id' ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $current_doc_id = sanitize_key( $_GET[ 'id' ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $is_import = str_starts_with( $current_doc_id, 'import_' );
            if ( ! in_array( $current_doc_id, $doc_ids ) || ( ! $is_import && ! get_post_status( $current_doc_id ) ) ) {
                $current_doc_id = false;
            }
        }

        if ( ! $s && ! $current_doc_id ) {
            $default_doc_id = get_option( 'helpdocs_default_doc' );
            if ( $default_doc_id && 'publish' == get_post_status( $default_doc_id ) ) {
                $site_location = get_post_meta( $default_doc_id, 'helpdocs_site_location', true );
                if ( $site_location && $site_location == base64_encode( 'main' ) ) {
                    $current_doc_id = $default_doc_id;
                }
            } else {
                if ( ! empty( $docs ) ) {
                    $current_doc_id = $docs[0]->ID;
                }
            }
            if ( $current_doc_id ) {
                Helpers::add_qs_without_refresh( 'id', $current_doc_id );
            }
        }

        $current_doc = (Object)[];
        $feed = false;
        ?>
        <div id="helpdocs-sidebar" class="helpdocs-box">
            <ul id="helpdocs-docs-list">
            <?php

            $in_folders = [];
            if ( $s == '' && ! empty( $folders ) ) {
                foreach ( $mapped_folders as $folder_id => $folder ) {

                    if ( ! $user_can_edit && empty( $folder[ 'docs' ] ) ) {
                        continue;
                    }
                        
                    $folder_count = count( $folder[ 'docs' ] );                    

                    if ( $current_doc_id && in_array( $current_doc_id, $folder[ 'docs' ] ) ) {
                        $active_folder = ' active-folder';
                    } else {
                        $active_folder = ' hide-in-folder';
                    }

                    ?>
                    <li id="folder-<?php echo esc_attr( $folder_id ); ?>" class="helpdocs-folder<?php echo esc_attr( $active_folder ); ?>" data-type="folder" draggable="true" data-folder-id="<?php echo absint( $folder_id ); ?>">
                        <a href="#"><span class="folder-icon"></span> <?php echo esc_html( $folder[ 'name' ] ); ?> (<span class="folder-count"><?php echo absint( $folder_count ); ?></span>)</a>
                    </li>
                    <?php
                    
                    foreach ( $docs as $doc ) {

                        if ( in_array( $doc->ID, $folder[ 'docs' ] ) ) {
                            $in_folders[] = $doc->ID;
                        } else {
                            continue;
                        }

                        $incl_doc = true;
                        if ( $s !== '' ) {
                            
                            if ( strpos( strtolower( $doc->post_title ), strtolower( $s ) ) !== false ) {
                                $incl_doc = true;
                            } else if ( strpos( strtolower( $doc->post_content ), strtolower( $s ) ) !== false ) {
                                $incl_doc = true;
                            } else {
                                $incl_doc = false;
                            }
                        }

                        if ( ! $incl_doc ) {
                            continue;
                        }

                        if ( ! $current_doc_id ) {
                            $current_doc_id = $doc->ID;
                            Helpers::add_qs_without_refresh( 'id', $current_doc_id );
                        }

                        if ( $doc->ID == $current_doc_id ) {
                            $active = ' active';
                            $current_doc = $doc;
                        } else {
                            $active = '';
                        }

                        if ( isset( $doc->auto_feed ) && $doc->auto_feed != '' ) {
                            $incl_feed = '&feed=true';
                            $feed = $doc->ID;
                            $file_icon_class = 'file-import-icon';
                            $data_import = 'true';
                            $import_id = $doc->feed_id;
                        } else {
                            $incl_feed = '';
                            $file_icon_class = 'file-icon';
                            $data_import = 'false';
                            $import_id = '';
                        }

                        $item_url = add_query_arg( [
                            'id' => $doc->ID,
                        ], $current_url );
                        ?>
                        <li id="item-<?php echo esc_attr( $doc->ID ); ?>" class="helpdocs-sidebar-item <?php echo in_array( $doc->ID, $in_folders ) ? 'in-folder' : 'not-in-folder'; ?><?php echo esc_attr( $active ); ?>" draggable="true" data-type="item" data-item-id="<?php echo esc_attr( $doc->ID ); ?>" data-import="<?php echo esc_attr( $data_import ); ?>" data-folder-id="<?php echo esc_attr( $folder_id ?? 0 ); ?>" data-import-id="<?php echo esc_attr( $import_id ); ?>">
                            <a href="<?php echo esc_url( $item_url ); ?><?php echo esc_attr( $incl_feed ); ?>"><span class="<?php echo esc_attr( $file_icon_class ); ?>"></span> <span class="item-title"><?php echo esc_html( $doc->post_title ); ?></span></a>
                        </li>
                        <?php
                    }
                }
                if ( $user_can_edit || ! empty( $in_folders ) ) {
                    ?>
                    <li id="folder-0" class="invisible-folder" data-folder="0"></li>
                    <?php
                }
            }

            foreach ( $docs as $doc ) {

                $incl_doc = true;
                if ( $s !== '' ) {
                    
                    if ( strpos( strtolower( $doc->post_title ), strtolower( $s ) ) !== false ) {
                        $incl_doc = true;
                    } elseif ( strpos( strtolower( $doc->post_content ), strtolower( $s ) ) !== false ) {
                        $incl_doc = true;
                    } else {
                        $incl_doc = false;
                    }
                }

                if ( ! $incl_doc ) {
                    continue;
                }

                if ( $s == '' && in_array( $doc->ID, $in_folders ) ) {
                    continue;
                }

                if ( ! $current_doc_id ) {
                    $current_doc_id = $doc->ID;
                    Helpers::add_qs_without_refresh( 'id', $current_doc_id );
                }

                if ( $doc->ID == $current_doc_id ) {
                    $active = ' active';
                    $current_doc = $doc;
                } else {
                    $active = '';
                }

                if ( isset( $doc->auto_feed ) && $doc->auto_feed != '' ) {
                    $incl_feed = '&feed=true';
                    $feed = $doc->ID;
                    $file_icon_class = 'file-import-icon';
                    $data_import = 'true';
                    $import_id = $doc->feed_id;
                } else {
                    $incl_feed = '';
                    $file_icon_class = 'file-icon';
                    $data_import = 'false';
                    $import_id = '';
                }
                
                $item_url = add_query_arg( [
                    'id' => $doc->ID,
                ], $current_url );
                ?>
                <li id="item-<?php echo esc_attr( $doc->ID ); ?>" class="helpdocs-sidebar-item <?php echo in_array( $doc->ID, $in_folders ) ? 'in-folder' : 'not-in-folder'; ?><?php echo esc_attr( $active ); ?>" draggable="true" data-type="item" data-item-id="<?php echo esc_attr( $doc->ID ); ?>" data-import="<?php echo esc_attr( $data_import ); ?>" data-folder-id="0" data-import-id="<?php echo esc_attr( $import_id ); ?>">
                    <a href="<?php echo esc_url( $item_url ); ?><?php echo esc_attr( $incl_feed ); ?>"><span class="<?php echo esc_attr( $file_icon_class ); ?>"></span> <span class="item-title"><?php echo esc_html( $doc->post_title ); ?></span></a>
                </li>
                <?php
            }

            ?>
            </ul>
        </div>
        <?php
        $current_doc_as_array = (array)$current_doc;
        if ( ! empty( $current_doc_as_array ) ) {

            ?>
            <div id="helpdocs-document-viewer" class="helpdocs-box">
                <?php
                if ( ! get_option( 'helpdocs_hide_doc_meta' ) ) {

                    if ( $is_import ) {
                        $created_by = isset( $current_doc->created_by ) ? sanitize_text_field( $current_doc->created_by ) : __( 'an unknown author', 'admin-help-docs' );
                        $created_by .= ' from ' . esc_html( $current_doc->feed_id ? get_the_title( $current_doc->feed_id ) : __( 'an unknown source', 'admin-help-docs' ) );
                    } elseif ( is_numeric( $current_doc->post_author ) ) {
                        $created_by = get_userdata( $current_doc->post_author );
                        $created_by = $created_by->display_name;
                    } else {
                        $created_by = $current_doc->post_author;
                    }
                    $incl_created_by = __( 'Created: ', 'admin-help-docs' ) . date_i18n(
                        'F j, Y g:i A T',
                        strtotime( $current_doc->post_date )
                    ) . ' by ' . $created_by;

                    if ( $current_doc->_edit_last ) {

                        if ( is_numeric( $current_doc->_edit_last ) ) {
                            $modified_by = get_userdata( $current_doc->_edit_last );
                            $modified_by = $modified_by->display_name;
                        } else {
                            $modified_by = $current_doc->_edit_last;
                        }
                        
                        $incl_modified = '<br>' . __( 'Last modified: ', 'admin-help-docs' ) . date_i18n(
                            'F j, Y g:i A T',
                            strtotime( $current_doc->post_modified )
                        ) . ' by ' . esc_attr( $modified_by );
                    } else {
                        $incl_modified = '';
                    }
                } else {
                    $incl_created_by = '';
                    $incl_modified = '';
                }

                if ( Helpers::user_can_edit() ) {
                    if ( $feed == $current_doc_id ) {
                        $post_id = $current_doc->feed_id;
                    } else {
                        $post_id = $current_doc_id;
                    }
                    $edit_url = get_edit_post_link( $post_id );
                    $incl_edit = ' <span id="edit-link"><a href="' . esc_url( $edit_url ) . '">✎ ' . __( 'Edit', 'admin-help-docs' ) . '</a></span>';
                } else {
                    $incl_edit = '';
                }

                if ( $feed == $current_doc_id ) {
                    $incl_feed = '<br>' . __( 'Content feed: ', 'admin-help-docs' ) . $current_doc->auto_feed;
                } else {
                    $incl_feed = '';
                }

                if ( $s != '' ) {
                    $post_title = preg_replace( '/'.$s.'/i', '<span class="highlight">$0</span>', sanitize_text_field( $current_doc->post_title ) );
                } else {
                    $post_title = sanitize_text_field( $current_doc->post_title );
                }

                ?>
                <div id="helpdoc-header">
                    <h2><?php echo wp_kses_post( $post_title ); ?></h2><?php echo wp_kses_post( $incl_edit ); ?>
                    <span id="helpdocs-meta"><?php echo wp_kses_post( $incl_created_by ); ?>
                    <?php echo wp_kses_post( $incl_modified ); ?>
                    <?php echo wp_kses_post( $incl_feed ); ?></span>
                </div>
                <?php

                if ( $s != '' ) {
                    $post_content = str_replace( $s, '<span class="highlight">'.$s.'</span>', $current_doc->post_content );
                } else {
                    $post_content = $current_doc->post_content;
                }

                if ( get_option( 'helpdocs_auto_htoc' ) ) {
                    preg_match_all( '/<h([2-6])[^>]*>(.*?)<\/h\1>/i', $post_content, $matches, PREG_SET_ORDER );

                    if ( $matches ) {
                        $toc = '<div id="page-toc"><ul class="page-toc-list">';
                        $prev_level = 0;

                        foreach ( $matches as $match ) {
                            $level = intval( $match[1] );
                            $heading_text = wp_strip_all_tags( $match[2] );
                            $anchor = sanitize_title( $heading_text );

                            $post_content = preg_replace(
                                '/' . preg_quote( $match[0], '/' ) . '/',
                                '<h' . $level . ' id="' . $anchor . '">' . $heading_text . '</h' . $level . '>',
                                $post_content,
                                1 // Only replace first occurrence
                            );

                            if ( $prev_level && $level > $prev_level ) {
                                $toc .= str_repeat( '<ul>', $level - $prev_level );
                            } elseif ( $prev_level && $level < $prev_level ) {
                                $toc .= str_repeat( '</ul>', $prev_level - $level );
                            }

                            $toc .= '<li><a href="#' . $anchor . '">' . esc_html( rtrim( $heading_text, ':' ) ) . '</a></li>';
                            $prev_level = $level;
                        }

                        if ( $prev_level > 0 ) {
                            $toc .= str_repeat( '</ul>', $prev_level - 1 );
                        }

                        $toc .= '</ul></div>';

                        echo wp_kses_post( $toc );
                    }
                } else {
                    echo '<hr class="helpdocs-title-hr">';
                }

                // Highlight the content
                add_filter( 'wp_kses_allowed_html', [ __CLASS__, 'allow_addt_tags' ] );
                ?>
                <div id="helpdoc-content"><?php echo wp_kses_post( apply_filters( 'the_content', $post_content ) ); ?></div>
                <?php
                remove_filter( 'wp_kses_allowed_html', [ __CLASS__, 'allow_addt_tags' ] );

            ?>
            </div>
            <?php

        // Search with no results
        } else if ( $s !== '' ) {

            Helpers::remove_qs_without_refresh( 'id' );
            ?>
            <div id="no-docs-found" class="helpdocs-box"><?php echo sprintf( esc_html__( 'No docs found with the keyword "%s"... Please try again.', 'admin-help-docs' ), esc_html( $s ) ); ?></div>
            <?php
        
        // Otherwise redirect to page without doc id
        } else {
            // wp_safe_redirect( Bootstrap::tab_url( 'documentation' ) );
        }
    } // End render_tab()


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
            ],
        ] );

        return apply_filters( 'helpdocs_allowed_html', $tags );
    } // End allow_addt_tags()


    /**
     * Enqueue admin styles.
     */
    public function enqueue_admin_styles() {
        $text_domain = Bootstrap::textdomain();
        $current_screen = get_current_screen();
        $is_helpdocs_screen = $current_screen->id === 'toplevel_page_' . $text_domain || $current_screen->id === 'toplevel_page_' . $text_domain . '-network';
        $current_tab = Menu::get_current_tab();

        if ( $current_screen->id == HelpDocs::$post_type && $current_screen->base == 'post' && get_option( 'helpdocs_gutenberg_editor' ) ) {
            wp_enqueue_style(
                'wp-block-library-frontend',
                includes_url( 'css/dist/block-library/style.css' ),
                [],
                Bootstrap::script_version()
            );
        }

        if ( $is_helpdocs_screen && $current_tab === 'documentation' && get_option( 'helpdocs_enqueue_frontend_styles' ) ) {
            global $wp_styles;

            do_action( 'wp_enqueue_scripts' );

            $skip = [ 'wp-block-library', 'wp-admin', 'colors', 'dashicons' ];

            foreach ( $wp_styles->queue as $handle ) {
                if ( in_array( $handle, $skip, true ) ) {
                    continue;
                }

                $style = $wp_styles->registered[ $handle ] ?? null;

                if ( $style && ! wp_style_is( $handle, 'enqueued' ) ) {
                    wp_enqueue_style(
                        $handle,
                        $style->src,
                        $style->deps,
                        $style->ver
                    );
                }
            }

            wp_add_inline_style( 'wp-admin', 'html.wp-toolbar { padding-top: 0 !important; }' );
        }
    } // End enqueue_admin_styles()


    /**
     * AJAX handler to save the order of docs and folders
     */
    public function ajax_save_docs_order() {
        check_ajax_referer( 'helpdocs_documentation_nonce', 'nonce' );

        if ( ! Helpers::user_can_edit() ) {
            wp_send_json_error( __( 'Insufficient permissions.', 'admin-help-docs' ) );
        }

        // Get the arrays from wp_unslash before processing (sanitize below)
        $folders = isset( $_POST[ 'folders' ] ) ? (array) wp_unslash( $_POST[ 'folders' ] ) : []; // phpcs:ignore
        $items   = isset( $_POST[ 'items' ] ) ? (array) wp_unslash( $_POST[ 'items' ] ) : []; // phpcs:ignore

        // 1. Update Folder Order
        foreach ( $folders as $index => $folder_id ) {
            update_term_meta( absint( $folder_id ), 'helpdocs_order', $index + 1 );
        }

        // 2. Update Items (Files) Order and Taxonomy
        $import_customizations = [];

        foreach ( $items as $index => $item ) {
            $raw_id    = sanitize_text_field( $item[ 'id' ] );
            $folder_id = absint( $item[ 'folder' ] );
            $import_id = absint( $item[ 'import_id' ] );
            $new_order = $index + 1;

            // Imports
            if ( ! empty( $import_id ) ) {
                $remote_id = absint( str_replace( 'import_', '', $raw_id ) );
                $import_customizations[ $import_id ][ $remote_id ] = [
                    'order'  => $new_order,
                    'folder' => $folder_id
                ];

            // Local Docs
            } else {
                $item_id   = absint( $raw_id );
                $locations = get_post_meta( $item_id, 'helpdocs_locations', true );

                // Backward Compatibility: If locations is empty, migrate legacy meta
                if ( ! is_array( $locations ) || empty( $locations ) ) {
                    $meta = get_post_meta( $item_id );
                    
                    $locations = [
                        [
                            'site_location' => $meta[ 'helpdocs_site_location' ][ 0 ] ?? '',
                            'page_location' => $meta[ 'helpdocs_page_location' ][ 0 ] ?? '',
                            'custom'        => $meta[ 'helpdocs_custom' ][ 0 ] ?? '',
                            'addt_params'   => ! empty( $meta[ 'helpdocs_addt_params' ][ 0 ] ),
                            'post_types'    => isset( $meta[ 'helpdocs_post_types' ][ 0 ] ) ? Helpers::normalize_meta_array( $meta[ 'helpdocs_post_types' ][ 0 ] ) : [],
                            'order'         => $new_order, // Set the new order here
                            'toc'           => isset( $meta[ 'helpdocs_toc' ][ 0 ] ) ? filter_var( $meta[ 'helpdocs_toc' ][ 0 ], FILTER_VALIDATE_BOOLEAN ) : false,
                            'css_selector'  => $meta[ 'helpdocs_css_selector' ][ 0 ] ?? '',
                        ]
                    ];

                    // Cleanup legacy keys now that we've migrated
                    $legacy_keys = [ 'site_location', 'page_location', 'custom', 'addt_params', 'post_types', 'order', 'toc', 'css_selector' ];
                    foreach ( $legacy_keys as $key ) {
                        delete_post_meta( $item_id, 'helpdocs_' . $key );
                    }
                } else {
                    // New method: Update the 'main' location order (bWFpbg==)
                    foreach ( $locations as $key => $loc ) {
                        if ( isset( $loc[ 'site_location' ] ) && $loc[ 'site_location' ] === 'bWFpbg==' ) {
                            $locations[ $key ][ 'order' ] = $new_order;
                            break;
                        }
                    }
                }

                update_post_meta( $item_id, 'helpdocs_locations', $locations );

                // Update Taxonomy Association (Local only)
                wp_set_post_terms( $item_id, ( $folder_id > 0 ) ? [ $folder_id ] : [], Folders::$taxonomy );
            }
        }

        // 3. Save bundled import customizations
        foreach ( $import_customizations as $cpt_id => $data ) {
            $existing = get_post_meta( $cpt_id, 'helpdocs_local_customizations', true ) ?: [];
            $updated  = array_replace( (array) $existing, $data );
            update_post_meta( $cpt_id, 'helpdocs_local_customizations', $updated );
        }

        Helpers::flush_location_cache();

        wp_send_json_success();
    } // End ajax_save_docs_order()


    /**
     * Remove wptexturize filter to prevent curly quotes in documentation content
     */
    public function remove_curly_quotes() {
        $current_screen = get_current_screen();
        if ( ! is_admin() || ! $current_screen ) {
            return;
        }

        $text_domain = Bootstrap::textdomain();
        $is_helpdocs_screen = ( $current_screen->id === 'toplevel_page_' . $text_domain || $current_screen->id === 'toplevel_page_' . $text_domain . '-network' );
        $current_tab = Menu::get_current_tab();

        if ( $is_helpdocs_screen && 'documentation' === $current_tab ) {
            remove_filter( 'the_content', 'wptexturize' );
        }
    } // End remove_curly_quotes()

    
    /**
     * Prevent cloning and unserializing
     */
    public function __clone() {}
    public function __wakeup() {}

}


Documentation::instance();