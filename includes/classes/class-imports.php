<?php
/**
 * Import Documents Class
 * 
 * USAGE: 
 * $imports = helpdocs_get_imports( $args );
 */

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Initiate the class
 */
add_action( 'init', function() {
    (new HELPDOCS_IMPORTS)->init();
} );


/**
 * Main plugin class.
 */
class HELPDOCS_IMPORTS {

    /**
     * Post type
     */ 
    public $post_type;


    /**
	 * Constructor
	 */
	public function __construct() {

        // The post type
        $this->post_type = 'help-doc-imports';

	} // End __construct()


    /**
     * Load on init
     *
     * @return void
     */
    public function init() {

        // Register the post type
        $this->register_post_type();

        // Add the header to the top of the admin list page
        add_action( 'load-edit.php', [ $this, 'add_header' ] );

        // Add the meta box
        add_action( 'add_meta_boxes', [ $this, 'meta_boxes' ] );

        // Save the post data
        add_action( 'save_post', [ $this, 'save_post' ] );

        // Add admin columns
        add_filter( 'manage_'.$this->post_type.'_posts_columns', [ $this, 'admin_columns' ] );
        add_action( 'manage_'.$this->post_type.'_posts_custom_column', [ $this, 'admin_column_content' ], 10, 2 );

    } // End init()


    /**
     * Register the post type
     */
    public function register_post_type() {
        // Set the labels
        $labels = [
            'name'                  => _x( 'Imports', 'Post Type General Name', 'admin-help-docs' ),
            'singular_name'         => _x( 'Import', 'Post Type Singular Name', 'admin-help-docs' ),
            'menu_name'             => __( 'Imports', 'admin-help-docs' ),
            'name_admin_bar'        => __( 'Imports', 'admin-help-docs' ),
            'archives'              => __( 'Import Archives', 'admin-help-docs' ),
            'attributes'            => __( 'Import Attributes', 'admin-help-docs' ),
            'parent_item_colon'     => __( 'Parent Import:', 'admin-help-docs' ),
            'all_items'             => __( 'All Imports', 'admin-help-docs' ),
            'add_new_item'          => __( 'Add New Import', 'admin-help-docs' ),
            'add_new'               => __( 'Add New', 'admin-help-docs' ),
            'new_item'              => __( 'New Import', 'admin-help-docs' ),
            'edit_item'             => __( 'Edit Import', 'admin-help-docs' ),
            'update_item'           => __( 'Update Import', 'admin-help-docs' ),
            'view_item'             => __( 'View Import', 'admin-help-docs' ),
            'view_items'            => __( 'View Imports', 'admin-help-docs' ),
            'search_items'          => __( 'Search Imports', 'admin-help-docs' ),
            'not_found'             => __( 'Not found', 'admin-help-docs' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'admin-help-docs' ),
            'featured_image'        => __( 'Featured Image', 'admin-help-docs' ),
            'set_featured_image'    => __( 'Set featured image', 'admin-help-docs' ),
            'remove_featured_image' => __( 'Remove featured image', 'admin-help-docs' ),
            'use_featured_image'    => __( 'Use as featured image', 'admin-help-docs' ),
            'insert_into_item'      => __( 'Insert into import', 'admin-help-docs' ),
            'uploaded_to_this_item' => __( 'Uploaded to this import', 'admin-help-docs' ),
            'items_list'            => __( 'Import list', 'admin-help-docs' ),
            'items_list_navigation' => __( 'Import list navigation', 'admin-help-docs' ),
            'filter_items_list'     => __( 'Filter import list', 'admin-help-docs' ),
        ];

        // Allow filter for supports and taxonomies
        $supports = apply_filters( HELPDOCS_GO_PF.'imports_supports', [ 'title', 'excerpt' ] );
        $taxonomies = apply_filters( HELPDOCS_GO_PF.'imports_taxonomies', [] );
    
        // Set the CPT args
        $args = [
            'label'                 => __( 'Imports', 'admin-help-docs' ),
            'description'           => __( 'Imports', 'admin-help-docs' ),
            'labels'                => $labels,
            'supports'              => $supports,
            'taxonomies'            => $taxonomies,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => false,
            'show_in_admin_bar'     => false,
            'show_in_nav_menus'     => false,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'query_var'             => $this->post_type,
            'capability_type'       => 'post',
            'show_in_rest'          => false,
        ];
    
        // Register the CPT
        register_post_type( $this->post_type, $args );
    } // End register_post_type()


    /**
     * Add the header to the top of the admin list page
     *
     * @return void
     */
    public function add_header() {
        $screen = get_current_screen();

        // Only edit post screen:
        if ( 'edit-'.$this->post_type === $screen->id ) {

            // Add the header
            add_action( 'all_admin_notices', function() {
                include HELPDOCS_PLUGIN_ADMIN_PATH.'header.php';
                echo '<br>';
            } );
        }
    } // End add_header()


    /**
     * Meta box
     *
     * @return void
     */
    public function meta_boxes() {
        // Add the location meta box to our custom post type
        add_meta_box( 
            'help-import-url',
            __( 'Website URL', 'admin-help-docs' ),
            [ $this, 'meta_box_content_url' ],
            $this->post_type,
            'advanced',
			'high'
        );

        // Add the location meta box to our custom post type
        add_meta_box( 
            'help-import-docs',
            __( 'Choose Documents to Import', 'admin-help-docs' ),
            [ $this, 'meta_box_content' ],
            $this->post_type,
            'advanced',
			'default'
        );
    } // End meta_boxes()


    /**
     * Meta box content for the URL
     *
     * @param object $post
     * @return void
     */
    public function meta_box_content_url( $post ) {

        // Add a nonce field so we can check for it later.
        wp_nonce_field( 'help_imports_nonce', 'help_imports_nonce' );
    
        // Get the current values
        $url = esc_attr( get_post_meta( $post->ID, HELPDOCS_GO_PF.'url', true ) );

        // Preview button
        $my_api_url = help_get_api_path();
        $their_api_url = str_replace( home_url(), $url, $my_api_url );
        if ( $url && $url != '' ) {
            $incl_url = $their_api_url;
        } else {
            $incl_url = '';
        }

        // Add some CSS
        echo '<style>
        #doc-url-field {
            width: 30rem !important;
        }
        #preview-raw-data:disabled {
            cursor: not-allowed;
        }
        </style>';

        // Import URL
        echo '<div id="doc-url" class="help-docs-url-cont">
            <label for="doc-url-field" class="doc-url-label">Enter the URL of the website you would like to import help docs from:</label>
            <input name="'.esc_attr( HELPDOCS_GO_PF ).'url" id="doc-url-field" type="text" style="width: 100%;" value="'.esc_url( $url ).'" placeholder="https://example.com">
            <input type="button" id="preview-raw-data" class="button button-secondary" onclick="window.open( \''.esc_url( $incl_url ).'\', \'_blank\')" value="Preview RAW Data">
        </div>';

        // Add some JS to make checkboxes appear
        echo "<script>
        // Get the elements
        const urlInput = document.getElementById( 'doc-url-field' );
        var urlInputValue = urlInput.value;
        var previewBtn = document.getElementById( 'preview-raw-data' );

        // Check if the url has an input
        if ( urlInputValue != '' ) {
            previewBtn.disabled = false;
        } else {
            previewBtn.disabled = true;
        }
        
        // Listen for changes
        urlInput.addEventListener( 'keyup', function () {

            // Get the new value
            urlInputValue = this.value;

            // Convert the url
            var myApiUrl = '".esc_url( $my_api_url )."';
            var theirApiUrl = myApiUrl.replace( window.location.protocol + '//' + window.location.hostname, urlInputValue );

            // Change the link
            previewBtn.setAttribute( 'onclick', 'window.open( \'' + theirApiUrl + '\', \'_blank\')' );

            // Page Location container
            if ( urlInputValue != '' ) {

                // Validate field
                if ( validateUrl( urlInputValue ) ) {

                    // Enable it
                    previewBtn.disabled = false;
                } else {
                    // Disable it
                    previewBtn.disabled = true;
                }
            } else {
                previewBtn.disabled = true;
            }
        } );
        function validateUrl( value ) {
            return /^(?:(?:(?:https?|ftp):)?\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:[/?#]\S*)?$/i.test( value );
          }
        </script>";

    } // End meta_box_content_url()


    /**
     * Meta box content
     *
     * @param object $post
     * @return void
     */
    public function meta_box_content( $post ) {    
        // Add a nonce field so we can check for it later.
        wp_nonce_field( 'help_imports_rest_nonce', 'help_imports_rest_nonce' );

        // Get the current values
        $url = get_post_meta( $post->ID, HELPDOCS_GO_PF.'url', true ) ? filter_var( get_post_meta( $post->ID, HELPDOCS_GO_PF.'url', true ), FILTER_SANITIZE_URL ) : '';
        $all = ( get_post_meta( $post->ID, HELPDOCS_GO_PF.'all', true ) && get_post_meta( $post->ID, HELPDOCS_GO_PF.'all', true ) == 1 ) ? 1 : 0;
        $all_tocs = ( get_post_meta( $post->ID, HELPDOCS_GO_PF.'all_tocs', true ) && get_post_meta( $post->ID, HELPDOCS_GO_PF.'all_tocs', true ) == 1 ) ? 1 : 0;
        $enabled = ( get_post_meta( $post->ID, HELPDOCS_GO_PF.'enabled', true ) && get_post_meta( $post->ID, HELPDOCS_GO_PF.'enabled', true ) == 1 ) ? 1 : 0;
        
        // Get the selected docs
        if ( get_post_meta( $post->ID, HELPDOCS_GO_PF.'docs', true ) ) {
            $selected_docs = get_post_meta( $post->ID, HELPDOCS_GO_PF.'docs', true );
            if ( !is_array( $selected_docs ) ) {
                $selected_docs = unserialize( $selected_docs );
            }
        } else {
            $selected_docs = [];
        }

        // Get the selected tocs
        if ( get_post_meta( $post->ID, HELPDOCS_GO_PF.'tocs', true ) ) {
            $selected_tocs = get_post_meta( $post->ID, HELPDOCS_GO_PF.'tocs', true );
            if ( !is_array( $selected_tocs ) ) {
                $selected_tocs = unserialize( $selected_tocs );
            }
        } else {
            $selected_tocs = [];
        }

        // Get our api url
        if ( !$url || $url == '' ) {
            echo '<br><em>The available public documents will be shown here after entering the URL and clicking on the "Update" button.</em>';
            return;
        }

        // Get the api url
        $api_url = help_get_api_path();
        $api_url = str_replace( home_url(), $url, $api_url );

        // Fetch the docs
        $response = wp_remote_get(
            $api_url,
            [
                'httpversion' => '1.1',
                'blocking'    => true
            ]
        );

        // Error msg
        $err_msg = '<br><br><em>There are no public documents at the URL you specified. Check the URL and ensure that documents are given permission to be public.</em>';

        // If there is no error, continue
        if ( !is_wp_error( $response ) ) {

            // Decode
            $docs = json_decode( wp_remote_retrieve_body( $response ) ) ?: [];

            // Verify that we found some
            if ( !empty( $docs ) ) {

                // Check if we are importing a doc
                if ( isset( $_GET[ 'imp' ] ) && absint( $_GET[ 'imp' ] ) != '' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

                    // Doc id
                    $import_doc_id = absint( $_GET[ 'imp' ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

                    // Iter the docs
                    foreach ( $docs as $doc ) {

                        // Attempt to get the doc
                        if ( $import_doc_id == $doc->ID ) {

                            // Import it to local
                            $new_post_id = $this->import_post( $doc, $post->ID );

                            // If successful, add a notice
                            if ( $new_post_id ) {
                                $import_edit_url = home_url( HELPDOCS_ADMIN_URL.'/post.php?post='.$new_post_id.'&action=edit' );
                                helpdocs_remove_qs_without_refresh( 'imp' );
                                ?>
                                <div class="notice notice-success is-dismissible">
                                <p><?php /* translators: 1: The help document title */
                                echo wp_kses_post( sprintf( __( 'Great Scott! You have imported the doc entitled "%s".', 'admin-help-docs' ), '<strong>' . $doc->title . '</strong>' ) ); ?> <span class="notice-buttons" style="margin-left: 10px;"><a class="button button-secondary" href="<?php echo esc_url( $import_edit_url ); ?>">Manage Doc</a></span></p>
                                </div>
                                <?php
                            }
                        }
                    }
                }

                // Instructions
                echo '<p><em>You can choose to remotely feed documents from the other website, which will update automatically if they are changed on the other site. This is useful if you manage several sites and want to control them in one spot. You may also import them individually, which will clone them and add them to this website. The benefit of doing that is that you won\'t lose them if they get taken down on the other site. Note that if you import it, you will probably want to disable the local version or the remote feed so that you don\'t have two documents showing up at the same time.</em></p>
                <p><em>The "TOC" option allows you to add it to the Dashboard Table of Contents, provided that you have enabled Dashboard TOC in your settings and the feed\'s site location is set to "Main Documentation Page."</em></p>';

                // Are we displaying all TOC checkbox at load?
                if ( $all ) {
                    $disp_tocs = 'inline-block';
                } else {
                    $disp_tocs = 'none';
                }

                // Add some CSS
                echo '<style>
                .help-docs-checkbox-cont {
                    display: inline-block;
                    margin: 10px 20px 0 0;
                    padding: 10px;
                }
                .help-docs-checkbox-cont.first {
                    padding-left: 0;
                }
                .help-docs-checkbox-cont input {
                    vertical-align: bottom;
                }
                #all-tocs-cont {
                    display: '.esc_attr( $disp_tocs ).';
                }
                #help-docs-enable-site-cont.disabled {
                    background: yellow;
                    border-radius: 5px;
                }
                #help-docs-enable-site-cont.disabled label {
                    font-weight: bold;
                }
                .help-docs-doc-table {
                    margin-top: 1rem;
                    width: 100%;
                }
                .help-docs-doc-table {
                    border-collapse: collapse;
                }
                .help-docs-doc-table,
                .help-docs-doc-table th,
                .help-docs-doc-table td {
                    border: 1px solid black;
                }
                .help-docs-doc-table th,
                .help-docs-doc-table td {
                    padding: 10px;
                    text-align: center;
                }
                .help-docs-doc-table td {
                    word-break:break-all;
                }
                .help-docs-doc-table tr:nth-child(even) {
                    background: #F0F0F1 !important;
                }
                .help-docs-doc-table pre {
                    word-break: break-word;
                    white-space: pre-wrap;
                }
                .help-docs-doc-table .import-col {
                    width: 100px;
                }
                .help-docs-doc-table .feed-col {
                    width: 45px;
                }
                .help-docs-doc-table .title-col {
                    text-align: left !important;
                }
                .help-docs-doc-table tr.disabled {
                    background: repeating-linear-gradient( 45deg, rgba(0, 0, 0, 0.05), rgba(0, 0, 0, 0.05) 10px, rgba(0, 0, 0, 0.1) 10px, rgba(0, 0, 0, 0.1) 20px ) !important;
                }
                </style>';

                // Start the settings container
                echo '<div id="doc-settings" class="help-docs-checkboxes-cont">';

                    // Add the checkbox
                    echo '<div class="help-docs-checkbox-cont first">
                        <input type="checkbox" id="doc_all" name="'.esc_attr( HELPDOCS_GO_PF ).'all" value="1" '.checked( 1, $all, false ).'> 
                        <span id="doc_label-all" class="doc_labels">
                            <label for="doc_all">Feed All Documents Automatically</label>
                        </span>
                    </div>';

                    // Add the checkbox
                    echo '<div id="all-tocs-cont" class="help-docs-checkbox-cont">
                        <input type="checkbox" id="doc_all_tocs" name="'.esc_attr( HELPDOCS_GO_PF ).'all_tocs" value="1" '.checked( 1, $all_tocs, false ).'> 
                        <span id="doc_label-all-tocs" class="doc_labels">
                            <label for="doc_all_tocs">Add All Main Docs to Dashboard Table of Contents (Must be Enabled in <a href="'.esc_url( helpdocs_plugin_options_path( 'settings' ) ).'">Settings</a>)</label>
                        </span>
                    </div>';

                    // Add the checkbox
                    $class_disabled = $enabled ? '' : ' disabled';
                    echo '<div id="help-docs-enable-site-cont" class="help-docs-checkbox-cont'.esc_attr( $class_disabled ).'">
                        <input type="checkbox" id="doc_enabled" name="'.esc_attr( HELPDOCS_GO_PF ).'enabled" value="1" '.checked( 1, $enabled, false ).'> 
                        <span id="doc_label-enabled" class="doc_labels">
                            <label for="doc_enabled">Enable This Site</label>
                        </span>
                    </div>';

                // End the settings container
                echo '</div>';

                // Get the current url
                $current_url = helpdocs_get_current_url();

                // Initiate documentation class
                $HELPDOCS_DOCUMENTATION = new HELPDOCS_DOCUMENTATION();

                // Start the table with headers
                echo '<table id="docs-table" class="help-docs-doc-table">
                    <tr>
                    <th class="import-col">Import</th>
                    <th class="feed-col">Feed</th>
                    <th class="title-col">Title</th>
                    <th>Publish Date</th>
                    <th>Created By</th>
                    <th>Site Location</th>
                    <th class="toc-col">TOC</th>
                </tr>';

                // Allowed html
                $allowed_html = helpdocs_wp_kses_allowed_html(); 

                // Iter the docs
                foreach ( $docs as $doc ) {

                    // Feed Checked?
                    if ( !empty( $selected_docs ) && in_array( $doc->ID, $selected_docs ) ) {
                        $feed_checked = ' checked';
                    } else {
                        $feed_checked = '';
                    }

                    // TOC Checked?
                    if ( !empty( $selected_tocs ) && in_array( $doc->ID, $selected_tocs ) ) {
                        $toc_checked = ' checked';
                    } else {
                        $toc_checked = '';
                    }

                    // Check for an excerpt/description
                    if ( $doc->desc && $doc->desc != '' ) {
                        $incl_desc = '<br><em>('.esc_html( $doc->desc ).')</em>';
                    } else {
                        $incl_desc = '';
                    }

                    // Include a TOC checkbox ONLY if location is main
                    if ( base64_decode( $doc->site_location ) == 'main' ) {
                        $incl_toc = '<input type="checkbox" id="toc_'.absint( $doc->ID ).'" class="import-checkboxes" name="'.esc_attr( HELPDOCS_GO_PF ).'tocs[]" value="'.absint( $doc->ID ).'" '.esc_attr( $toc_checked ).' aria-label="Add to Dashboard Table of Contents">';
                    } else {
                        $incl_toc = '';
                    }

                    // Start the field
                    echo '<tr>
                        <td><a href="'.esc_url( $current_url ).'&imp='.absint( $doc->ID ).'">Import Now</a></td>
                        <td><input type="checkbox" id="doc_'.absint( $doc->ID ).'" class="import-checkboxes" name="'.esc_attr( HELPDOCS_GO_PF ).'docs[]" value="'.absint( $doc->ID ).'" '.esc_attr( $feed_checked ).'></td>
                        <td><label for="doc_'.absint( $doc->ID ).'">'.esc_attr( $doc->title ).'</label>'.wp_kses( $incl_desc, [ 'br' => [], 'em' => [] ] ).'</td>
                        <td>'.esc_html( gmdate( 'F j, Y', strtotime( $doc->publish_date ) ) ).'</td>
                        <td>'.esc_attr( $doc->created_by ).'</td>
                        <td>'.wp_kses_post( $HELPDOCS_DOCUMENTATION->get_admin_page_title_from_url( $doc->site_location ) ).'</td>
                        <td>'.wp_kses( $incl_toc, $allowed_html ).'</td>
                    </tr>';
                }

                // End the table
                echo '</table>';

            // Otherwise say it isn't so
            } else {
                echo wp_kses_post( $err_msg );
            }
        } else {
            echo wp_kses_post( $err_msg );
        }

        // Add some JS to make checkboxes appear
        echo "<script>
        // Get the enable checkbox
        const importEnableSite = document.getElementById( 'doc_enabled' );
        importEnableSite.addEventListener( 'change', function ( e ) {
            const importEnableSiteCont = document.getElementById( 'help-docs-enable-site-cont' );
            if ( this.checked ) {
                importEnableSiteCont.classList.remove( 'disabled' );
            } else {
                importEnableSiteCont.classList.add( 'disabled' );
            }
        } );

        // Get the import all checkbox
        const importAllCheckbox = document.getElementById( 'doc_all' );

        // Get the import all tocs checkbox
        const importAllTocsCont = document.getElementById( 'all-tocs-cont' );

        // Enable/disable list
        docsToggleDisabled( importAllCheckbox );
        
        // Listen for change
        importAllCheckbox.addEventListener( 'change', function () {
            docsToggleDisabled( importAllCheckbox );
        } );

        // Enable/disable list function
        function docsToggleDisabled( importAllCheckbox ) {
            const importCheckboxes = document.querySelectorAll( '.import-checkboxes' );
            var docsTable = document.querySelectorAll( '#docs-table th, #docs-table td' );
            var docsRows = document.querySelectorAll( '#docs-table tr' );

            // Is the import all checkbox checked?
            if ( importAllCheckbox.checked == true ) {

                // Iter the table rows
                for ( var r = 0; r < docsRows.length; r++) {
                    docsRows[r].classList.add( 'disabled' );
                }
                
                // Iter the table cells
                for ( var i = 0; i < docsTable.length; i++) {
                    docsTable[i].style.opacity = '0.65';
                }

                // Iter the checkboxes
                for ( var c = 0; c < importCheckboxes.length; c++) {
                    importCheckboxes[c].disabled = true;
                }

                // Show the all TOC checkbox
                importAllTocsCont.style.display = 'inline-block';

            } else {

                // Iter the table rows
                for ( var r = 0; r < docsRows.length; r++) {
                    docsRows[r].classList.remove( 'disabled' );
                }
                
                // Iter the table cells
                for ( var i = 0; i < docsTable.length; i++) {
                    docsTable[i].style.opacity = '1';
                }

                // Iter the checkboxes
                for ( var c = 0; c < importCheckboxes.length; c++) {
                    importCheckboxes[c].disabled = false;
                }

                // Hide the all TOC checkbox
                importAllTocsCont.style.display = 'none';
            }
        }
        </script>";

    } // End meta_box_content()

    
    /**
     * Import post from json
     *
     * @param int $post_id
     * @return int
     */
    public function import_post( $doc, $imported_from_id ) {
        // If custom url exists, we should swap the domains out.
        if ( sanitize_text_field( $doc->site_location ) == base64_encode( 'custom' ) ) {
            $custom = filter_var( $doc->custom, FILTER_SANITIZE_URL );

            // Parse urls
            $remote_parts = wp_parse_url( $custom );
            $remote_domain = strtolower( $remote_parts[ 'host' ] ?? '' );
            $local_parts = wp_parse_url( home_url() );
            $local_domain = $local_parts[ 'host' ];

            // Get an updated URL
            $custom = str_replace( $remote_domain, $local_domain, $custom );
        } else {
            $custom = '';
        }

        $post_type = (new HELPDOCS_DOCUMENTATION())->post_type;
        
        // Gather post data.
        $post = [
            'import_id'     => absint( $doc->ID ),
            'post_title'    => sanitize_text_field( $doc->title ),
            'post_content'  => wp_kses_post( $doc->content ),
            'post_status'   => 'publish',
            'post_author'   => get_current_user_id(),
            'post_type'     => $post_type,
            'post_excerpt'  => sanitize_text_field( $doc->desc ),
            'meta_input'    => [
                HELPDOCS_GO_PF.'site_location'  => sanitize_text_field( $doc->site_location ),
                HELPDOCS_GO_PF.'page_location'  => sanitize_text_field( $doc->page_location ),
                HELPDOCS_GO_PF.'priority'       => sanitize_text_field( $doc->priority ),
                HELPDOCS_GO_PF.'custom'         => $custom,
                HELPDOCS_GO_PF.'addt_params'    => absint( $doc->addt_params ),
                HELPDOCS_GO_PF.'order'          => 0,
                HELPDOCS_GO_PF.'post_types'     => sanitize_text_field( $doc->post_types ),
                HELPDOCS_GO_PF.'imported_from'  => absint( $imported_from_id ),
                HELPDOCS_GO_PF.'import_id'      => absint( $doc->ID ),
            ],
        ];
        // dpr( $doc );
        // dpr( $post );

        // Insert the post into the database.
        $post_id = wp_insert_post( $post );

        if ( $post_id && !is_wp_error( $post_id ) ) {
            $taxonomies = get_object_taxonomies( $post_type, 'names' );

            if ( isset( $doc->taxonomies ) && is_object( $doc->taxonomies ) ) {
                foreach ( $doc->taxonomies as $taxonomy => $terms ) {
                    if ( in_array( $taxonomy, $taxonomies, true ) && is_array( $terms ) ) {

                        $term_slugs = [];

                        foreach ( $terms as $term ) {
                            if ( is_object( $term ) && isset( $term->name, $term->slug ) ) {
                                $term_name = sanitize_text_field( $term->name );
                                $term_slug = sanitize_title( $term->slug );

                                $existing = get_terms( [
                                    'taxonomy'   => $taxonomy,
                                    'hide_empty' => false,
                                    'name'       => $term_name,
                                ] );

                                $matched = false;

                                if ( !is_wp_error( $existing ) && !empty( $existing ) ) {
                                    foreach ( $existing as $existing_term ) {
                                        if ( $existing_term->slug === $term_slug ) {
                                            $matched = true;
                                            break;
                                        }
                                    }
                                }

                                if ( !$matched ) {
                                    wp_insert_term( $term_name, $taxonomy, [ 'slug' => $term_slug ] );
                                }

                                $term_slugs[] = $term_slug;
                            }
                        }

                        if ( !empty( $term_slugs ) ) {
                            wp_set_object_terms( $post_id, $term_slugs, $taxonomy, false );
                        }
                    }
                }
            }
        }
        
        return $post_id;
    } // End import_post()


    /**
     * Save the post data
     *
     * @param int $post_id
     * @return void
     */
    public function save_post( $post_id ) {
        // Check if our nonces are set.
        if ( !isset( $_POST[ 'help_imports_nonce' ] ) || !isset( $_POST[ 'help_imports_rest_nonce' ] ) ) {
            return;
        }
     
        // Verify that the nonce is valid.
        if ( !wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST[ 'help_imports_nonce' ] ) ), 'help_imports_nonce' ) ||
             !wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST[ 'help_imports_rest_nonce' ] ) ), 'help_imports_rest_nonce' ) ) {
            return;
        }
     
        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
     
        // Check the user's permissions.
        if ( isset( $_POST[ 'post_type' ] ) && $_POST[ 'post_type' ] != $this->post_type ) {
            return;
        }
     
        /* OK, it's safe for us to save the data now. */

        // URL
        $url = isset( $_POST[ HELPDOCS_GO_PF.'url' ] ) ? filter_var( $_POST[ HELPDOCS_GO_PF.'url' ], FILTER_VALIDATE_URL ) : '';

        // All
        $all = isset( $_POST[ HELPDOCS_GO_PF.'all' ] ) && $_POST[ HELPDOCS_GO_PF.'all' ] == 1 ? 1 : 0;

        // All TOCs
        $all_tocs = isset( $_POST[ HELPDOCS_GO_PF.'all_tocs' ] ) && $_POST[ HELPDOCS_GO_PF.'all_tocs' ] == 1 ? 1 : 0;
        
        // Enabled
        $enabled = isset( $_POST[ HELPDOCS_GO_PF.'enabled' ] ) && $_POST[ HELPDOCS_GO_PF.'enabled' ] == 1 ? 1 : 0;

        // Docs
        $docs = isset( $_POST[ HELPDOCS_GO_PF.'docs' ] ) ? array_map( 'absint', (array) $_POST[ HELPDOCS_GO_PF.'docs' ] ) : [];

        // TOCs
        $tocs = isset( $_POST[ HELPDOCS_GO_PF.'tocs' ] ) ? array_map( 'absint', (array) $_POST[ HELPDOCS_GO_PF.'tocs' ] ) : [];
        
        // Values
        $values = [
            HELPDOCS_GO_PF.'url'        => $url,
            HELPDOCS_GO_PF.'all'        => $all,
            HELPDOCS_GO_PF.'all_tocs'   => $all_tocs,
            HELPDOCS_GO_PF.'enabled'    => $enabled,
            HELPDOCS_GO_PF.'docs'       => $docs,
            HELPDOCS_GO_PF.'tocs'       => $tocs,
        ];

        // Update the meta field in the database.
        foreach ( $values as $k => $v ) {
            update_post_meta( $post_id, $k, $v );
        }
    } // End save_post()


    /**
     * Admin columns
     *
     * @param array $columns
     * @return array
     */
    public function admin_columns( $columns ) {
        $columns[ HELPDOCS_GO_PF.'url' ]        = __( 'URL', 'admin-help-docs' );
        $columns[ HELPDOCS_GO_PF.'desc' ]       = __( 'Description', 'admin-help-docs' );
        $columns[ HELPDOCS_GO_PF.'all' ]        = __( 'Import All?', 'admin-help-docs' );
        $columns[ HELPDOCS_GO_PF.'enabled' ]    = __( 'Enabled?', 'admin-help-docs' );
        return $columns;
    } // End admin_columns()


    /**
     * Admin column content
     *
     * @param string $column
     * @param int $post_id
     * @return void
     */
    public function admin_column_content( $column, $post_id ) {
        // URL
        if ( HELPDOCS_GO_PF.'url' === $column ) {
            echo get_post_meta( $post_id, HELPDOCS_GO_PF.'url', true ) ? esc_url( get_post_meta( $post_id, HELPDOCS_GO_PF.'url', true ) ) : '';
        }

        // Description
        if ( HELPDOCS_GO_PF.'desc' === $column ) {
            echo esc_html( get_the_excerpt( $post_id ) );
        }

        // Import all
        if ( HELPDOCS_GO_PF.'all' === $column ) {
            if ( get_post_meta( $post_id, HELPDOCS_GO_PF.'all', true ) && get_post_meta( $post_id, HELPDOCS_GO_PF.'all', true ) == true ) {
                $all = "Yes";
            } else {
                $all = 'No';
            }
            echo esc_attr( $all );
        }

        // Enabled
        if ( HELPDOCS_GO_PF.'enabled' === $column ) {
            if ( get_post_meta( $post_id, HELPDOCS_GO_PF.'enabled', true ) && get_post_meta( $post_id, HELPDOCS_GO_PF.'enabled', true ) == true ) {
                $enabled = "Yes";
            } else {
                $enabled = 'No';
            }
            echo esc_attr( $enabled );
        }
    } // End admin_column_content()
}


/**
 * Get all the imports as post objects
 *
 * @return array
 */
function helpdocs_get_imports( $args = null ) {
    // Get the post type
    $post_type = (new HELPDOCS_IMPORTS)->post_type;

    // Get all of the imports that are enabled
    $import_args = [
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'post_type'      => $post_type,
        'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
            [
                'key'     => HELPDOCS_GO_PF.'enabled',
                'value'   => 1,
                'compare' => '='
            ]
        ]
    ];
    $imports = get_posts( $import_args );

    // Get the api url
    $api_url = help_get_api_path();

    // Import all keys
    $import_all_key = HELPDOCS_GO_PF.'all';
    $import_all_tocs_key = HELPDOCS_GO_PF.'all_tocs';

    // Store the import objects here
    $objects = [];

    // Did we find any?
    if ( !empty( $imports ) ) {

        // Iter the imports
        foreach ( $imports as $import ) {

            // Get the api url
            $url = esc_attr( get_post_meta( $import->ID, HELPDOCS_GO_PF.'url', true ) );
            $api_url = str_replace( home_url(), $url, $api_url );

            // Fetch all of the docs from the import
            $response = wp_remote_get(
                $api_url,
                [
                    'httpversion' => '1.1',
                    'blocking'    => true
                ]
            );

            // If there is no error, continue
            if ( !is_wp_error( $response ) ) {

                // Get the docs array
                $docs = json_decode( wp_remote_retrieve_body( $response ) ) ?: [];

                // Verify that we found some
                if ( !empty( $docs ) ) {

                    // Get the selected docs
                    if ( get_post_meta( $import->ID, HELPDOCS_GO_PF.'docs', true ) ) {
                        $selected_docs = get_post_meta( $import->ID, HELPDOCS_GO_PF.'docs', true );
                    } else {
                        $selected_docs = [];
                    }

                    // Get the selected tocs
                    if ( get_post_meta( $import->ID, HELPDOCS_GO_PF.'tocs', true ) ) {
                        $selected_tocs = get_post_meta( $import->ID, HELPDOCS_GO_PF.'tocs', true );
                    } else {
                        $selected_tocs = [];
                    }

                    // Iter the docs
                    foreach ( $docs as $doc ) {

                        // Is the import all checkbox selected?
                        $import_all = $import->$import_all_key;

                        // Are we including it?
                        if ( $import_all || ( !$import_all && in_array( $doc->ID, $selected_docs ) ) ) {

                            // Now check if we are including all on TOC
                            $import_all_tocs = $import->$import_all_tocs_key;
                            if ( $import_all && $import_all_tocs || ( !$import_all && in_array( $doc->ID, $selected_tocs ) ) ) {
                                $toc = true;
                            } else {
                                $toc = false;
                            }

                            // If custom url exists, we should swap the domains out.
                            if ( sanitize_text_field( $doc->site_location ) == base64_encode( 'custom' ) ) {
                                $custom = filter_var( $doc->custom, FILTER_SANITIZE_URL );

                                // Parse urls
                                $remote_parts = wp_parse_url( $custom );
                                $remote_domain = strtolower( $remote_parts[ 'host' ] ?? '' );
                                $local_parts = wp_parse_url( home_url() );
                                $local_domain = $local_parts[ 'host' ];

                                // Get an updated URL
                                $custom = str_replace( $remote_domain, $local_domain, $custom );
                            } else {
                                $custom = '';
                            }

                            // Create the object
                            $object = (object)[
                                'ID'                            => 'import_' . absint( $doc->ID ),
                                'post_author'                   => sanitize_text_field( $doc->created_by ),
                                'post_date'                     => sanitize_text_field( $doc->publish_date ),
                                'post_content'                  => wp_kses_post( $doc->content ),
                                'post_title'                    => sanitize_text_field( $doc->title ),
                                'post_excerpt'                  => sanitize_text_field( $doc->desc ),
                                'post_modified'                 => sanitize_text_field( $doc->modified_date ),
                                '_edit_last'                    => sanitize_text_field( $doc->modified_by ),
                                HELPDOCS_GO_PF.'custom'         => $custom,
                                HELPDOCS_GO_PF.'addt_params'    => absint( $doc->addt_params ),
                                HELPDOCS_GO_PF.'order'          => $doc->order != '' ? absint( $doc->order ) : '',
                                HELPDOCS_GO_PF.'page_location'  => sanitize_text_field( $doc->page_location ),
                                HELPDOCS_GO_PF.'post_types'     => sanitize_text_field( $doc->post_types ),
                                HELPDOCS_GO_PF.'priority'       => sanitize_text_field( $doc->priority ),
                                HELPDOCS_GO_PF.'site_location'  => sanitize_text_field( $doc->site_location ),
                                HELPDOCS_GO_PF.'toc'            => $toc,
                                'auto_feed'                     => $import->post_title,
                                'feed_id'                       => $import->ID,
                                'taxonomies'                    => isset( $doc->taxonomies ) ? $doc->taxonomies : []
                            ];

                            // Create taxonomy terms if needed
                            if ( isset( $doc->taxonomies ) && is_object( $doc->taxonomies ) ) {
                                foreach ( $doc->taxonomies as $taxonomy => $terms ) {
                                    foreach ( $terms as $term ) {

                                        $term_name = sanitize_text_field( $term->name );
                                        $term_slug = sanitize_title( $term->slug );

                                        // Get existing terms by name
                                        $existing = get_terms( [
                                            'taxonomy'   => $taxonomy,
                                            'hide_empty' => false,
                                            'name'       => $term_name
                                        ] );

                                        $matched = false;

                                        if ( !is_wp_error( $existing ) && !empty( $existing ) ) {
                                            foreach ( $existing as $existing_term ) {
                                                if ( $existing_term->slug === $term_slug ) {
                                                    $matched = true;
                                                    break;
                                                }
                                            }
                                        }

                                        if ( !$matched ) {
                                            wp_insert_term( $term_name, $taxonomy, [ 'slug' => $term_slug ] );
                                        }
                                    }
                                }
                            }

                            // Check for args
                            if ( !is_null( $args ) ) {

                                // Check for meta_key and meta_value
                                if ( isset( $args[ 'meta_key' ] ) && isset( $args[ 'meta_value' ] ) && isset( $args[ 'meta_compare' ] ) ) {

                                    // The vars
                                    $meta_key = $args[ 'meta_key' ];
                                    $meta_value = $args[ 'meta_value' ];
                                    $meta_compare = $args[ 'meta_compare' ];                                

                                    // Check it
                                    if ( ( ( $meta_compare == '==' || $meta_compare == '=' ) && $object->$meta_key == $meta_value ) ||
                                            ( $meta_compare == '!=' && $object->$meta_key != $meta_value ) ) {

                                        // Add it
                                        $objects[] = $object;
                                    }
                                }

                                 // Check for meta_query
                                 if ( isset( $args[ 'meta_query' ] ) ) {

                                    // Separate queries from relation
                                    if ( isset( $args[ 'meta_query' ][ 'relation' ] ) ) {
                                        $relation = strtoupper( $args[ 'meta_query' ][ 'relation' ] );
                                        unset( $args[ 'meta_query' ][ 'relation' ] );
                                    } else {
                                        $relation = 'AND';
                                    }
                                    $meta_query = $args[ 'meta_query' ];

                                    // Count queries
                                    $queries = count( $meta_query );

                                    // Check single query
                                    if ( $queries == 1 ) {
                                        if ( isset( $meta_query[ 'key' ] ) && isset( $meta_query[ 'value' ] ) ) {
                                            $meta_key = $meta_query[ 'key' ];
                                            $meta_value = $meta_query[ 'value' ];
                                            if ( isset( $object->$meta_key ) && $object->$meta_key == $meta_value ) {

                                                // Add it
                                                $objects[] = $object;
                                            }
                                        }
                                    } elseif ( $queries > 1 ) {
                                        $matches = 0;
                                        foreach ( $meta_query as $query ) {
                                            $meta_key = $query[ 'key' ];
                                            $meta_value = $query[ 'value' ];
                                            if ( $object->$meta_key == $meta_value ) {
                                                $matches++;
                                            }
                                        }
                                        if ( ( $relation == 'AND' && $matches == $queries ) ||
                                             ( $relation == 'OR' && $matches > 0 ) ) {
                                            
                                            // Add it
                                            $objects[] = $object;
                                        }
                                    }
                                }

                            // Or else just add it
                            } else {
                                $objects[] = $object;
                            }
                        }
                    }
                }
            }
        }
    }

    // Return the objects
    return $objects;
} // End helpdocs_get_imports()