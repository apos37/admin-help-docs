<?php
/**
 * Rest API End-Point Class
 * API: /wp-json/admin-help-docs/v1/docs
 * API: /wp-json/admin-help-docs/v2/docs
 */

namespace PluginRx\AdminHelpDocs;

if ( ! defined( 'ABSPATH' ) ) exit;

class API {

    /**
     * API Namespace
     *
     * @var string
     */
    private static $api_namespace;


    /**
     * API Base
     *
     * @var string
     */
	private static $base;


    /**
     * The single instance of the class
     *
     * @var self|null
     */
    private static ?API $instance = null;


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

        self::$api_namespace = Bootstrap::textdomain() . '/v';
		self::$base = 'docs';

		add_action( 'rest_api_init', [ $this, 'register_routes' ] );

	} // End __construct()


    /**
     * Get the API path
     *
     * @param int|null $doc_id
     * @return string
     */
    public static function api_path( $doc_id = null ) {
        $incl_id = $doc_id ? '/' . $doc_id : '';
        return home_url( 'wp-json/' . self::$api_namespace . '2/' . self::$base . $incl_id );
    } // End api_path()


    /**
     * Register API Routes
     *
     * @return void
     */
    public function register_routes() {
        foreach ( [ '1', '2' ] as $version ) {
            $namespace = self::$api_namespace . $version;

            register_rest_route( $namespace, self::$base, [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_docs' ],
                'permission_callback' => [ $this, 'check_api_permissions' ], // Updated
                'args'                => [
                    'version' => [ 'default' => $version ],
                ]
            ] );

            register_rest_route( $namespace, self::$base . '/(?P<doc_id>[\d]+)', [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_docs' ],
                'permission_callback' => [ $this, 'check_api_permissions' ], // Updated
                'args'                => [
                    'version' => [ 'default' => $version ],
                    'doc_id'  => [
                        'validate_callback' => function( $param ) {
                            return is_numeric( $param );
                        },
                        'sanitize_callback' => 'absint',
                        'required'          => true,
                    ],
                ]
            ] );
        }
    } // End register_routes()


    /**
     * Check permissions for the remote API
     * * @param WP_REST_Request $request
     * @return bool|WP_Error
     */
    public function check_api_permissions( $request ) {
        $stored_key = get_option( 'helpdocs_api_key' );
        
        // If no global API key is set, anyone can attempt to access public docs
        if ( empty( $stored_key ) ) {
            return true;
        }

        $incoming_key = $request->get_header( 'X-HelpDocs-API-Key' );

        if ( ! $incoming_key || $incoming_key !== $stored_key ) {
            return new \WP_Error( 
                'rest_forbidden', 
                esc_html__( 'A valid API Key is required to access this endpoint.', 'admin-help-docs' ), 
                [ 'status' => 401 ] 
            );
        }

        return true;
    } // End check_api_permissions()
	

	/**
     * Unified Callback for All and Single
     * 
     * @param object $request
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_docs( $request ) {
        $version = $request->get_param( 'version' );
        $doc_id  = $request->get_param( 'doc_id' );
        
        // Get the global default once
        $global_default = get_option( 'helpdocs_api', 'no' );
        $is_default_on  = ( $global_default === 'yes' || $global_default === '1' || $global_default === true );

        if ( $doc_id ) {
            $doc = get_post( $doc_id );
            if ( ! $doc || $doc->post_type !== HelpDocs::$post_type ) {
                return new \WP_Error( 'no_doc', 'Document not found', [ 'status' => 404 ] );
            }

            if ( ! $this->is_api_enabled( $doc->ID, $is_default_on ) ) {
                return new \WP_Error( 'forbidden', 'API access disabled for this doc', [ 'status' => 403 ] );
            }

            return rest_ensure_response( $this->prepare_item( $doc, $version ) );
        }

        $docs = get_posts( [
            'post_type'      => HelpDocs::$post_type,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ] );

        $items = [];
        foreach ( $docs as $id ) {
            if ( $this->is_api_enabled( $id, $is_default_on ) ) {
                $items[] = $this->prepare_item( get_post( $id ), $version );
            }
        }

        return rest_ensure_response( $items );
    } // End get_docs()


    /**
     * Check if API is enabled for a doc
     *
     * @param int $post_id
     * @return bool
     */
    private function is_api_enabled( $post_id, $is_default_on ) {
        $api = get_post_meta( $post_id, 'helpdocs_api', true );
        
        // If set to 'default' or not set, use the pre-fetched global default
        if ( ! $api || $api === 'default' ) {
            return $is_default_on;
        }

        return $api === 'yes';
    } // End is_api_enabled()


    /**
     * Prepare a doc item for API response
     *
     * @param object $doc
     * @param string $version
     * @return array
     */
    public function prepare_item( $doc, $version = '2' ) {
        $created_by = get_userdata( $doc->post_author );
        
        $edit_last = get_post_meta( $doc->ID, '_edit_last', true );
        $modified_by = $edit_last ? get_userdata( $edit_last ) : false;

        $result = [
            'ID'            => $doc->ID,
            'title'         => $doc->post_title,
            'created_by'    => $created_by ? esc_attr( $created_by->display_name ) : '',
            'publish_date'  => $doc->post_date,
            'modified_date' => $doc->post_modified,
            'modified_by'   => $modified_by ? esc_attr( $modified_by->display_name ) : false,
            'desc'          => $doc->post_excerpt,
            'content'       => $doc->post_content,
            'taxonomies'    => []
        ];

        $locations = get_post_meta( $doc->ID, 'helpdocs_locations', true );

        // Normalize if doc hasn't been saved with the new repeater yet
        if ( ! is_array( $locations ) || empty( $locations ) ) {
            $locations = [[
                'site_location' => get_post_meta( $doc->ID, 'helpdocs_site_location', true ) ?: '',
                'page_location' => get_post_meta( $doc->ID, 'helpdocs_page_location', true ) ?: '',
                'custom'        => get_post_meta( $doc->ID, 'helpdocs_custom', true ) ?: '',
                'addt_params'   => (bool) get_post_meta( $doc->ID, 'helpdocs_addt_params', true ),
                'post_types'    => Helpers::normalize_meta_array( get_post_meta( $doc->ID, 'helpdocs_post_types', true ) ),
                'order'         => intval( get_post_meta( $doc->ID, 'helpdocs_order', true ) ),
                'toc'           => filter_var( get_post_meta( $doc->ID, 'helpdocs_toc', true ), FILTER_VALIDATE_BOOLEAN ),
                'css_selector'  => get_post_meta( $doc->ID, 'helpdocs_css_selector', true ) ?: '',
            ]];
        }

        /**
         * VERSION BRANCHING
         */
        if ( $version === '1' ) {
            // V1: Only return the first location at the top level (Legacy behavior)
            if ( ! empty( $locations[0] ) ) {
                foreach ( $locations[0] as $key => $val ) {
                    if ( ! isset( $result[ $key ] ) ) {
                        $result[ $key ] = $val;
                    }
                }
            }
        } else {
            // V2: Return the full locations array (Modern behavior)
            $result[ 'locations' ] = $locations;
        }

        $result[ 'view_roles' ] = array_values( array_filter( (array) get_post_meta( $doc->ID, 'helpdocs_view_roles', true ) ) );
        $result[ 'editor_type' ] = get_post_meta( $doc->ID, 'helpdocs_editor_type', true ) ?: ( get_post_meta( $doc->ID, 'classic-editor-remember', true ) ?: '' );

        // Taxonomies
        $terms = get_the_terms( $doc->ID, 'help-docs-folder' );
        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            foreach ( $terms as $term ) {
                $result[ 'taxonomies' ][ 'help-docs-folder' ][] = [
                    'name'        => $term->name,
                    'slug'        => $term->slug,
                    'parent'      => $term->parent ? get_term( $term->parent, 'help-docs-folder' )->slug : 0,
                    'description' => $term->description,
                ];
            }
        }

        return apply_filters( 'helpdocs_api_doc_object_out', $result, $doc, $version );
    } // End prepare_item()
    
}


API::instance();