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
new HELPDOCS_IMPORTS;


/**
 * Main plugin class.
 */
class HELPDOCS_IMPORTS {

    /**
     * Post type
     */ 
    public static $post_type;


    /**
	 * Constructor
	 */
	public function __construct() {

        // The post type
        self::$post_type = 'help-doc-imports';

        // Initialize on init
        add_action( 'init', [ $this, 'init' ] );

        // Add the header to the top of the admin list page
        add_action( 'load-edit.php', [ $this, 'add_header' ] );

        // Add the meta box
        add_action( 'add_meta_boxes', [ $this, 'meta_boxes' ] );

        // Save the post data
        add_action( 'save_post', [ $this, 'save_post' ] );

        // Add admin columns
        add_filter( 'manage_'.self::$post_type.'_posts_columns', [ $this, 'admin_columns' ] );
        add_action( 'manage_'.self::$post_type.'_posts_custom_column', [ $this, 'admin_column_content' ], 10, 2 );

	} // End __construct()


    /**
     * Load on init
     *
     * @return void
     */
    public function init() {

        // Register the post type
        $this->register_post_type();

    } // End init()


    /**
     * Register the post type
     */
    public function register_post_type() {
        // Create names
        $singular_lc = 'import';
        $singular = ucwords( $singular_lc );

        $plural_lc = $singular_lc.'s';
        $plural = ucwords( $plural_lc );

        $menu_label = $plural;
        $name_admin_bar = $plural;

        // Set the labels
        $labels = [
            'name'                  => _x( $plural, 'Post Type General Name', 'admin-help-docs' ),
            'singular_name'         => _x( $singular, 'Post Type Singular Name', 'admin-help-docs' ),
            'menu_name'             => __( $menu_label, 'admin-help-docs' ),
            'name_admin_bar'        => __( $name_admin_bar, 'admin-help-docs' ),
            'archives'              => __( $singular.' Archives', 'admin-help-docs' ),
            'attributes'            => __( $singular.' Attributes', 'admin-help-docs' ),
            'parent_item_colon'     => __( 'Parent '.$singular.':', 'admin-help-docs' ),
            'all_items'             => __( 'All '.$plural, 'admin-help-docs' ),
            'add_new_item'          => __( 'Add New '.$singular, 'admin-help-docs' ),
            'add_new'               => __( 'Add New', 'admin-help-docs' ),
            'new_item'              => __( 'New '.$singular, 'admin-help-docs' ),
            'edit_item'             => __( 'Edit '.$singular, 'admin-help-docs' ),
            'update_item'           => __( 'Update '.$singular, 'admin-help-docs' ),
            'view_item'             => __( 'View '.$singular, 'admin-help-docs' ),
            'view_items'            => __( 'View '.$plural, 'admin-help-docs' ),
            'search_items'          => __( 'Search '.$plural, 'admin-help-docs' ),
            'not_found'             => __( 'Not found', 'admin-help-docs' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'admin-help-docs' ),
            'featured_image'        => __( 'Featured Image', 'admin-help-docs' ),
            'set_featured_image'    => __( 'Set featured image', 'admin-help-docs' ),
            'remove_featured_image' => __( 'Remove featured image', 'admin-help-docs' ),
            'use_featured_image'    => __( 'Use as featured image', 'admin-help-docs' ),
            'insert_into_item'      => __( 'Insert into '.$singular_lc, 'admin-help-docs' ),
            'uploaded_to_this_item' => __( 'Uploaded to this '.$singular_lc, 'admin-help-docs' ),
            'items_list'            => __( $singular.' list', 'admin-help-docs' ),
            'items_list_navigation' => __( $singular.' list navigation', 'admin-help-docs' ),
            'filter_items_list'     => __( 'Filter '.$singular_lc.' list', 'admin-help-docs' ),
        ];

        // Allow filter for supports and taxonomies
        $supports = apply_filters( HELPDOCS_GO_PF.'post_type_supports', [ 'title', 'excerpt' ] );
        $taxonomies = apply_filters( HELPDOCS_GO_PF.'post_type_taxonomies', [] );
    
        // Set the CPT args
        $args = [
            'label'                 => __( $name_admin_bar, 'admin-help-docs' ),
            'description'           => __( $plural, 'admin-help-docs' ),
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
            'query_var'             => self::$post_type,
            'capability_type'       => 'post',
            'show_in_rest'          => false,
        ];
    
        // Register the CPT
        register_post_type( self::$post_type, $args );
    } // End register_post_type()


    /**
     * Add the header to the top of the admin list page
     *
     * @return void
     */
    public function add_header() {
        $screen = get_current_screen();

        // Only edit post screen:
        if ( 'edit-'.self::$post_type === $screen->id ) {

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
            self::$post_type,
            'advanced',
			'high'
        );

        // Add the location meta box to our custom post type
        add_meta_box( 
            'help-import-docs',
            __( 'Choose Documents to Import', 'admin-help-docs' ),
            [ $this, 'meta_box_content' ],
            self::$post_type,
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
        $selected_docs = get_post_meta( $post->ID, HELPDOCS_GO_PF.'docs', true );
        $selected_tocs = get_post_meta( $post->ID, HELPDOCS_GO_PF.'tocs', true );

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
                'blocking' => true
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
                if ( isset( $_GET[ 'imp' ] ) && absint( $_GET[ 'imp' ] ) != '' ) {

                    // Doc id
                    $import_doc_id = absint( $_GET[ 'imp' ] );

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
                                <p><?php _e( 'Great Scott! You have imported the doc entitled "<strong>'.esc_html( $doc->title ).'</strong>".', 'admin-help-docs' ); ?> <span class="notice-buttons" style="margin-left: 10px;"><a class="button button-secondary" href="<?php echo esc_url( $import_edit_url ); ?>">Manage Doc</a></span></p>
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
                }
                .help-docs-checkbox-cont input {
                    vertical-align: bottom;
                }
                #all-tocs-cont {
                    display: '.esc_attr( $disp_tocs ).';
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
                    echo '<div class="help-docs-checkbox-cont">
                        <input type="checkbox" id="doc_all" name="'.esc_attr( HELPDOCS_GO_PF ).'all" value="1" '.checked( 1, $all, false ).'> 
                        <span id="doc_label-all" class="doc_labels">
                            <label for="doc_all">Feed All Documents Automatically</label>
                        </span>
                    </div>';

                    // Add the checkbox
                    echo '<div id="all-tocs-cont" class="help-docs-checkbox-cont">
                        <input type="checkbox" id="doc_all_tocs" name="'.esc_attr( HELPDOCS_GO_PF ).'all_tocs" value="1" '.checked( 1, $all_tocs, false ).'> 
                        <span id="doc_label-all-tocs" class="doc_labels">
                            <label for="doc_all_tocs">Add All to Dashboard Table of Contents</label>
                        </span>
                    </div>';

                    // Add the checkbox
                    echo '<div class="help-docs-checkbox-cont">
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

                // Iter the docs
                foreach ( $docs as $doc ) {

                    // Feed Checked?
                    if ( in_array( $doc->ID, $selected_docs ) ) {
                        $feed_checked = ' checked';
                    } else {
                        $feed_checked = '';
                    }

                    // TOC Checked?
                    if ( in_array( $doc->ID, $selected_tocs ) ) {
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

                    // Start the field
                    echo '<tr>
                        <td><a href="'.esc_url( $current_url ).'&imp='.absint( $doc->ID ).'">Import Now</a></td>
                        <td><input type="checkbox" id="doc_'.absint( $doc->ID ).'" class="import-checkboxes" name="'.esc_attr( HELPDOCS_GO_PF ).'docs[]" value="'.absint( $doc->ID ).'" '.esc_attr( $feed_checked ).'></td>
                        <td><label for="doc_'.absint( $doc->ID ).'">'.esc_attr( $doc->title ).'</label>'.$incl_desc.'</td>
                        <td>'.date( 'F j, Y', strtotime( $doc->publish_date ) ).'</td>
                        <td>'.esc_attr( $doc->created_by ).'</td>
                        <td>'.wp_kses_post( $HELPDOCS_DOCUMENTATION->get_admin_page_title_from_url( $doc->site_location ) ).'</td>
                        <td><input type="checkbox" id="toc_'.absint( $doc->ID ).'" class="import-checkboxes" name="'.esc_attr( HELPDOCS_GO_PF ).'tocs[]" value="'.absint( $doc->ID ).'" '.esc_attr( $toc_checked ).' aria-label="Add to Dashboard Table of Contents"></td>
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
        // Gather post data.
        $post = [
            'import_id'     => absint( $doc->ID ),
            'post_title'    => sanitize_text_field( $doc->title ),
            'post_content'  => wp_kses_post( $doc->content ),
            'post_status'   => 'publish',
            'post_author'   => get_current_user_id(),
            'post_type'     => HELPDOCS_DOCUMENTATION::$post_type,
            'post_excerpt'  => sanitize_text_field( $doc->desc ),
            'meta_input'    => [
                HELPDOCS_GO_PF.'site_location'  => sanitize_text_field( $doc->site_location ),
                HELPDOCS_GO_PF.'page_location'  => sanitize_text_field( $doc->page_location ),
                HELPDOCS_GO_PF.'priority'       => sanitize_text_field( $doc->priority ),
                HELPDOCS_GO_PF.'order'          => absint( $doc->order ),
                HELPDOCS_GO_PF.'post_types'     => sanitize_text_field( $doc->post_types ),
                HELPDOCS_GO_PF.'imported_from'  => absint( $imported_from_id ),
                HELPDOCS_GO_PF.'import_id'      => absint( $doc->ID ),
            ],
        ];
        // dpr( $doc );
        // dpr( $post );

        // Insert the post into the database.
        $post_id = wp_insert_post( $post );
        // $post_id = 1234;
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
        if ( !wp_verify_nonce( $_POST[ 'help_imports_nonce' ], 'help_imports_nonce' ) || 
             !wp_verify_nonce( $_POST[ 'help_imports_rest_nonce' ], 'help_imports_rest_nonce' ) ) {
            return;
        }
     
        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
     
        // Check the user's permissions.
        if ( isset( $_POST[ 'post_type' ] ) && $_POST[ 'post_type' ] != self::$post_type ) {
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
    $post_type = HELPDOCS_IMPORTS::$post_type;

    // Get all of the imports that are enabled
    $import_args = [
        'posts_per_page'    => -1,
        'post_status'       => 'publish',
        'post_type'         => $post_type,
        'meta_key'		    => HELPDOCS_GO_PF.'enabled',
        'meta_value'	    => 1,
        'meta_compare'	    => '==',
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
                    'blocking' => true
                ]
            );

            // If there is no error, continue
            if ( !is_wp_error( $response ) ) {

                // Get the docs array
                $docs = json_decode( wp_remote_retrieve_body( $response ) ) ?: [];

                // Verify that we found some
                if ( !empty( $docs ) ) {

                    // Get the selected docs
                    $selected_docs = get_post_meta( $import->ID, HELPDOCS_GO_PF.'docs', true );

                    // Get the selected tocs
                    $selected_tocs = get_post_meta( $import->ID, HELPDOCS_GO_PF.'tocs', true );

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

                            // Create the object
                            $object = (object)[
                                'ID'                            => $doc->ID,
                                'post_author'                   => $doc->created_by,
                                'post_date'                     => $doc->publish_date,
                                'post_content'                  => $doc->content,
                                'post_title'                    => $doc->title,
                                'post_excerpt'                  => $doc->desc,
                                'post_modified'                 => $doc->modified_date,
                                '_edit_last'                    => $doc->modified_by,
                                HELPDOCS_GO_PF.'order'          => $doc->order,
                                HELPDOCS_GO_PF.'page_location'  => $doc->page_location,
                                HELPDOCS_GO_PF.'post_types'     => $doc->post_types,
                                HELPDOCS_GO_PF.'priority'       => $doc->priority,
                                HELPDOCS_GO_PF.'site_location'  => $doc->site_location,
                                HELPDOCS_GO_PF.'toc'            => $toc,
                                'auto_feed'                     => $import->post_title,
                                'feed_id'                       => $import->ID
                            ];

                            // Check for args
                            if ( !is_null( $args ) ) {

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
}