<?php
// Get the colors
$HELPDOCS_COLORS = new HELPDOCS_COLORS();
$color_ti = $HELPDOCS_COLORS->get( 'ti' );
?>

<style>
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
.toc-item {
    display: block;
    padding: 10px 10px 10px 0;
    border-bottom: 1px solid #ccc;
}
.toc-item.active {
    font-weight: bold;
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
    cursor: row-resize;
}
ul, ol {
    padding-top: 10px;
    padding-bottom: 5px;
}
ol.lower-alpha {
    list-style-type: lower-alpha;
}
ol.lower-roman {
    list-style-type: lower-roman;
}
#doc-viewer img {
    max-width:100%;
    height: auto;
    object-fit: contain;
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
    'meta_key'		    => HELPDOCS_GO_PF.'site_location',
    'meta_value'	    => base64_encode( 'main' ),
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
    echo '<br><br>No documentation found. <a href="/'.esc_attr( HELPDOCS_ADMIN_URL ).'/edit.php?post_type='.esc_attr( $post_type ).'">Add some now!</a>';
    return;
}

// First we sort by the doc order
usort( $docs, function( $a, $b ) { return strcmp( $a->helpdocs_order, $b->helpdocs_order ) ; } );

// Check if we are viewing a doc
if ( helpdocs_get( 'id' ) ) {
    $current_doc_id = absint( helpdocs_get( 'id' ) );
} else {
    $current_doc_id = $docs[0]->ID;
    helpdocs_add_qs_without_refresh( 'id', $current_doc_id );
}

// Store the current doc here
$current_doc = (Object)[];
$feed = false;

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

        // Loop through each post
        foreach ( $docs as $doc ) {

            // Highlight
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
            } else {
                $incl_feed = '';
            }

            // Add the item
            echo '<li id="item-'.absint( $doc->ID ).'" class="toc-item'.esc_attr( $active ).'"><a href="'.esc_url( $current_url ).'&id='.absint( $doc->ID ).esc_attr( $incl_feed ).'">'.esc_html( $doc->post_title ).'</a></li> ';
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

            // Get the author
            if ( is_numeric( $current_doc->post_author ) ) {
                $created_by = get_userdata( $current_doc->post_author );
                $created_by = $created_by->display_name;
            } else {
                $created_by = $current_doc->post_author;
            }

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

            // Add the header
            echo '<div id="doc-header">
                <h2>'.esc_html( $current_doc->post_title ).'</h2>'.wp_kses_post( $incl_edit ).'
                <span id="doc-meta">Created: '.esc_html( helpdocs_convert_timezone( $current_doc->post_date ) ).' by '.esc_attr( $created_by ).'
                '.wp_kses_post( $incl_modified ).'
                '.wp_kses_post( $incl_feed ).'</span>
            </div>';

            // Add the content
            echo '<div id="doc-content">'.wp_kses_post( apply_filters( 'the_content', $current_doc->post_content ) ).'</div>';

        // End the toc container
        echo '</div>';
    
    // Otherwise redirect to page without doc id
    } else {
        wp_safe_redirect( $current_url );
    }

// End the full page container
echo '</div>';
?>