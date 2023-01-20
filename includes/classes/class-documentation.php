<?php
/**
 * Help Docs Custom Post Type
 */

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Initiate the class
 */
new HELPDOCS_DOCUMENTATION;


/**
 * Main plugin class.
 */
class HELPDOCS_DOCUMENTATION {

    /**
     * Post type
     */ 
    public static $post_type;


    /**
     * Site location
     *
     * @var array
     */
    public static $site_location;


    /**
     * Page location
     *
     * @var array
     */
    public static $page_location;

    
    /**
     * Priority for other pages
     *
     * @var array
     */
    public static $priority;


    /**
	 * Constructor
	 */
	public function __construct() {

        // Define the post type
        // Use dashes for spaces
        self::$post_type = 'help-docs';

        // Get the page title
        if ( get_option( HELPDOCS_GO_PF.'page_title' ) && get_option( HELPDOCS_GO_PF.'page_title' ) != '' ) {
            $title = get_option( HELPDOCS_GO_PF.'page_title' );
        } else {
            $title = HELPDOCS_NAME;
        }

        // Set the locations
        self::$site_location = [
            'main'              => 'Main Documentation Page',
            'admin_bar'         => 'Admin Bar Menu (Must Be Enabled in Settings)',
            'index.php'         => 'Dashboard',
            'post.php'          => 'Post/Page Edit Screen',
            'edit.php'          => 'Post/Page Admin List Screen',
        ];

        // Locations on the page
        self::$page_location = [
            'contextual'        => 'Contextual Help Tab (At Top of Screen)',
            'top'               => 'Top',
            'bottom'            => 'Bottom',
            'side'              => 'Side'
        ];

        // Priority for meta boxes
        self::$priority = [
            'high'              => 'High',
            'core'              => 'Core',
            'default'           => 'Default',
            'low'               => 'Low'
        ];

        // Initialize on init
        add_action( 'init', [ $this, 'init' ] );

        // Disable block editor
        add_filter( 'use_block_editor_for_post_type', [ $this, 'disable_gutenberg' ], 10, 2 );

        // Add the header to the top of the admin list page
        add_action( 'load-edit.php', [ $this, 'add_header' ] );

        // Add the meta box
        add_action( 'add_meta_boxes', [ $this, 'meta_boxes' ] );

        // Rename excerpt meta box
        add_filter( 'gettext', [ $this, 'excerpt_meta_box' ], 10, 2 );

        // Save the post data
        add_action( 'save_post', [ $this, 'save_post' ] );

        // Add admin columns
        add_filter( 'manage_'.self::$post_type.'_posts_columns', [ $this, 'admin_columns' ] );
        add_action( 'manage_'.self::$post_type.'_posts_custom_column', [ $this, 'admin_column_content' ], 10, 2 );

        // Make admin columns sortable
        add_filter( 'manage_edit-'.self::$post_type.'_sortable_columns', [ $this, 'sort_columns' ] );

        // Dashboard widgets
        add_action( 'wp_dashboard_setup', [ $this, 'dashboard_widgets' ] );

        // Other locations
        add_action( 'admin_head', [ $this, 'add_to_other_pages' ] );

        // Gutenberg
        add_action( 'admin_footer', [ $this, 'gutenberg_content' ] );

        // Update order of documentation with Ajax
        add_action( 'wp_ajax_'.HELPDOCS_GO_PF.'update_order', [ $this, 'update_order' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

	} // End __construct()

    
    /**
     * Load on init
     *
     * @return void
     */
    public function init() {

        // Register the post type
        $this->register_post_type();

        // Register taxonomy
        // $this->register_taxonomy( 'help-location', [ self::$post_type ] );

    } // End init()

    
    /**
     * Register the post type
     */
    public function register_post_type() {
        // Create names
        $singular_lc = 'help document';
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
        $supports = apply_filters( HELPDOCS_GO_PF.'post_type_supports', [ 'title', 'editor', 'author', 'revisions', 'excerpt' ] );
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
            'show_in_rest'          => true,
        ];
    
        // Register the CPT
        register_post_type( self::$post_type, $args );
    } // End register_post_type()


    /**
     * Disable Gutenberg while allowing rest
     *
     * @param [type] $current_status
     * @param [type] $post_type
     * @return void
     */
    public function disable_gutenberg( $current_status, $post_type ) {

        // Disabled post types
        $disabled_post_types = [ self::$post_type ];
    
        // Change $can_edit to false for any post types in the disabled post types array
        if ( in_array( $post_type, $disabled_post_types, true ) ) {
            $current_status = false;
        }
    
        return $current_status;
    } // End disable_gutenberg()


    /**
     * Register taxonomy
     */
    public function register_taxonomy( $taxonomy, $post_types = [], $hierarchical = true, $show_in_rest = false, $show_admin_column = true, $show_in_quick_edit = true, $show_meta_box = null ) {
        // Make sure it's lowercase
        $taxonomy = strtolower( $taxonomy );
    
        // Plural Taxonomy
        if ( str_ends_with( $taxonomy, 'y' ) ) {
            $part = substr( $taxonomy, 0, -1 );
            $plural_no_spaces = $part.'ies';
        } else {
            $plural_no_spaces = $taxonomy.'s';
        }
        $plural = str_replace( '-', ' ', $plural_no_spaces );
    
        // Singular Taxonomy
        $singular = str_replace( '-', ' ', $taxonomy );
        
        // Capitalize it
        $capitalized_s = ucwords( $singular );
        $capitalized_p = ucwords( $plural );
    
        // Create the labels
        $labels = [
            'name' => _x( $capitalized_p, 'taxonomy general name' ),
            'singular_name' => _x( $capitalized_s, 'taxonomy singular name' ),
            'search_items' =>  __( 'Search '.$capitalized_p ),
            'all_items' => __( 'All '.$capitalized_p ),
            'parent_item' => __( 'Parent '.$capitalized_s ),
            'parent_item_colon' => __( 'Parent '.$capitalized_s.':' ),
            'edit_item' => __( 'Edit '.$capitalized_s ),
            'update_item' => __( 'Update '.$capitalized_s ),
            'add_new_item' => __( 'Add New '.$capitalized_s ),
            'new_item_name' => __( 'New '.$capitalized_s.' Name' ),
            'menu_name' => __( $capitalized_p ),
        ]; 	
    
        // Register it as a new taxonomy
        register_taxonomy( $plural_no_spaces, $post_types, [
            'hierarchical' => $hierarchical,
            'labels' => $labels,
            'show_ui' => true,
            'show_in_rest' => $show_in_rest,
            'show_admin_column' => $show_admin_column,
            'show_in_quick_edit' => $show_in_quick_edit,
            'meta_box_cb' => $show_meta_box,
            'query_var' => true,
            'rewrite' => [ 'slug' => $taxonomy, 'with_front' => false ],
        ] );
    } // End register_taxonomy()


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
            'help-locations',
            __( 'Location', 'admin-help-docs' ),
            [ $this, 'meta_box_content' ],
            self::$post_type,
            'advanced',
			'high'
        ); 

        // Start the args to get the docs
        $args = [
            'posts_per_page'    => -1,
            'post_status'       => 'publish',
            'post_type'         => self::$post_type,
            'meta_key'		    => HELPDOCS_GO_PF.'site_location',
            'meta_value'	    => base64_encode( 'post.php' ),
            'meta_compare'	    => '=',
        ];

        // Get the posts
        $docs = get_posts( $args );

        // Also get the imports
        $imports = helpdocs_get_imports( $args );

        // Merge them together
        if ( !empty( $imports ) ) {
            $docs = array_merge( $docs, $imports );
        }

        // Iter the docs
        foreach ( $docs as $doc ) {

            // Skip if the page location is not bottom or side
            $page_location_var = HELPDOCS_GO_PF.'page_location';
            $page_location = $doc->$page_location_var;
            if ( $page_location != 'bottom' && $page_location != 'side' ) {
                continue;
            }

            // Skip if bottom of gutenberg editor
            if ( $page_location == 'bottom' && is_gutenberg() ) {
                continue;
            }

            // Get the post type
            $post_types_var = HELPDOCS_GO_PF.'post_types';
            $post_types = unserialize( $doc->$post_types_var );
            if ( empty( $post_types ) ) {
                continue;
            }

            // Set the location
            if ( $page_location == 'bottom' ) {
                $context = 'advanced';
            } else {
                $context = 'side';
            }

            // Get the priority
            $priority_var = HELPDOCS_GO_PF.'priority';
            if ( $doc->$priority_var != '' && $page_location == 'side' ) {
                $priority = $doc->$priority_var;
            } else {
                $priority = 'low';
            }

            // Pass docs in the args
            $args = [ $doc ];
            
            // Create the meta box
            add_meta_box( 
                HELPDOCS_GO_PF.$doc->ID,
                __( $doc->post_title, 'admin-help-docs' ),
                [ $this, 'post_page_meta_box_content' ],
                $post_types,
                $context,
                $priority,
                $args
            ); 
        }
    } // End meta_boxes()


    /**
     * Rename excerpt meta box as description
     *
     * @param string $translation
     * @param string $original
     * @return string
     */
    public function excerpt_meta_box( $translation, $original ) {
        if ( 'Excerpt' == $original ) {
            return __( 'Description', 'admin-help-docs' );
        } elseif ( false !== strpos( $original, 'Excerpts are optional hand-crafted summaries of your' ) ) {
            return __( 'This is only used to summarize the document for the manage documents list table. If left blank, it will show first couple of lines of the content.', 'admin-help-docs' );
        }
        return $translation;
    } // End excerpt_meta_box()


    /**
     * Meta box content
     *
     * @param object $post
     * @return void
     */
    public function meta_box_content( $post ) {

        // Add a nonce field so we can check for it later.
        wp_nonce_field( 'help_location_nonce', 'help_location_nonce' );
    
        // Get the current values
        $site_location = esc_attr( get_post_meta( $post->ID, HELPDOCS_GO_PF.'site_location', true ) );
        $page_location = esc_attr( get_post_meta( $post->ID, HELPDOCS_GO_PF.'page_location', true ) );
        $post_types = unserialize( get_post_meta( $post->ID, HELPDOCS_GO_PF.'post_types', true ) );
        $order = get_post_meta( $post->ID, HELPDOCS_GO_PF.'order', true ) ? filter_var( get_post_meta( $post->ID, HELPDOCS_GO_PF.'order', true ), FILTER_SANITIZE_NUMBER_INT ) : 0;
        $priority = esc_attr( get_post_meta( $post->ID, HELPDOCS_GO_PF.'priority', true ) );
        $api = esc_attr( get_post_meta( $post->ID, HELPDOCS_GO_PF.'api', true ) );

        // Get all choices
        $all_site_locations = self::$site_location;
        $all_page_locations = self::$page_location;
        $all_post_types = get_post_types( [ '_builtin' => false ] );
        $all_priorities = self::$priority;

        // Location name changes
        $site_location_names = apply_filters( HELPDOCS_GO_PF.'location_names', [
            'edit-comments.php' => 'Comments',
            'site-editor.php' => 'Editor',
            'plugins.php' => 'Plugins',
            'dev-debug-tools' => 'Dev Debug Tools',
            'admin.php?page=dev-debug-tools&tab=logs' => 'Logs'
        ] );

        // Get all screens
        global $menu, $submenu;

        // Iter the parent menu items
        foreach ( $menu as $m ) {

            // Skip separators
            if ( str_starts_with( $m[2], 'separator' ) ) {
                continue;
            }

            // Skip dashboard and help topics
            if ( $m[2] == 'index.php' || $m[2] == helpdocs_plugin_options_short_path() ) {
                continue;
            }

            // Skip post types
            if ( str_starts_with( $m[2], 'edit.php' ) ) {
                continue;
            }

            // Change option names
            if ( array_key_exists( $m[2], $site_location_names ) ) {
                $site_location_name = $site_location_names[ $m[2] ];
            } else {
                $site_location_name = $m[0];
            }

            // Strip html
            $site_location_name = strip_tags( $site_location_name );

            // Add the parent location
            if ( !array_key_exists( $m[2], $submenu ) ) {

                // Get the url
                $url = $this->get_admin_menu_item_url( $m[2] );
                if ( is_null( $url ) ) {
                    $url = 'not found';
                }

                // Strip the url
                $url = str_replace( get_home_url().'/'.HELPDOCS_ADMIN_URL.'/', '', $url );

                // Add it
                $all_site_locations[ $url ] = $site_location_name;
            }
            
            // Check for sub menu items
            foreach ( $submenu as $k => $sub ) {

                if ( $k == $m[2] ) {

                    // Iter the submenu items
                    foreach ( $sub as $s ) {

                        // Change option names
                        if ( array_key_exists( $s[2], $site_location_names ) ) {
                            $sublocation_name = $site_location_names[ $s[2] ];
                        } else {
                            $sublocation_name = $s[0];
                        }

                        // Strip html
                        $sublocation_name = strip_tags( $sublocation_name );

                        // Get the url
                        $url = $this->get_admin_menu_item_url( $s[2] );
                        if ( is_null( $url ) ) {
                            $url = 'not found';
                        }

                        // Strip the url
                        $url = str_replace( get_home_url().'/'.HELPDOCS_ADMIN_URL.'/', '', $url );

                        // Add the child location
                        $all_site_locations[ $url ] = $site_location_name.' — '.$sublocation_name;
                    }
                }
            }
        }
    
        // Add some CSS
        echo '<style>
        .help-docs-select-cont,
        .help-docs-number-cont {
            margin-right: 20px;
            display: inline-block;
        }
        .help-docs-select-cont label,
        .help-docs-number-cont label {
            display: inline;
            margin-right: 10px;
        }
        #doc-page-location,
        #doc-post-types,
        #doc-order,
        doc-priority {
            display: none;
        }
        .help-docs-checkbox-cont {
            display: inline-block;
            margin: 10px 20px 0 0;
        }
        .help-docs-checkbox-cont input {
            vertical-align: bottom;
        }
        .help-docs-number-cont input {
            width: 100px;
        }';
        if ( !str_starts_with( base64_decode( $site_location ), 'post.php' ) ) {
            echo '.lop-option-side {
                display: none;
            }';
        }
        echo '</style>';

        // Start the form
        echo '<form>';

            // Site location dropdown
            echo '<div id="doc-site-location" class="help-docs-select-cont">
                <label for="doc-site-location-select" class="doc-site-location-select-label">Site Location:</label>
                <select name="'.esc_attr( HELPDOCS_GO_PF ).'site_location" id="doc-site-location-select">
                    <option value="">-- None --</option>';

                    // Iter the locations
                    foreach ( $all_site_locations as $key_l => $l ) {

                        // Decode
                        $encoded = base64_encode( $key_l );

                        // Check if it's selected
                        if ( $encoded == $site_location ) {
                            $all_site_locations_selected = ' selected';
                        } else {
                            $all_site_locations_selected = '';
                        }

                        // Add the option
                        echo '<option value="'.esc_attr( $encoded ).'"'.esc_attr( $all_site_locations_selected ).'>'.esc_attr( $l ).'</option>';
                    }

            echo '</select>
            </div>';

            // Order
            echo '<div id="doc-order" class="help-docs-number-cont">
                <label for="doc-order-select" class="doc-order-select-label">Menu Order:</label>
                <input name="'.esc_attr( HELPDOCS_GO_PF ).'order" id="doc-order-select" type="number" value="'.esc_attr( $order ).'">
            </div>';

            // Page location dropdown
            echo '<div id="doc-page-location" class="help-docs-select-cont">
                <label for="doc-page-location-select" class="doc-page-location-select-label">Page Location:</label>
                <select name="'.esc_attr( HELPDOCS_GO_PF ).'page_location" id="doc-page-location-select">';

                    // Iter the locations
                    foreach ( $all_page_locations as $key_lop => $lop ) {

                        // Check if it's selected
                        if ( $key_lop == $page_location || ( !$page_location && $key_lop == 'top' ) ) {
                            $all_page_locations_selected = ' selected';
                        } else {
                            $all_page_locations_selected = '';
                        }

                        // Add the option
                        echo '<option value="'.esc_attr( $key_lop ).'"'.esc_attr( $all_page_locations_selected ).' class="lop-option-'.esc_attr( $key_lop ).'">'.esc_attr( $lop ).'</option>';
                    }

            echo '</select>
            </div>';

            // Priority dropdown
            echo '<div id="doc-priority" class="help-docs-select-cont">
                <label for="doc-priority-select" class="doc-priority-select-label">Priority:</label>
                <select name="'.esc_attr( HELPDOCS_GO_PF ).'priority" id="doc-priority-select">';

                    // Iter the priorities
                    foreach ( $all_priorities as $key_p => $p ) {

                        // Check if it's selected
                        if ( $key_p == $priority || ( !$priority && $key_p == 'default' ) ) {
                            $all_priority_selected = ' selected';
                        } else {
                            $all_priority_selected = '';
                        }

                        // Add the option
                        echo '<option value="'.esc_attr( $key_p ).'"'.esc_attr( $all_priority_selected ).' class="lop-option-'.esc_attr( $key_p ).'">'.esc_attr( $p ).'</option>';
                    }

            echo '</select>
            </div>';
        
            // Post types
            $post_page = [
                'post' => 'post',
                'page' => 'page'
            ];
            $all_post_types = array_merge( $post_page, $all_post_types );
            echo '<div id="doc-post-types" class="help-docs-checkboxes-cont">';

                foreach ( $all_post_types as $pt ) {

                    // Get the post type object
                    $post_type_obj = get_post_type_object( $pt );

                    // Skip if post type does not generate and allow a UI for managing the post type in the admin
                    if ( !$post_type_obj->show_ui ) {
                        continue;
                    }

                    // Get the name of the post type
                    if ( $post_type_obj ) {
                        $post_type_name = esc_html( $post_type_obj->labels->name );
                    } else {
                        $post_type_name = $pt;
                    }

                    // Should it be checked?
                    $checked = '';
                    if ( $post_types && in_array( $pt, $post_types ) ) {
                        $checked = 'checked';
                    }

                    // Add the checkbox
                    echo '<div class="help-docs-checkbox-cont">
                        <input type="checkbox" id="doc_post_types-'.esc_attr( $pt ).'" name="'.esc_attr( HELPDOCS_GO_PF ).'post_types[]" value="'.esc_attr( $pt ).'" '.esc_attr( $checked ).'> 
                        <span id="doc_post_types-label-'.esc_attr( $pt ).'" class="doc_post_types-labels">
                            <label for="doc_post_types-'.esc_attr( $pt ).'">'.esc_attr( $post_type_name ).'</label>
                        </span>
                    </div>';
                }

            echo '</div>';

            // Add to rest api?
            echo '<br><br><div id="doc-api" class="help-docs-select-cont">
                <label for="doc-api-select" class="doc-api-select-label">Allow Public:</label>
                <select name="'.esc_attr( HELPDOCS_GO_PF ).'api" id="doc-api-select">';

                    // Get the default
                    if ( get_option( HELPDOCS_GO_PF.'api' ) && get_option( HELPDOCS_GO_PF.'api' ) != '' ) {
                        $default_api_choice = get_option( HELPDOCS_GO_PF.'api' );
                    } else {
                        $default_api_choice = 'no';
                    }

                    // Choices
                    $api_choices = [
                        'default'   => 'Default ('.ucwords( $default_api_choice ).')',
                        'no'        => 'No',
                        'yes'       => 'Yes'
                    ];

                    // Iter the priorities
                    foreach ( $api_choices as $key_api => $a ) {

                        // Check if it's selected
                        if ( $key_api == $api || ( !$api && $key_api == 'default' ) ) {
                            $api_selected = ' selected';
                        } else {
                            $api_selected = '';
                        }

                        // Add the option
                        echo '<option value="'.esc_attr( $key_api ).'"'.esc_attr( $api_selected ).' class="lop-option-'.esc_attr( $key_api ).'">'.esc_attr( $a ).'</option>';
                    }

            echo '</select>';

            // Get the end-point url
            $api_url = help_get_api_path();

            echo '<p><em>Allowing this document to be public adds it to a <a href="'.esc_url( $api_url ).'" target="_blank">publicly accessible custom rest api end-point</a>, which can then be pulled in from other sites you manage. If allowed, make sure no sensitive information is included in your content above.</em></p>
            </div>';
    
        // End the form
        echo '</form>';
    
        // Add some JS to make checkboxes appear
        echo "<script>
        // Get the elements
        const siteLocationInput = document.getElementById( 'doc-site-location-select' );
        const order = document.getElementById( 'doc-order' );
        const pageLocation = document.getElementById( 'doc-page-location' );
        const pageLocationInput = document.getElementById( 'doc-page-location-select' );
        const optionSide = document.querySelector( 'option.lop-option-side' );
        const priority = document.getElementById( 'doc-priority' );
        const postType = document.getElementById( 'doc-post-types' );

        siteLocationInputValue = atob( siteLocationInput.value );

        // Check if the site location is NOT main, admin_bar or dashboard
        if ( siteLocationInputValue != '' && siteLocationInputValue != 'main' && siteLocationInputValue != 'admin_bar' && siteLocationInputValue != 'index.php' ) {

            // Display the page location
            pageLocation.style.display = 'inline-block';
        }

        // Check if the site location is main or admin_bar
        if ( siteLocationInputValue != '' && ( siteLocationInputValue == 'main' || siteLocationInputValue == 'admin_bar' ) ) {

            // Display the page location
            order.style.display = 'inline-block';
        }

        // Check if the site location is edit or post
        if ( siteLocationInputValue == 'edit.php' || siteLocationInputValue == 'post.php' ) {
            postType.style.display = 'block';
        } else {
            postType.style.display = 'none';
        }

        // Check if the page location is side
        if ( pageLocationInput.value == 'side' ) {
            priority.style.display = 'inline-block';
        } else {
            priority.style.display = 'none';
        }

        // Also listen for changes
        siteLocationInput.addEventListener( 'change', function () {

            // Decode
            siteLocationValue = atob( this.value );

            // Page Location container
            if ( siteLocationValue != '' && siteLocationValue != 'main' && siteLocationValue != 'admin_bar' && siteLocationValue != 'index.php' ) {
                pageLocation.style.display = 'inline-block';
            } else {
                pageLocation.style.display = 'none';
            }

            // Page Location container
            if ( siteLocationValue != '' && ( siteLocationValue == 'main' || siteLocationValue == 'admin_bar' ) ) {
                order.style.display = 'inline-block';
            } else {
                order.style.display = 'none';
            }

            // Post Type container
            if ( siteLocationValue == 'edit.php' || siteLocationValue == 'post.php' ) {
                postType.style.display = 'block';
            } else {
                postType.style.display = 'none';
            }

            // Change page location to top
            pageLocationInput.value = 'top';

            // 'side' option
            if ( siteLocationValue == 'post.php' ) {
                optionSide.style.display = 'block';
            } else {
                optionSide.style.display = 'none';
            }

            // Check if the page location is side
            if ( siteLocationValue != 'post.php' ) {
                priority.style.display = 'none';
            }
        } );

        // Side => Priority
        pageLocationInput.addEventListener( 'change', function () {
            if ( this.value == 'side' ) {
                priority.style.display = 'inline-block';
            } else {
                priority.style.display = 'none';
            }
        } );
        </script>";
    
        // echo '<label for="help_location_msg">Choose a message to display instead of the default (you may include /login/?redirect_to=<strong>{current_url}</strong> or {member_dashboard_url} in your message):</label>
        // <br><input type="text" name="help_location_msg" id="help_location_msg" value="'.esc_attr( $get_message ).'"/>';
    } // End meta_box_content()


    /**
     * Post/page meta box content
     *
     * @return void
     */
    public function post_page_meta_box_content( $post, $args ) {
        $doc = $args[ 'args' ][0];
        echo '<span style="margin-top: 10px; display: block;">'.wp_kses_post( $doc->post_content ).'</span>';
    } // End post_page_meta_box_content()


    /**
     * Get menu item url
     * Snippet courtesy of Mighty Minnow
     * https://www.mightyminnow.com/2013/12/how-to-get-urls-for-wordpress-admin-menu-items/
     *
     * @param [type] $menu_item_file
     * @param boolean $submenu_as_parent
     * @return string
     */
    public function get_admin_menu_item_url( $menu_item_file, $submenu_as_parent = true ) {
        global $menu, $submenu, $self, $typenow;
    
        $admin_is_parent = false;
        $item = '';
        $submenu_item = '';
        $url = '';
    
        // 1. Check if top-level menu item
        foreach( $menu as $key => $menu_item ) {
            if ( array_keys( $menu_item, $menu_item_file, true ) ) {
                $item = $menu[ $key ];
            }
    
            if ( $submenu_as_parent && ! empty( $submenu_item ) ) {
                $menu_hook = get_plugin_page_hook( $submenu_item[2], $item[2] );
                $menu_file = $submenu_item[2];
    
                if ( false !== ( $pos = strpos( $menu_file, '?' ) ) )
                    $menu_file = substr( $menu_file, 0, $pos );
                if ( ! empty( $menu_hook ) || ( ( 'index.php' != $submenu_item[2] ) && file_exists( WP_PLUGIN_DIR . "/$menu_file" ) && ! file_exists( ABSPATH . "/wp-admin/$menu_file" ) ) ) {
                    $admin_is_parent = true;
                    $url = 'admin.php?page=' . $submenu_item[2];
                } else {
                    $url = $submenu_item[2];
                }
            }
    
            elseif ( ! empty( $item[2] ) && current_user_can( $item[1] ) ) {
                $menu_hook = get_plugin_page_hook( $item[2], 'admin.php' );
                $menu_file = $item[2];
    
                if ( false !== ( $pos = strpos( $menu_file, '?' ) ) )
                    $menu_file = substr( $menu_file, 0, $pos );
                if ( ! empty( $menu_hook ) || ( ( 'index.php' != $item[2] ) && file_exists( WP_PLUGIN_DIR . "/$menu_file" ) && ! file_exists( ABSPATH . "/wp-admin/$menu_file" ) ) ) {
                    $admin_is_parent = true;
                    $url = 'admin.php?page=' . $item[2];
                } else {
                    $url = $item[2];
                }
            }
        }
    
        // 2. Check if sub-level menu item
        if ( ! $item ) {
            $sub_item = '';
            foreach( $submenu as $top_file => $submenu_items ) {
    
                // Reindex $submenu_items
                $submenu_items = array_values( $submenu_items );
    
                foreach( $submenu_items as $key => $submenu_item ) {
                    if ( array_keys( $submenu_item, $menu_item_file ) ) {
                        $sub_item = $submenu_items[ $key ];
                        break;
                    }
                }					
    
                if ( ! empty( $sub_item ) )
                    break;
            }
    
            // Get top-level parent item
            foreach( $menu as $key => $menu_item ) {
                if ( array_keys( $menu_item, $top_file, true ) ) {
                    $item = $menu[ $key ];
                    break;
                }
            }
    
            // If the $menu_item_file parameter doesn't match any menu item, return false
            if ( ! $sub_item )
                return false;
    
            // Get URL
            $menu_file = $item[2];
    
            if ( false !== ( $pos = strpos( $menu_file, '?' ) ) )
                $menu_file = substr( $menu_file, 0, $pos );
    
            // Handle current for post_type=post|page|foo pages, which won't match $self.
            $self_type = ! empty( $typenow ) ? $self . '?post_type=' . $typenow : 'nothing';
            $menu_hook = get_plugin_page_hook( $sub_item[2], $item[2] );
    
            $sub_file = $sub_item[2];
            if ( false !== ( $pos = strpos( $sub_file, '?' ) ) )
                $sub_file = substr($sub_file, 0, $pos);
    
            if ( ! empty( $menu_hook ) || ( ( 'index.php' != $sub_item[2] ) && file_exists( WP_PLUGIN_DIR . "/$sub_file" ) && ! file_exists( ABSPATH . "/wp-admin/$sub_file" ) ) ) {
                // If admin.php is the current page or if the parent exists as a file in the plugins or admin dir
                if ( ( ! $admin_is_parent && file_exists( WP_PLUGIN_DIR . "/$menu_file" ) && ! is_dir( WP_PLUGIN_DIR . "/{$item[2]}" ) ) || file_exists( $menu_file ) )
                    $url = add_query_arg( array( 'page' => $sub_item[2] ), $item[2] );
                else
                    $url = add_query_arg( array( 'page' => $sub_item[2] ), 'admin.php' );
            } else {
                $url = $sub_item[2];
            }
        }
    
        return esc_url( $url );
    
    } // End get_admin_menu_item_url()


    /**
     * Get admin page title from url
     *
     * @param string $url
     * @return string|false
     */
    public function get_admin_page_title_from_url( $url ) {
        // Return main, index.php, post.php, edit.php
        if ( $url == base64_encode( 'main' ) ) {
            return 'Main Documentation Page';
        } elseif ( $url == base64_encode( 'admin_bar' ) ) {
            return 'Admin Bar';
        } elseif ( $url == base64_encode( 'index.php' ) ) {
            return 'Dashboard';
        } elseif ( $url == base64_encode( 'post.php' ) ) {
            return 'Post/Page Edit Screen';
        } elseif ( $url == base64_encode( 'edit.php' ) ) {
            return 'Post/Page Admin List Screen';
        }

        // Location name changes
        $site_location_names = apply_filters( HELPDOCS_GO_PF.'location_names', [
            'edit-comments.php' => 'Comments',
            'site-editor.php' => 'Editor',
            'plugins.php' => 'Plugins',
            'dev-debug-tools' => 'Dev Debug Tools',
            'admin.php?page=dev-debug-tools&tab=logs' => 'Logs'
        ] );

        // Let's call the menu and submenu
        global $menu, $submenu;

        // Store the parent
        $parent_id = false;
        $parent = false;

        // Store the child
        $child = false;
        $child_default_name = false;

        // Iter the submenu array
        foreach ( $submenu as $k => $sm ) {

            // Iter each submenu
            foreach ( $sm as $s ) {
                $encoded_s = base64_encode( str_replace( '&', '&#038;', $s[2] ) );

                // Check for the url
                if ( $encoded_s == $url ) {

                    // Set the parent id
                    $parent_id = $k;

                    // Set the child default name that is not changed
                    $child_default_name = $s[0];

                    // Set the child name
                    if ( array_key_exists( base64_decode( $url ), $site_location_names ) ) {
                        $child = $site_location_names[ base64_decode( $url ) ];
                    } else {
                        $child = $child_default_name;
                    }
                }
            }
        }

        // First submenu var
        $first_submenu = false;

        // If we found a submenu
        if ( $child ) {

            // Iter the menu array
            foreach ( $menu as $m ) {

                // Get the parent id
                if ( $parent_id == $m[2] ) {
                    $encoded_m = base64_encode( str_replace( '&', '&#038;', $m[2] ) );

                    // Set the parent url
                    if ( $encoded_m == $url ) {
                        $first_submenu = true;
                    }

                    // Set the parent name
                    if ( array_key_exists( $m[2], $site_location_names ) ) {
                        $parent = $site_location_names[ $m[2] ];
                    } else {
                        $parent = $m[0];
                    }
                }
            }

        // If we did not find a submenu
        } else {

            // Iter the menu array
            foreach ( $menu as $m ) {
                
                // Does the url start with admin.php?page=
                if ( str_starts_with( base64_decode( $url ), 'admin.php?page=' ) ) {
                    $text_domain = str_replace( 'admin.php?page=', '', base64_decode( $url ) );

                    // Check for textdomain
                    if ( $text_domain == $m[2] ) {
                        
                        // Set the parent name
                        if ( array_key_exists( base64_decode( $url ), $site_location_names ) ) {
                            $parent = $site_location_names[ base64_decode( $url ) ];
                        } else {
                            $parent = $m[0];
                        }
                    }
                } else {

                    $encoded_m = base64_encode( str_replace( '&', '&#038;', $m[2] ) );

                    // Check for the url
                    if ( $encoded_m == $url ) {

                        // Set the parent name
                        if ( array_key_exists( base64_decode( $url ), $site_location_names ) ) {
                            $parent = $site_location_names[ base64_decode( $url ) ];
                        } else {
                            $parent = $m[0];
                        }
                    }
                }
            }
        }
        
        // Now put them together
        if ( $parent ) {
            
            // Add the parent
            $results = $parent;

            // Did we find a child?
            if ( $child ) {

                // Are we on the first page?
                if ( $first_submenu && $child_default_name ) {
                    $child = $child_default_name;
                }

                // If so, add the child name
                $results .= '<br>⤷ '.$child;
            }

            // Return the full title
            return $results;

        } else {
            return base64_decode( $url );
        }
    } // End get_admin_page_title_from_url()


    /**
     * Save the post data
     *
     * @param int $post_id
     * @return void
     */
    public function save_post( $post_id ) {

        // Check if our nonce is set.
        if ( !isset( $_POST[ 'help_location_nonce' ] ) ) {
            return;
        }
     
        // Verify that the nonce is valid.
        if ( !wp_verify_nonce( $_POST[ 'help_location_nonce' ], 'help_location_nonce' ) ) {
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

        // Site Location
        $site_location = isset( $_POST[ HELPDOCS_GO_PF.'site_location' ] ) ? sanitize_text_field( $_POST[ HELPDOCS_GO_PF.'site_location' ] ) : false;
        $decoded_site_location = base64_decode( $site_location );

        // Page Location and Priority
        if ( $decoded_site_location != 'main' && $decoded_site_location != 'index.php' ) {
            $page_location = isset( $_POST[ HELPDOCS_GO_PF.'page_location' ] ) ? sanitize_text_field( $_POST[ HELPDOCS_GO_PF.'page_location' ] ) : false;
            $priority = isset( $_POST[ HELPDOCS_GO_PF.'priority' ] ) ? sanitize_text_field( $_POST[ HELPDOCS_GO_PF.'priority' ] ) : false;
        } else {
            $page_location = false;
            $priority = false;
        }
        
        // Post Types
        if ( $decoded_site_location == 'edit.php' || $decoded_site_location == 'post.php' ) {
            $post_types = isset( $_POST[ HELPDOCS_GO_PF.'post_types' ] ) ? (array) $_POST[ HELPDOCS_GO_PF.'post_types' ] : [];
            $post_types = array_map( 'esc_attr', $post_types );
            $post_types = serialize( $post_types );
        } else {
            $post_types = false;
        }

        // Order
        if ( $decoded_site_location == 'main' ) {
            $order = isset( $_POST[ HELPDOCS_GO_PF.'order' ] ) ? filter_var( $_POST[ HELPDOCS_GO_PF.'order' ], FILTER_SANITIZE_NUMBER_INT ) : false;
        } else {
            $order = false;
        }

        // API
        $api = isset( $_POST[ HELPDOCS_GO_PF.'api' ] ) ? sanitize_text_field( $_POST[ HELPDOCS_GO_PF.'api' ] ) : false;

        // Values
        $values = [
            HELPDOCS_GO_PF.'site_location'  => $site_location,
            HELPDOCS_GO_PF.'page_location'  => $page_location,
            HELPDOCS_GO_PF.'post_types'     => $post_types,
            HELPDOCS_GO_PF.'order'          => $order,
            HELPDOCS_GO_PF.'priority'       => $priority,
            HELPDOCS_GO_PF.'api'            => $api,
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
        $columns[ HELPDOCS_GO_PF.'desc' ]          = __( 'Description', 'admin-help-docs' );
        $columns[ HELPDOCS_GO_PF.'site_location' ] = __( 'Site Location', 'admin-help-docs' );
        $columns[ HELPDOCS_GO_PF.'page_location' ] = __( 'Page Location', 'admin-help-docs' );
        $columns[ HELPDOCS_GO_PF.'order' ]         = __( 'Order', 'admin-help-docs' );
        $columns[ HELPDOCS_GO_PF.'priority' ]      = __( 'Priority', 'admin-help-docs' );
        $columns[ HELPDOCS_GO_PF.'post_types' ]    = __( 'Post Types', 'admin-help-docs' );
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
        // Description
        if ( HELPDOCS_GO_PF.'desc' === $column ) {
            echo esc_html( get_the_excerpt( $post_id ) );
        }

        // Site Location
        if ( HELPDOCS_GO_PF.'site_location' === $column ) {
            $site_location = esc_attr( get_post_meta( $post_id, HELPDOCS_GO_PF.'site_location', true ) ) ?? '';
            $admin_page_title = $this->get_admin_page_title_from_url( $site_location );
            echo wp_kses_post( $admin_page_title );
        }

        // Page Location
        if ( HELPDOCS_GO_PF.'page_location' === $column ) {
            $page_location = esc_attr( get_post_meta( $post_id, HELPDOCS_GO_PF.'page_location', true ) ) ?? '';
            $page_location = ucwords( $page_location );
            if ( $page_location == 'Contextual' ) {
                $page_location .= ' Help Tab';
            }
            echo esc_html( $page_location );
        }

        // Order
        if ( HELPDOCS_GO_PF.'order' === $column ) {
            echo filter_var( get_post_meta( $post_id, HELPDOCS_GO_PF.'order', true ), FILTER_SANITIZE_NUMBER_INT ) ?? '';
        }

        // Priority
        if ( HELPDOCS_GO_PF.'priority' === $column ) {
            $priority = esc_attr( get_post_meta( $post_id, HELPDOCS_GO_PF.'priority', true ) ) ?? '';
            $priority = ucwords( $priority );
            echo esc_html( $priority );
        }

        // Post Types
        if ( HELPDOCS_GO_PF.'post_types' === $column ) {
            if ( get_post_meta( $post_id, HELPDOCS_GO_PF.'post_types', true ) && get_post_meta( $post_id, HELPDOCS_GO_PF.'post_types', true ) != '' ) {
                $post_types = get_post_meta( $post_id, HELPDOCS_GO_PF.'post_types', true );
                $post_types = unserialize( $post_types );
                $post_type_labels = [];
                foreach ( $post_types as $post_type ) {
                    $post_type_obj = get_post_type_object( $post_type );
                    $post_type_labels[] = $post_type_obj->labels->singular_name;
                }
                echo esc_html( implode( ', ', $post_type_labels ) );
            } else {
                echo '';
            }
        }
    } // End admin_column_content()


    /**
     * Make admin columns sortable
     *
     * @param array $columns
     * @return array
     */
    public function sort_columns( $columns ){
        $columns[ HELPDOCS_GO_PF.'site_location' ] = HELPDOCS_GO_PF.'site_location';
        $columns[ HELPDOCS_GO_PF.'page_location' ] = HELPDOCS_GO_PF.'page_location';
        $columns[ HELPDOCS_GO_PF.'order' ]         = HELPDOCS_GO_PF.'order';
        $columns[ HELPDOCS_GO_PF.'priority' ]      = HELPDOCS_GO_PF.'priority';
        return $columns;
    } // End sort_columns()


    /**
     * Dashboard widgets
     *
     * @return void
     */
    public function dashboard_widgets() {
        // Get all the docs that need to go on the dashboard
        $args = [
            'posts_per_page'    => -1,
            'post_status'       => 'publish',
            'post_type'         => self::$post_type,
            'meta_key'		    => HELPDOCS_GO_PF.'site_location',
            'meta_value'	    => base64_encode( 'index.php' ),
            'meta_compare'	    => '=',
        ];
        $docs = get_posts( $args );

        // Also get the imports
        $imports = helpdocs_get_imports( $args );

        // Merge them together
        if ( !empty( $imports ) ) {
            $docs = array_merge( $docs, $imports );
        }

        // Did we find any?
        if ( !empty( $docs ) ) {

            // Iter the docs
            foreach ( $docs as $doc ) {

                // The edit link
                if ( helpdocs_user_can_edit() ) {
                    if ( isset( $doc->feed_id ) && $doc->feed_id != '' ) {
                        $post_id = $doc->feed_id;
                    } else {
                        $post_id = $doc->ID;
                    }
                    $incl_edit = ' <span>[<a href="/'.esc_attr( HELPDOCS_ADMIN_URL ).'/post.php?post='.absint( $post_id ).'&action=edit" style="display: contents;">edit</a>]</span>';
                } else {
                    $incl_edit = '';
                }

                // The title
                $title = $doc->post_title.$incl_edit;

                // Add the widget
                wp_add_dashboard_widget( HELPDOCS_GO_PF.absint( $doc->ID ), $title, [ $this, 'dashboard_content' ], null, $doc, 'normal', 'high' );
            }
        }
    } // End dashboard_widgets()
        

    /**
     * Dashboard content
     *
     * @return void
     */
    public function dashboard_content( $var, $args ) {
        $doc = $args[ 'args' ];
        echo wp_kses_post( $doc->post_content );
    } // End dashboard_content()


    /**
     * Add to other pages
     *
     * @return void
     */
    public function add_to_other_pages() {
        // Get the colors
        $HELPDOCS_COLORS = new HELPDOCS_COLORS();
        $color_ac = $HELPDOCS_COLORS->get( 'ac' );
        $color_bg = $HELPDOCS_COLORS->get( 'bg' );
        $color_ti = $HELPDOCS_COLORS->get( 'ti' );
        $color_fg = $HELPDOCS_COLORS->get( 'fg' );
        $color_cl = $HELPDOCS_COLORS->get( 'cl' );

        // Do not add to dashboard
        global $current_screen;
        if ( $current_screen->id == 'dashboard' ) {

            echo '<style>
            div[id^="'.esc_attr( HELPDOCS_GO_PF ).'"] {
                background-color: '.esc_attr( $color_bg ).' !important;
                color: '.esc_attr( $color_fg ).' !important;
            }
            div[id^="'.esc_attr( HELPDOCS_GO_PF ).'"] .order-higher-indicator,
            div[id^="'.esc_attr( HELPDOCS_GO_PF ).'"] .order-lower-indicator,
            div[id^="'.esc_attr( HELPDOCS_GO_PF ).'"] .toggle-indicator {
                color: '.esc_attr( $color_ac ).' !important;
            }
            div[id^="'.esc_attr( HELPDOCS_GO_PF ).'"] .postbox-header h2 {
                color: '.esc_attr( $color_ti ).' !important;
            }
            div[id^="'.esc_attr( HELPDOCS_GO_PF ).'"] .postbox-header h2 span {
                color: revert !important;
            }
            div[id^="'.esc_attr( HELPDOCS_GO_PF ).'"] a {
                color: '.esc_attr( $color_cl ).' !important;
            }
            </style>';
            return;
        }

        // Get params
        $url = $_SERVER[ 'REQUEST_URI' ];
        $url = str_replace( '/'.HELPDOCS_ADMIN_URL.'/', '', $url );

        // Get all the docs that need to go in the contextual help menu
        $args = [
            'posts_per_page'    => -1,
            'post_status'       => 'publish',
            'post_type'         => self::$post_type,
            'meta_key'		    => HELPDOCS_GO_PF.'site_location',
            'meta_value'	    => base64_encode( 'main' ),
            'meta_compare'	    => '!=',
        ];
        $docs = get_posts( $args );

        // Also get the imports
        $imports = helpdocs_get_imports( $args );

        // Merge them together
        if ( !empty( $imports ) ) {
            $docs = array_merge( $docs, $imports );
        }

        // Did we find any?
        if ( !empty( $docs ) ) {

            // Only show things once
            $already_posted = false;

            // Iter the docs
            foreach ( $docs as $doc ) {

                // Site location
                $site_location_var = HELPDOCS_GO_PF.'site_location';
                $site_location = base64_decode( esc_attr( $doc->$site_location_var ) );
                $site_location = preg_replace( '/[^A-Za-z0-9 ._\-\+\&\=\?]/', '', $site_location );
                $site_location = str_replace( '&038', '&', $site_location );

                // Post types
                $post_types_var = HELPDOCS_GO_PF.'post_types';
                $post_types = $doc->$post_types_var;
                $post_types = unserialize( $post_types );

                // Continue?
                $continue = false;

                // Post/pages admin list screen
                if ( $site_location == 'edit.php' && 
                     str_starts_with( $url, 'edit.php' ) && 
                     in_array( $current_screen->post_type, $post_types ) ) {
                    $continue = true;

                // Post/pages edit screen
                } elseif ( $site_location == 'post.php' && 
                           ( str_starts_with( $url, 'post.php' ) || 
                           str_starts_with( $url, 'post-new.php' ) ) && 
                           in_array( $current_screen->post_type, $post_types ) ) {
                    $continue = true;

                // User profile
                } elseif ( $site_location == 'profile.php' && 
                           str_starts_with( $url, 'profile.php' ) ) {
                    $continue = true;

                // Other pages
                } elseif ( $site_location != 'edit.php' &&
                           $site_location != 'post.php' &&
                           $site_location != 'profile.php' &&
                           $url == $site_location ) {
                    $continue = true;
                }

                // Stop them in their tracks
                if ( !$continue ) {
                    continue;
                }

                // Page location
                $page_location_var = HELPDOCS_GO_PF.'page_location';
                $page_location = esc_attr( $doc->$page_location_var );

                // Contextual help
                if ( $page_location == 'contextual' ) {

                    $args = [ 'color_ac' => $color_ac, 'color_bg' => $color_bg, 'color_ti' => $color_ti, 'color_fg' => $color_fg ];
                    if ( !$already_posted ) {
                        echo '<style>
                        #contextual-help-back {
                            background-color: '.esc_attr( $args[ 'color_bg' ] ).' !important;
                        }
                        .help-tab-content.active {
                            color: '.esc_attr( $args[ 'color_fg' ] ).' !important;
                        }
                        .contextual-help-tabs .active {
                            border-left-color: '.esc_attr( $args[ 'color_ac' ] ).' !important;
                            background-color: '.esc_attr( $args[ 'color_bg' ] ).' !important;
                        }
                        .contextual-help-tabs .active a {
                            color: '.esc_attr( $args[ 'color_ti' ] ).' !important;
                        }
                        </style>';
                    }
                    $already_posted = true;

                    $current_screen->add_help_tab( [
                        'id'       => HELPDOCS_GO_PF.'_'.$doc->ID, 
                        'title'    => $doc->post_title, 
                        'content'  => $this->add_to_other_pages_content( $doc ),
                    ] );

                // Top of page
                } elseif ( $page_location == 'top' ) {

                    // If post edit screen and using gutenberg editor, things are different
                    if ( str_starts_with( $site_location, 'post.php' ) && is_gutenberg() ) {
                        continue;

                    // Otherwise just add as a notice
                    } else {
                        $args = [ 'doc' => $doc, 'color_ac' => $color_ac, 'color_bg' => $color_bg, 'color_ti' => $color_ti, 'color_fg' => $color_fg ];
                        add_action( 'admin_notices', function() use ( $args ) {
                            $logo = get_option( HELPDOCS_GO_PF.'logo', HELPDOCS_PLUGIN_IMG_PATH.'logo.png' );
                            if ( $logo != '' ) {
                                $incl_logo = '<img src="'.$logo.'" style="height: 20px; width: auto; margin-right: 10px;">';
                            } else {
                                $incl_logo = '';
                            }
                            $doc = $args[ 'doc' ];
                            echo '<div class="notice notice-info is-dismissible" style="background-color: '.esc_attr( $args[ 'color_bg' ] ).'; color: '.esc_attr( $args[ 'color_fg' ] ).'; border-left-color: '.esc_attr( $args[ 'color_ac' ] ).';">
                                <h2 style="color: '.esc_attr( $args[ 'color_ti' ] ).' !important; margin-top: 10px !important;">'.wp_kses_post( $incl_logo ).''.esc_html( $doc->post_title ).'</h2>
                                <p>'.wp_kses_post( $doc->post_content ).'</p>
                            </div>';
                        } );
                    }

                // Bottom of page
                }  elseif ( $page_location == 'bottom' ) {

                    // Check if a edit screen
                    // The content is added as a meta box in post_page_meta_box_content()
                    if ( str_starts_with( $site_location, 'post.php' ) && !is_gutenberg() ) {
                        if ( !$already_posted ) {
                            echo '<style>
                            #'.esc_attr( HELPDOCS_GO_PF ).absint( $doc->ID ).' {
                                background-color: '.esc_attr( $color_bg ).' !important; 
                                color: '.esc_attr( $color_fg ).' !important;
                            }
                            #'.esc_attr( HELPDOCS_GO_PF ).absint( $doc->ID ).' .postbox-header h2 {
                                color: '.esc_attr( $color_ti ).' !important;
                            }
                            #'.esc_attr( HELPDOCS_GO_PF ).absint( $doc->ID ).' .handle-order-higher, 
                            #'.esc_attr( HELPDOCS_GO_PF ).absint( $doc->ID ).' .handle-order-lower,
                            #'.esc_attr( HELPDOCS_GO_PF ).absint( $doc->ID ).' .toggle-indicator {
                                color: '.esc_attr( $color_ac ).' !important;
                            }
                            </style>';
                        }
                        $already_posted = true;

                    // Skip if gutenberg post
                    } elseif ( str_starts_with( $site_location, 'post.php' ) && is_gutenberg() ) {
                        continue;

                    // Otherwise just add to footer
                    } else {
                        if ( !$already_posted ) {
                            echo '<style>
                            #wpbody-content {
                                position: relative;
                                padding-bottom: 0px;
                            }
                            </style>';
                            $bottom_margin = '20px';
                        } else {
                            $bottom_margin = '0';
                        }
                        $args = [ 'doc' => $doc, 'color_ac' => $color_ac, 'color_bg' => $color_bg, 'color_ti' => $color_ti, 'color_fg' => $color_fg, 'bottom_margin' => $bottom_margin ];
                        add_action( 'admin_notices', function( $data ) use ( $args ) {
                            $logo = get_option( HELPDOCS_GO_PF.'logo', HELPDOCS_PLUGIN_IMG_PATH.'logo.png' );
                            if ( $logo != '' ) {
                                $incl_logo = '<img src="'.$logo.'" style="height: 20px; width: auto; margin-right: 10px;">';
                            } else {
                                $incl_logo = '';
                            }
                            $doc = $args[ 'doc' ];
                            echo '<div class="doc-location-footer" style="position: absolute; bottom: 0; left: 0; margin: 20px 20px '.esc_attr( $args[ 'bottom_margin' ] ).' 0px; padding: 10px 20px; background-color: '.esc_attr( $args[ 'color_bg' ] ).'; color: '.esc_attr( $args[ 'color_fg' ] ).'; box-shadow: 0 1px 1px rgb(0 0 0 / 4%); border: 1px solid #c3c4c7; border-left-color: '.esc_attr( $args[ 'color_ac' ] ).'; border-left-width: 4px;">
                                <h2 style="margin-top: 5px; color: '.esc_attr( $args[ 'color_ti' ] ).';">'.wp_kses_post( $incl_logo ).''.esc_html( $doc->post_title ).'</h2>
                                <p>'.wp_kses_post( $doc->post_content ).'</p>
                            </div>';
                        }, 1 );
                        if ( !$already_posted ) {
                            echo '<script>
                            jQuery( document ).ready( function( $ ) {
                                var outerHeight = 0;
                                $( ".doc-location-footer" ).each( function() {
                                    var thisHeight = $( this ).outerHeight();
                                    outerHeight += thisHeight;
                                    $( this ).css( {
                                        "bottom" : outerHeight - 40
                                    } );
                                    console.log( thisHeight );
                                } );
                                $( "#wpbody-content" ).css( {
                                    "padding-bottom" : outerHeight + 140 + "px"
                                } );
                                console.log( "total: " + outerHeight );
                            } );
                            </script>';
                        }
                        $already_posted = true;
                    }

                // Side of post/page edit screen
                } elseif ( $page_location == 'side' && str_starts_with( $site_location, 'post.php' ) ) {
                    if ( !$already_posted ) {
                        echo '<style>
                        #'.esc_attr( HELPDOCS_GO_PF ).absint( $doc->ID ).' {
                            background-color: '.esc_attr( $color_bg ).' !important; 
                            color: '.esc_attr( $color_fg ).' !important;
                        }
                        #'.esc_attr( HELPDOCS_GO_PF ).absint( $doc->ID ).' .postbox-header h2 {
                            color: '.esc_attr( $color_ti ).' !important;
                        }
                        #'.esc_attr( HELPDOCS_GO_PF ).absint( $doc->ID ).' .handle-order-higher, 
                        #'.esc_attr( HELPDOCS_GO_PF ).absint( $doc->ID ).' .handle-order-lower,
                        #'.esc_attr( HELPDOCS_GO_PF ).absint( $doc->ID ).' .toggle-indicator {
                            color: '.esc_attr( $color_ac ).' !important;
                        }
                        </style>';
                    }
                    $already_posted = true;
                }
            }
        }
    } // End add_to_other_pages()


    /**
     * Add to other pages - content
     *
     * @return void
     */
    public function add_to_other_pages_content( $doc ) {
        return '<span style="margin: 10px 0; display: block;">'.wp_kses_post( $doc->post_content ).'</span>';
    } // End add_to_other_pages_content()


    /**
     * Post/page edit screen content for gutenberg only
     * Top, Bottom, and Contextual view only
     * Side is added via meta_boxes()
     *
     * @return void
     */
    public function gutenberg_content() {
        // Make sure we are only viewing gutenberg
        global $current_screen;
        if ( $current_screen->id != 'post' && !is_gutenberg() ) {
            return;
        }

        // Start the args to get the docs
        $args = [
            'posts_per_page'    => -1,
            'post_status'       => 'publish',
            'post_type'         => self::$post_type,
            'meta_key'		    => HELPDOCS_GO_PF.'site_location',
            'meta_value'	    => base64_encode( 'post.php' ),
            'meta_compare'	    => '=',
        ];

        // Get the posts
        $docs = get_posts( $args );

        // Also get the imports
        $imports = helpdocs_get_imports( $args );

        // Merge them together
        if ( !empty( $imports ) ) {
            $docs = array_merge( $docs, $imports );
        }

        // Iter the docs
        foreach ( $docs as $doc ) {
            
            // Skip if the page location is not top or bottom
            $page_location_var = HELPDOCS_GO_PF.'page_location';
            $page_location = $doc->$page_location_var;
            if ( $page_location != 'top' && $page_location != 'bottom' && $page_location != 'contextual' ) {
                continue;
            }

            // Get the post type
            $post_types_var = HELPDOCS_GO_PF.'post_types';
            $post_types = unserialize( $doc->$post_types_var );
            if ( empty( $post_types ) || !in_array( $current_screen->post_type, $post_types ) ) {
                continue;
            }

            // The content
            $content = str_replace( "'", "\\'", $doc->post_content );           // Add \ to single quotes
            $content = preg_replace( "/\r\n<ul>\r\n/", "<ul>", $content );      // Replace line breaks wrapping <ul>
            $content = preg_replace( "/\r\n<\/ul>/", "</ul>", $content );       // Replace line breaks before </ul>
            $content = preg_replace( "/<\/li>\r\n/", "</li>", $content );       // Replace line breaks before </ul>
            $content = preg_replace( "/\r\n/", "<br>", $content );              // Replace double line breaks with single
            $content = nl2br( $content );                                       // Convert the rest of the line breaks

            // Get the colors
            $HELPDOCS_COLORS = new HELPDOCS_COLORS();
            $color_bg = $HELPDOCS_COLORS->get( 'bg' );
            $color_ti = $HELPDOCS_COLORS->get( 'ti' );
            $color_fg = $HELPDOCS_COLORS->get( 'fg' );
            $color_cl = $HELPDOCS_COLORS->get( 'cl' );
            ?>
            <script>
            // Load JavaScript after the page has loaded
            window.addEventListener( 'load', function () {

                // Get the editor wrapper
                var editorWrapper = document.querySelector( '.interface-interface-skeleton__content' );

                // Make sure the editor exists
                if ( editorWrapper ) {

                    // Create a new div
                    const div = document.createElement( 'div' );
                    div.setAttribute( 'id', '<?php echo esc_attr( HELPDOCS_GO_PF.$doc->ID ); ?>' );
                    div.style.backgroundColor = '<?php echo esc_attr( $color_bg ); ?>';
                    div.style.color = '<?php echo esc_attr( $color_fg ); ?>';
                    div.style.padding = '20px';

                    // Create the title
                    const title = document.createElement( 'div' );
                    title.style.color = '<?php echo esc_attr( $color_ti ); ?>';
                    title.style.fontSize = '1.2rem';
                    title.style.fontWeight = 'bold';
                    title.innerHTML = '<?php echo esc_html( $doc->post_title ); ?>';

                    // Create the content
                    const content = document.createElement( 'div' );
                    content.innerHTML = '<?php echo wp_kses_post( $content ); ?>';

                    // Add the title and content to the div
                    div.append( title );
                    div.append( content );

                    // Top or bottom?
                    const pageLocation = '<?php echo esc_attr( $page_location ); ?>';

                    // Top
                    if ( pageLocation == 'top' ) {
                        editorWrapper.insertBefore( div, editorWrapper.firstChild );

                    // Bottom
                    } else if ( pageLocation == 'bottom' ) {
                        // const editor = document.querySelector( '.edit-post-visual-editor' );
                        editorWrapper.appendChild( div, editor );

                    // Contextual
                    } else {
                        console.log( 'Is contextual ');

                        // Create a button on top bar
                        const button = document.createElement( 'button' );
                        button.setAttribute( 'id', '<?php echo esc_attr( HELPDOCS_GO_PF.'help_button' ); ?>' );
                        button.setAttribute( 'type', 'button' );
                        button.setAttribute( 'aria-pressed', 'false' );
                        button.setAttribute( 'data-toolbar-item', 'true' );
                        button.setAttribute( 'class', 'components-button is-primary' );
                        button.innerHTML = 'Help';

                        button.style.backgroundColor = '<?php echo esc_attr( $color_cl ); ?>';
                        button.style.color = '<?php echo esc_attr( $color_bg ); ?>';
                        button.style.padding = '16px';

                        // Add the button to the toolbar
                        const toolbarSettings = document.querySelector( '.edit-post-header__settings' );
                        toolbarSettings.insertBefore( button, toolbarSettings.firstChild );

                        // Create a popover slot
                        const slot = document.createElement( 'div' );
                        slot.setAttribute( 'class', 'popover-slot' );
                        
                        // Now add the popover slot after the button
                        const toolbar = document.querySelector( '.edit-post-header' );
                        toolbar.append( slot );

                        // Create a content container
                        const container = document.createElement( 'div' );
                        container.setAttribute( 'class', 'components-popover components-dropdown__content <?php echo esc_attr( HELPDOCS_GO_PF ); ?>_popover' );
                        container.style.position = 'absolute';
                        container.style.right = '300px';
                        container.style.top = '60px';
                        container.style.opacity = '1';
                        container.style.transform = 'translateY(0em) scale(0) translateZ(0px)';
                        container.style.transformOrigin = 'top center 0px';
                        container.style.transitionTimingFunction = 'ease-in';
                        container.style.transition = '0.1s';
                        
                        // Create an inner container
                        const _content = document.createElement( 'div' );
                        _content.setAttribute( 'class', 'components-popover__content' );
                        _content.style.width = '600px';
                        _content.style.overflow = 'auto';
                        _content.style.backgroundColor = '<?php echo esc_attr( $color_bg ); ?>';
                        _content.style.color = '<?php echo esc_attr( $color_fg ); ?>';
                        _content.style.padding = '16px';

                        // Create an inner wrapper
                        const _wrapper = document.createElement( 'div' );
                        _wrapper.setAttribute( 'class', '<?php echo esc_attr( HELPDOCS_GO_PF ); ?>_wrapper' );
                        _wrapper.setAttribute( 'role', 'note' );
                        _wrapper.setAttribute( 'aria-label', 'Help Documents' );

                        // Add space to the title
                        title.style.marginBottom = '10px';

                        // Put the containers together
                        _wrapper.append( title );
                        _wrapper.append( content );
                        _content.append( _wrapper );
                        container.append( _content );

                        // Listen for a click
                        button.addEventListener( 'click', function() {
                            const infoPopup = document.querySelector( '<?php echo esc_attr( HELPDOCS_GO_PF ); ?>_wrapper' );
                            if ( button.getAttribute( 'aria-pressed' ) == 'false' ) {
                                console.log( 'open' );
                                slot.appendChild( container, slot );
                                setTimeout( function() {
                                    container.style.transform = 'translateY(0em) scale(1) translateZ(0px)';
                                    container.style.transformOrigin = 'top center 0px';
                                }, 100 ); 
                                
                                button.setAttribute( 'aria-pressed', 'true' );
                                
                            } else {
                                console.log( 'close' );
                                container.style.transform = 'translateY(0em) scale(0) translateZ(0px)';
                                container.style.transformOrigin = 'top center 0px';
                                container.remove();
                                button.setAttribute( 'aria-pressed', 'false' );
                            }
                        });
                    }
                }
            } );
            </script>
            <?php
        }
    }


    /**
     * Update order via Ajax
     *
     * @return void
     */
    public function update_order() {
        // First verify the nonce
        if ( !wp_verify_nonce( $_REQUEST[ 'nonce' ], 'drag-doc-toc' ) ) {
            exit( 'No naughty business please' );
        }

        // Get the order
        $order = isset( $_REQUEST[ 'order' ] ) ? sanitize_text_field( $_REQUEST[ 'order' ] ) : false;

        // If order exists
        if ( $order ) {
            
            // Split
            $orders = explode( '&', $order );

            // Iter the ordered items
            for ( $o = 0; $o < count( $orders ); $o++ ) {

                // Get the doc id
                $doc_id = str_replace( 'item[]=', '', $orders[$o] );

                // Get the position
                $position = $o;

                // Update the doc
                update_post_meta( $doc_id, HELPDOCS_GO_PF.'order', $position );
            }

            // Return success
            $result[ 'type' ] = 'success';

        // Otherwise return error
        } else {
            $result[ 'type' ] = 'error';
        }

        // Pass to ajax
        if( !empty( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) && strtolower( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) == 'xmlhttprequest' ) {
            echo json_encode( $result );
        } else {
            $referer = filter_input( INPUT_SERVER, 'HTTP_REFERER', FILTER_SANITIZE_URL );
            header( 'Location: '.$referer );
        }

        // Stop
        die();
    } // End update_order()


    /**
     * Enqueue scripts
     *
     * @param string $hook
     * @return void
     */
    public function enqueue_scripts( $screen ) {
        // Get the options page slug
        $options_page = 'toplevel_page_'.HELPDOCS_TEXTDOMAIN;

        // Allow for multisite
        if ( is_network_admin() ) {
            $options_page .= '-network';
        }

        // Are we on the documentation page?
        if ( $screen != $options_page || ( $screen == $options_page && helpdocs_get( 'tab', '!=', 'documentation' ) ) ) {
            return;
        }

        // Handle
        $handle = HELPDOCS_GO_PF.'script';

        // Sorting draggable docs
        wp_register_script( $handle, HELPDOCS_PLUGIN_JS_PATH.'doc-sorting.js', [ 'jquery', 'jquery-ui-sortable' ] );
        wp_localize_script( $handle, 'docSortingAjax', [ 'ajaxurl' => admin_url( 'admin-ajax.php' ) ] );
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_script( $handle );
    }
}