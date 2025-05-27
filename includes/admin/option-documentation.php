<?php
// Get the colors
$HELPDOCS_COLORS = new HELPDOCS_COLORS();
$color_bg = $HELPDOCS_COLORS->get( 'bg' );
$color_ti = $HELPDOCS_COLORS->get( 'ti' );
?>

<style>
h2 { font-size: 1.5rem; }
h3 { font-size: 1.4rem; }
h4 { font-size: 1.3rem; }

#documentation {
    display: flex;
    border-top: 1px solid #ccc;
    margin-top: 26px;
}
#doc-toc {
    width: 14rem;
    border-right: 1px solid #ccc;
}
#draggable-items {
    width: 100% !important;
    list-style: none;
    padding: 0 !important;
    margin: 0 !important;
}
#draggable-items li {
    margin: 0 !important;
}
.toc-folder {
    padding: 0;
    border-top: 1px solid #ccc;
    border-bottom: 1px solid #ccc;
    border-left: 1px solid #ccc;
    box-shadow: 0px 3px 5px #ddd;
    font-weight: bold;
    z-index: 99;
}
#wpbody-content .toc-folder a {
    color: <?php echo esc_attr( $color_ti ); ?> !important;
    background-color: <?php echo esc_attr( $color_bg ); ?> !important;
    filter: brightness(95%);
}
#draggable-items li.toc-item.in-folder {
    margin-left: 5px !important;
    background-color: <?php echo esc_attr( $color_bg ); ?> !important;
    filter: brightness(97%);
    border-left: 1px solid #ccc;
}
.toc-item.hide-in-folder {
    display: none;
}
.toc-item {
    padding: 0;
}
.toc-item:not(:first-child) {
    border-top: 1px solid #ccc;
}
.toc-item:last-child {
    border-bottom: 1px solid #ccc;
}
.toc-folder a,
.toc-item a {
    display: block;
    padding: 10px;
    text-decoration: none;
}
.toc-item .item-title {
    text-decoration: underline;
}
.toc-item.active {
    font-weight: bold;
}
.invisible-folder {
    border-bottom: 2px double #ccc;
}
.hide-in-folder .folder-icon:before {
    content: "\1F4C1";
}
.active-folder .folder-icon:before {
    content: "\1F4C2";
}
.file-icon:before {
    content: "\1F4C4";
}
.file-import-icon:before {
    content: "\1F4F0";
}
#doc-viewer {
    flex: 1 0 auto;
    padding: 2rem;
    max-width: calc( 100% - 18rem );
}
#doc-header {
    margin-bottom: 2rem;
}
#doc-header h2 {
    color: <?php echo esc_attr( $color_ti ); ?>;
    font-size: 2rem;
    margin-bottom: 1.5rem;
    display: inline;
    line-height: 1.2;
}
#edit-link {
    margin-left: 1rem;
    display: inline-block;
}
#doc-meta {
    display: block;
    margin-top: 1rem;
    font-style: italic;
}
ul {
    list-style: square;
    padding: revert;
}
ul li {
    padding-inline-start: 1ch;
}
ul, ol {
    padding-top: 10px;
    padding-bottom: 5px;
}
ol li ol { list-style-type: lower-alpha !important; }
ol li ol li ol { list-style-type: lower-roman !important; }
ol li ol li ol li ol { list-style-type: decimal !important; }
ol li ol li ol li ol li ol { list-style-type: lower-alpha !important; }
ol li ol li ol li ol li ol li ol { list-style-type: lower-roman !important; }
ol li ol li ol li ol li ol li ol li ol { list-style-type: decimal !important; }
ol li ol li ol li ol li ol li ol li ol li ol { list-style-type: lower-alpha !important; }
ol li ol li ol li ol li ol li ol li ol li ol li ol { list-style-type: lower-roman !important; }

#doc-viewer img {
    max-width:100%;
    height: auto;
    object-fit: contain;
}
#search-bar {
    float: right;
    margin-right: 20px;
    margin-top: -7px;
}
#no-docs-found {
    padding: 3rem;
    font-style: italic;
}
.<?php echo esc_attr( HELPDOCS_GO_PF ); ?>form_sending {
    line-height: 2.25;
    font-style: italic;
    margin-left: 10px;
    display: none;
}
.<?php echo esc_attr( HELPDOCS_GO_PF ); ?>form_sending:after {
    display: inline-block;
    animation: dotty steps(1,end) 1s infinite;
    content: '';
}
@keyframes dotty {
    0%   { content: ''; }
    25%  { content: '.'; }
    50%  { content: '..'; }
    75%  { content: '...'; }
    100% { content: ''; }
}
.<?php echo esc_attr( HELPDOCS_GO_PF ); ?>form_result {
    color: white;
    font-weight: 500;
    width: fit-content;
    border-radius: 4px;
    padding: 6px 10px;
}
.<?php echo esc_attr( HELPDOCS_GO_PF ); ?>form_result.success {
    background-color: green;
    display: inline-block;
    margin-left: 10px;
}
.<?php echo esc_attr( HELPDOCS_GO_PF ); ?>form_result.fail {
    background-color: red;
    margin-top: 10px;
}
.action-links {
    display: inline-block;
    margin-right: 10px;
}
.highlight {
    background: yellow;
}
#helpdocs-alert-imports {
    display: none;
    position: fixed;
    bottom: 3rem;
    right: 2rem;
    background: red;
    color: white;
    padding: 20px;
    border-radius: 10px;
    border: 2px solid black;
    box-shadow: 4px 4px 16px;
    font-weight: 600;
    font-size: medium;
}
#helpdocs-alert-imports .close {
    position: absolute;
    top: -5px;
    right: -5px;
    background: white;
    border: 2px solid black;
    color: black !important;
    font-weight: bold;
    border-radius: 50%;
    padding: 0 5px;
    font-size: 10px;
    text-decoration: none;
}
.extra-bracket {
    display: none;
}
#page-toc {
    margin: 0 0 3rem 0;
    background: #f9f9f9;
    border: 1px solid #ddd;
    padding: 1rem 1.25rem;
    border-radius: 8px;
    font-family: sans-serif;
    font-size: 0.95rem;
    width: fit-content;
}

#page-toc::before {
    content: "On this page";
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #333;
    font-size: 1rem;
}
#page-toc ul,
#page-toc ol {
    margin-bottom: 0 !important;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
}
</style>

<?php include 'header-page.php'; ?>

<?php
// Get the current url
$current_url = helpdocs_plugin_options_path( 'documentation' );

// Post type
$post_type = 'help-docs';

// Start the args to get the docs
$args = [
    'posts_per_page'    => -1,
    'post_status'       => 'publish',
    'post_type'         => $post_type,
    'meta_key'		    => HELPDOCS_GO_PF.'site_location', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
    'meta_value'	    => base64_encode( 'main' ),        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
    'meta_compare'	    => '=',
];

// Are we filtering by category? // Must be category id
if ( $cat = absint( helpdocs_get( 'cat' ) ) ) {
    $args[ 'category' ] = absint( $cat );
}

// Are we filtering by tag? // Must be slug
if ( $tag = sanitize_text_field( helpdocs_get( 'tag' ) ) ) {
    $args[ 'tag' ] = $tag;
}

// Get the posts
$docs = get_posts( $args );

// Also get the imports
$imports = helpdocs_get_imports( $args );

// Merge them together
if ( !empty( $imports ) ) {
    $docs = array_merge( $docs, $imports );
}

// Stop if no posts are found
if ( !$docs ) {
    echo '<br><br><br><em>No documents found. Start by clicking "Add New" above!</em>';
    return;
}

// First we sort by the doc order
usort( $docs, function( $a, $b ) {
    return $a->helpdocs_order - $b->helpdocs_order;
} );

// Are we searching for something?
if ( helpdocs_get( 'search' ) ) {
    $s = sanitize_text_field( helpdocs_get( 'search' ) );
    $current_url = add_query_arg( 'search', $s, $current_url );
} else {
    $s = '';
}

// Get the folders
$folders = get_terms( [
    'taxonomy'   => 'help-docs-folder',
    'hide_empty' => false,
    'orderby'    => 'meta_value_num',
    'order'      => 'ASC',
    'meta_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
        [ 
            'key'    => HELPDOCS_GO_PF.'order',
            'type'   => 'NUMERIC',
        ]
    ],
] );

// Add Expand and Collapse links, only if folders exist
if ( !empty( $folders ) ) {
    $folder_action_links = '<a id="expand-all" class="action-links" href="#">Expand Folders</a>
    <a id="collapse-all" class="action-links" href="#">Collapse Folders</a>';
} else {
    $folder_action_links = '';
}

// Search bar
echo '<div id="search-bar">
    <form>
        '.wp_kses_post( $folder_action_links ).'
        <a id="view-all" class="action-links" href="'.esc_url( remove_query_arg( 'search', $current_url ) ).'">View All</a>
        <input type="text" id="search-input" class="normal-text-input" name="search" value="'.esc_html( $s ).'" aria-label="Enter Keyword">
        <input type="hidden" name="page" value="'.esc_attr( HELPDOCS_TEXTDOMAIN ).'">
        <input type="hidden" name="tab" value="documentation">
        <input type="submit" class="button" value="Search Docs">
    </form>
</div>';

// Check if we are viewing a doc
if ( helpdocs_get( 'id' ) ) {
    $current_doc_id = absint( helpdocs_get( 'id' ) );
} elseif ( $s !== '' ) {
    $current_doc_id = false;
} else {
    // Check if we have a default
    $default_doc_id = get_option( HELPDOCS_GO_PF.'default_doc' );
    if ( 'publish' == get_post_status( $default_doc_id ) ) {
        if ( get_post_meta( $default_doc_id, HELPDOCS_GO_PF.'site_location', true ) && get_post_meta( $default_doc_id, HELPDOCS_GO_PF.'site_location', true ) == base64_encode( 'main' ) ) {
            $current_doc_id = $default_doc_id;
        } else {
            $current_doc_id = false;
        }
    } else {
        $current_doc_id = $docs[0]->ID;
    }
    if ( $current_doc_id ) {
        helpdocs_add_qs_without_refresh( 'id', $current_doc_id );
    }
}

// Store the current doc here
$current_doc = (Object)[];
$feed = false;

// Icons
$file_icon = '1F4C4';

// Start the full page container
echo '<div id="documentation">';


    /**
     * Let's add a table of contents
     */

    // Create a nonce
    $nonce = wp_create_nonce( 'drag-doc-toc' );

    // Start the toc container
    echo '<div id="doc-toc">
        <ul id="draggable-items" data-nonce="'.esc_attr( $nonce ).'">';

        // Store which docs are in folders
        $in_folders = [];

        // Organize in folders if we are not searching
        if ( $s == '' && !empty( $folders ) ) {

            // Iter the folders first
            foreach ( $folders as $folder ) {
                    
                // Get the folder id
                $folder_id = $folder->term_id;

                // Get docs in this folder
                $folder_doc_args = [
                    'post_type'      => 'help-docs',
                    'posts_per_page' => -1,
                    'post_status'    => 'publish',
                    'tax_query'      => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
                        [
                            'taxonomy' => 'help-docs-folder',
                            'field'    => 'term_id',
                            'terms'    => $folder_id
                        ]
                    ],
                    'fields'    => 'ids'
                ];
                $folder_docs = get_posts( $folder_doc_args );

                // Active folder
                if ( $current_doc_id && in_array( $current_doc_id, $folder_docs ) ) {
                    $active_folder = ' active-folder';
                } else {
                    $active_folder = ' hide-in-folder';
                }

                // Add the folder
                echo '<li id="folder-'.absint( $folder_id ).'" class="toc-folder'.esc_attr( $active_folder ).'" data-folder="'.absint( $folder_id ).'"><a href="javascript:void(0);"><span class="folder-icon"></span> '.esc_html( $folder->name ).' (<span class="folder-count">'.absint( $folder->count ).'</span>)</a></li> ';

                // Loop through each doc
                foreach ( $docs as $doc ) {

                    // Only keep it if this doc belongs to this folder
                    if ( in_array( $doc->ID, $folder_docs ) ) {
                        $in_folders[] = $doc->ID;
                    } else {
                        continue;
                    }

                    // Are we going to include this doc?
                    $incl_doc = true;

                    // Check if we are searching
                    if ( $s !== '' ) {
                        
                        // Check for a keyword in the title
                        if ( strpos( strtolower( $doc->post_title ), strtolower( $s ) ) !== false ) {
                            $incl_doc = true;
                        } else if ( strpos( strtolower( $doc->post_content ), strtolower( $s ) ) !== false ) {
                            $incl_doc = true;
                        } else {
                            $incl_doc = false;
                        }
                    }

                    // Continue?
                    if ( !$incl_doc ) {
                        continue;
                    }

                    // Set the current doc if one isn't already set
                    if ( !$current_doc_id ) {
                        $current_doc_id = $doc->ID;
                        helpdocs_add_qs_without_refresh( 'id', $current_doc_id );
                    }

                    // Active
                    if ( $doc->ID == $current_doc_id ) {
                        $active = ' active';
                        $current_doc = $doc;
                    } else {
                        $active = '';
                    }

                    // If imported
                    if ( isset( $doc->auto_feed ) && $doc->auto_feed != '' ) {
                        $incl_feed = '&feed=true';
                        $feed = $doc->ID;
                        $file_icon_class = 'file-import-icon';
                        $data_import = 'true';
                    } else {
                        $incl_feed = '';
                        $file_icon_class = 'file-icon';
                        $data_import = 'false';
                    }

                    // Add the item
                    echo '<li id="item-'.absint( $doc->ID ).'" class="toc-item in-folder'.esc_attr( $active_folder.$active ).'" data-import="'.esc_attr( $data_import ).'" data-folder="'.absint( $folder_id ).'"><a href="'.esc_url( $current_url ).'&id='.absint( $doc->ID ).esc_attr( $incl_feed ).'"><span class="'.esc_attr( $file_icon_class ).'"></span> <span class="item-title">'.esc_html( $doc->post_title ).'</span></a></li> ';
                }
            }

            // Add an uncategorized folder
            echo '<li id="folder-0" class="invisible-folder" data-folder="0"></li> ';
        }

        // Add docs not in folders now
        foreach ( $docs as $doc ) {

            // Are we going to include this doc?
            $incl_doc = true;

            // Check if we are searching
            if ( $s !== '' ) {
                
                // Check for a keyword in the title
                if ( strpos( strtolower( $doc->post_title ), strtolower( $s ) ) !== false ) {
                    $incl_doc = true;
                } elseif ( strpos( strtolower( $doc->post_content ), strtolower( $s ) ) !== false ) {
                    $incl_doc = true;
                } else {
                    $incl_doc = false;
                }
            }

            // Continue?
            if ( !$incl_doc ) {
                continue;
            }

            // Only keep it if this doc belongs to this folder
            if ( $s == '' && in_array( $doc->ID, $in_folders ) ) {
                continue;
            }

            // Set the current doc if one isn't already set
            if ( !$current_doc_id ) {
                $current_doc_id = $doc->ID;
                helpdocs_add_qs_without_refresh( 'id', $current_doc_id );
            }

            // Active
            if ( $doc->ID == $current_doc_id ) {
                $active = ' active';
                $current_doc = $doc;
            } else {
                $active = '';
            }

            // If imported
            if ( isset( $doc->auto_feed ) && $doc->auto_feed != '' ) {
                $incl_feed = '&feed=true';
                $feed = $doc->ID;
                $file_icon_class = 'file-import-icon';
                $data_import = 'true';
            } else {
                $incl_feed = '';
                $file_icon_class = 'file-icon';
                $data_import = 'false';
            }

            // Add the item
            echo '<li id="item-'.absint( $doc->ID ).'" class="toc-item not-in-folder'.esc_attr( $active ).'" data-import="'.esc_attr( $data_import ).'" data-folder="0"><a href="'.esc_url( $current_url ).'&id='.absint( $doc->ID ).esc_attr( $incl_feed ).'"><span class="'.esc_attr( $file_icon_class ).'"></span> <span class="item-title">'.esc_html( $doc->post_title ).'</span></a></li> ';
        }

    // End the toc container
    echo '</ul>
    </div>';


    /**
     * Now load the document in the viewer
     */

    // Make sure the current doc is set
    
    $current_doc_as_array = (array)$current_doc;
    if ( !empty( $current_doc_as_array ) ) {

        // Start the toc container
        echo '<div id="doc-viewer">';

            // Are we including doc meta?
            if ( !get_option( HELPDOCS_GO_PF.'hide_doc_meta' ) ) {

                // Get the author
                if ( is_numeric( $current_doc->post_author ) ) {
                    $created_by = get_userdata( $current_doc->post_author );
                    $created_by = $created_by->display_name;
                } else {
                    $created_by = $current_doc->post_author;
                }
                $incl_created_by = 'Created: '. helpdocs_convert_timezone( $current_doc->post_date ).' by '.$created_by;

                // Get the modified by
                if ( $current_doc->_edit_last ) {

                    // Modified by
                    if ( is_numeric( $current_doc->_edit_last ) ) {
                        $modified_by = get_userdata( $current_doc->_edit_last );
                        $modified_by = $modified_by->display_name;
                    } else {
                        $modified_by = $current_doc->_edit_last;
                    }
                    
                    $incl_modified = '<br>Last modified: '.helpdocs_convert_timezone( $current_doc->post_modified ).' by '.esc_attr( $modified_by );
                } else {
                    $incl_modified = '';
                }
            } else {
                $incl_created_by = '';
                $incl_modified = '';
            }

            // The edit link
            if ( helpdocs_user_can_edit() ) {
                if ( $feed == $current_doc_id ) {
                    $post_id = $current_doc->feed_id;
                } else {
                    $post_id = $current_doc_id;
                }
                $incl_edit = ' <span id="edit-link">[<a href="/'.esc_attr( HELPDOCS_ADMIN_URL ).'/post.php?post='.absint( $post_id ).'&action=edit">edit</a>]</span>';
            } else {
                $incl_edit = '';
            }

            // If imported, say so
            if ( $feed == $current_doc_id ) {
                $incl_feed = '<br>Content feed: '.$current_doc->auto_feed;
            } else {
                $incl_feed = '';
            }

            // Highlight the title
            if ( $s != '' ) {
                $post_title = preg_replace( '/'.$s.'/i', '<span class="highlight">$0</span>', sanitize_text_field( $current_doc->post_title ) );
            } else {
                $post_title = sanitize_text_field( $current_doc->post_title );
            }

            // Add the header
            echo '<div id="doc-header">
                <h2>'.wp_kses_post( $post_title ).'</h2>'.wp_kses_post( $incl_edit ).'
                <span id="doc-meta">'.wp_kses_post( $incl_created_by ).'
                '.wp_kses_post( $incl_modified ).'
                '.wp_kses_post( $incl_feed ).'</span>
            </div>';

            // Get the content
            if ( $s != '' ) {
                $post_content = str_replace( $s, '<span class="highlight">'.$s.'</span>', $current_doc->post_content );
            } else {
                $post_content = $current_doc->post_content;
            }

            // Auto-Generate TOC from Headings
            if ( get_option( HELPDOCS_GO_PF.'auto_htoc' ) ) {
                // Match all h2–h6 tags
                preg_match_all( '/<h([2-6])[^>]*>(.*?)<\/h\1>/i', $post_content, $matches, PREG_SET_ORDER );

                if ( $matches ) {
                    $toc = '<div id="page-toc"><ul class="page-toc-list">';
                    $prev_level = 0;

                    foreach ( $matches as $match ) {
                        $level = intval( $match[1] );
                        $heading_text = strip_tags( $match[2] );
                        $anchor = sanitize_title( $heading_text );

                        // Insert anchor into the heading tag
                        $post_content = preg_replace(
                            '/'.preg_quote( $match[0], '/' ).'/',
                            '<h'.$level.' id="'.$anchor.'">'.$heading_text.'</h'.$level.'>',
                            $post_content,
                            1 // Only replace first occurrence
                        );

                        // Handle nested lists based on heading level
                        if ( $prev_level && $level > $prev_level ) {
                            $toc .= str_repeat( '<ul>', $level - $prev_level );
                        } elseif ( $prev_level && $level < $prev_level ) {
                            $toc .= str_repeat( '</ul>', $prev_level - $level );
                        }

                        $toc .= '<li><a href="#'.$anchor.'">'.esc_html( $heading_text ).'</a></li>';
                        $prev_level = $level;
                    }

                    // Close any remaining open lists
                    if ( $prev_level > 0 ) {
                        $toc .= str_repeat( '</ul>', $prev_level - 1 );
                    }

                    $toc .= '</ul></div>';

                    echo $toc;
                }
            }

            // Highlight the content
            add_filter( 'wp_kses_allowed_html', 'helpdocs_allow_addt_tags', 10, 1 );
            echo '<div id="doc-content">'.wp_kses_post( apply_filters( 'the_content', $post_content ) ).'</div>';
            remove_filter( 'wp_kses_allowed_html', 'helpdocs_allow_addt_tags', 10, 1 );

        // End the toc container
        echo '</div>';

    // Search with no results
    } else if ( $s !== '' ) {

        // Remove the id qs
        helpdocs_remove_qs_without_refresh( 'id' );

        // Say it aint so
        echo '<div id="no-docs-found">No docs found with the keyword "<strong>'.esc_html( $s ).'</strong>"... Please try again.</div>';
    
    // Otherwise redirect to page without doc id
    } else {
        wp_safe_redirect( $current_url );
    }

// End the full page container
echo '</div>';

// Alert
echo '<div id="helpdocs-alert-imports" aria-hidden="true"><a href="javascript:void(0);" class="close" aria-label="Close Notice">X</a>Import feeds cannot be added to folders. You must clone them onto your site to add them.</div>';
?>