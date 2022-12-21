<?php
// Get the colors
$color_ac = get_option( HELPDOCS_GO_PF.'color_ac', '#1F9DAB' );
$color_bg = get_option( HELPDOCS_GO_PF.'color_bg', '#FFFFFF' );
$color_ti = get_option( HELPDOCS_GO_PF.'color_ti', '#1D2327' );
$color_fg = get_option( HELPDOCS_GO_PF.'color_fg', '#1D2327' );
?>

<style>
#documentation {
    display: flex;
    height: 100vh;
}
#doc-toc {
    width: 200px;
    height: 100vh;
    border-right: 1px solid #ccc;
}
.toc-item {
    display: block;
}
.toc-item.active {
    font-weight: bold;
}
#doc-viewer {
    flex: 1 0 auto;
    padding: 0 2rem;
}
#doc-header {
    margin-bottom: 2rem;
}
#doc-header h2 {
    color: <?php echo esc_attr( $color_ti ); ?>;
    font-size: 2rem;
    margin-bottom: 1.5rem;
    display: inline-block;
}
#edit-link {
    margin-left: 1rem;
    display: inline-block;
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
$posts = get_posts( $args );

// Stop if no posts are found
if ( !$posts ) {
    echo 'No documentation found. <a href="/'.esc_url( HELPDOCS_ADMIN_URL ).'/edit.php?post_type='.esc_attr( $post_type ).'">Add some now!</a>';
    return;
}

// First we sort by the doc order
usort( $posts, function( $a, $b ) { return strcmp( $a->helpdocs_order, $b->helpdocs_order ) ; } );

// Check if we are viewing a doc
if ( helpdocs_get( 'id' ) ) {
    $current_doc_id = absint( helpdocs_get( 'id' ) );
} else {
    $current_doc_id = $posts[0]->ID;
}

// Start the full page container
echo '<div id="documentation">';


    /**
     * Let's add a table of contents
     */

    // Start the toc container
    echo '<div id="doc-toc">';

        // Loop through each post
        foreach ( $posts as $post ) {

            // Highlight
            if ( $post->ID == $current_doc_id ) {
                $active = ' active';
            } else {
                $active = '';
            }

            // Add the item
            echo '<span class="toc-item'.esc_attr( $active ).'">&#10551; <a href="'.esc_url( $current_url ).'&id='.absint( $post->ID ).'">'.esc_html( $post->post_title ).'</a></span> ';
        }

    // End the toc container
    echo '</div>';


    /**
     * Now load the document in the viewer
     */

    // Start the toc container
    echo '<div id="doc-viewer">';

        // Get the doc
        $doc = get_post( $current_doc_id );

        // Get the author
        $created_by = get_userdata( $doc->post_author );

        // Get the modified by
        if ( $doc->_edit_last ) {
            $modified_by = get_userdata( $doc->_edit_last );
            $incl_modified = '<br>Last modified: '.helpdocs_convert_timezone( $doc->post_modified ).' by '.esc_attr( $modified_by->display_name );
        } else {
            $incl_modified = '';
        }

        // The edit link
        if ( helpdocs_user_can_edit() ) {
            $incl_edit = ' <span id="edit-link">[<a href="/'.esc_attr( HELPDOCS_ADMIN_URL ).'/post.php?post='.absint( $current_doc_id ).'&action=edit">edit</a>]</span>';
        } else {
            $incl_edit = '';
        }

        // Add the header
        echo '<div id="doc-header">
            <h2>'.esc_html( $doc->post_title ).'</h2>'.wp_kses_post( $incl_edit ).'
            <br><em>Created: '.helpdocs_convert_timezone( $doc->post_date ).' by '.esc_attr( $created_by->display_name ).'
            '.wp_kses_post( $incl_modified ).'</em>
        </div>';

        // Add the content
        echo '<div id="doc-content">'.wp_kses_post( apply_filters( 'the_content', $doc->post_content ) ).'</div>';

    // End the toc container
    echo '</div>';


// End the full page container
echo '</div>';