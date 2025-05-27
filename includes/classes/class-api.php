<?php
/**
 * Rest API End-Point Class
 */

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Initiate the class
 */
new HELPDOCS_API;


/**
 * Main plugin class.
 */
class HELPDOCS_API {

    /**
     * API Namespace
     *
     * @var string
     */
    public $api_namespace;


    /**
     * API Base
     *
     * @var string
     */
	public $base;


    /**
     * API Version
     *
     * @var string
     */
	public $api_version;


    /**
	 * Constructor
	 */
	public function __construct() {

        // Add the variables
        $this->api_namespace = HELPDOCS_TEXTDOMAIN.'/v';
		$this->base = 'docs';
		$this->api_version = '1';

        // Register the routes
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );

	} // End __construct()


    /**
     * Register the routes
     *
     * @return void
     */
    public function register_routes() {
        // Put the namespace together with the version
		$namespace = $this->api_namespace.$this->api_version;
		
        // All docs
        register_rest_route( $namespace, $this->base, [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ $this, 'get_all' ],
            'permission_callback' => '__return_true'
        ] );

        // Single docs
        register_rest_route( $namespace, $this->base.'/(?P<doc_id>[\d]+)', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ $this, 'get_single' ],
            'permission_callback' => '__return_true'
        ] );
	} // End register_routes()
	

	/**
     * Rest API Callback
     *
     * @return void
     */
    public function get_all() {
        // Otherwise, only get all published docs
        $args = [
            'post_type'      => 'help-docs',
            'post_status'    => 'publish',
            'posts_per_page' => -1
        ];
        
        // Store them here
        $items = [];
        
        // If we found docs
        if ( $docs = get_posts( $args ) ) {

            // Get the default api choice
            if ( get_option( HELPDOCS_GO_PF.'api' ) && get_option( HELPDOCS_GO_PF.'api' ) != '' ) {
                $default_api_choice = get_option( HELPDOCS_GO_PF.'api' );
            } else {
                $default_api_choice = 'no';
            }
            
            // Iter them
            foreach ( $docs as $doc ) {

                // The key
                $api = HELPDOCS_GO_PF.'api';

                if ( ( !isset( $doc->$api ) && $default_api_choice == 'yes' ) ||
                     ( $doc->$api == 'default' && $default_api_choice == 'yes' ) ||
                     $doc->$api == 'yes' ) {
                
                    // Fetch the doc
                    $items[] = $this->return( $doc );
                }
            }
        }

        // Return the array of items
        return rest_ensure_response( $items );
    } // End get_help_docs()


    /**
     * Rest API Callback
     *
     * @return void
     */
    public function get_single( $request ) {
        // Check if we are only getting a single doc
        if ( $id = $request->get_param( 'doc_id' ) ) {

            // Get the doc
            $doc = get_post( $id );

            // Verify that doc exists
            if ( $doc ) {

                // Get the default api choice
                if ( get_option( HELPDOCS_GO_PF.'api' ) && get_option( HELPDOCS_GO_PF.'api' ) != '' ) {
                    $default_api_choice = get_option( HELPDOCS_GO_PF.'api' );
                } else {
                    $default_api_choice = 'no';
                }

                // The key
                $api = HELPDOCS_GO_PF.'api';

                // Don't allow if default is no
                if ( ( !isset( $doc->$api ) && $default_api_choice == 'yes' ) ||
                     ( $doc->$api == 'default' && $default_api_choice == 'yes' ) ||
                     $doc->$api == 'yes' ) {
                    
                    // Fetch the doc
                    $result = $this->return( $doc );

                    // Return the object
                    return rest_ensure_response( $result );
                }
            }
        }

        // Else return false
        return __( 'Sorry, you do no have permission to access this help document.', 'admin-help-docs' );
    } // End get_help_doc()

    
    /**
     * What to return for a doc
     *
     * @param object $doc
     * @return array
     */
    public function return( $doc ) {
        // Created
        $created_by = get_userdata( $doc->post_author );

        // Get the modified by
        if ( $doc->_edit_last ) {
            $modified_by = get_userdata( $doc->_edit_last );
            $incl_modified = esc_attr( $modified_by->display_name );
        } else {
            $incl_modified = false;
        }

        // Return
        $result = [
            'ID'                => $doc->ID,
            'title'             => $doc->post_title,
            'created_by'        => esc_attr( $created_by->display_name ),
            'publish_date'      => $doc->post_date,
            'modified_date'     => $doc->post_modified,
            'modified_by'       => $incl_modified,
            'desc'              => $doc->post_excerpt,
            'content'           => $doc->post_content,
        ];

        // Additional fields
        $add_fields = [ 'custom', 'addt_params', 'order', 'page_location', 'post_types', 'priority', 'site_location' ];

        // Add the fields
        foreach ( $add_fields as $field ) {

            // The key
            $key = HELPDOCS_GO_PF.$field;
            
            // Add to array
            $result[ $field ] = $doc->$key;
        }

        // Allow final modification via filter
        return apply_filters( 'helpdocs_exports_object', $result, $doc );

        // Return the array
        return $result;
    } // End return()
}


/**
 * Get the full API path
 *
 * @return string
 */
function help_get_api_path( $doc_id = null ) {
    // Put the namespace together with the version
    $HELPDOCS_API = new HELPDOCS_API();
    $namespace = $HELPDOCS_API->api_namespace.$HELPDOCS_API->api_version;
    if ( !is_null( $doc_id ) ) {
        $incl_id = '/'.$doc_id;
    } else {
        $incl_id = '';
    }
    return home_url( 'wp-json/'.$namespace.'/'.$HELPDOCS_API->base.$incl_id );
} // End get_api_path()