<?php
/**
 * Wallkit: REST API controller class
 *
 * @since 3.3.7
 */

/**
 * Registers new REST API endpoints.
 *
 * @since 3.3.7
 */
class Wallkit_REST_Controller extends WP_REST_Controller {
	/**
	 * The namespace for the REST API routes.
	 *
     * @since 3.3.7
	 *
	 * @var string
	 */
	public $namespace = 'wallkit/v1';

	/**
	 * Register the new routes and endpoints.
	 *
     * @since 3.3.7
	 */
	public function register_routes()
    {
		register_rest_route( $this->namespace, '/get-content-part', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_content_part' ),
				'permission_callback' => array( $this, 'permissions_check' )
			),
		) );
	}

	/**
	 * Get full content is user has access.
	 *
     * @since 3.3.7
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response.
	 */
	public function get_content_part( $request ) {
        $params = $request->get_query_params();
        if( !isset($params['post_id']) ) {
            return new WP_REST_Response(
                [
                    'message'   => 'Invalid post ID'
                ], 404);
        }

        $post = get_post($params['post_id']);
        if( !$post ) {
            return new WP_REST_Response(
                [
                    'message'   => 'Invalid post ID'
                ], 404);
        }

        try {
            $access = apply_filters('wallkit_check_post_access', $post);
        } catch (\Exception $exception) {
            $access = [
                'allow' => false,
                'message' => 'Error check post access'
            ];
        }

        try {
            if( !$access || (isset($access['allow']) && !$access['allow']) ) {
                return new WP_REST_Response([
                    'allowed'   => false,
                    'data'    => $access
                ], 200);
            }

            $skip_paragraphs = (int)Wallkit_Wp_Settings::get_options()['wk_free_paragraph'];
            $formatted_content = Wallkit_Wp_Admin::get_formatted_content($post->post_content);
            $content_part = Wallkit_Wp_Admin::get_content_body_paragraph($formatted_content, $skip_paragraphs);

            return new WP_REST_Response( [
                'allowed'           => $access['allow'],
                'data'              => $access,
                'wp_content_part'   => apply_filters('the_content', $content_part),
            ], 200);
        }
        catch (\Exception $exception) {
            return new WP_REST_Response( ['allowed' => false, 'message' => 'Check access failed'], 409);
        }
	}

    /**
     * Check to see if the current user is allowed to use this endpoint.
     *
     * @since 3.3.7
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return bool.
     */
    public function permissions_check( $request ) {
        // Check user is user has access
        $request_headers = $request->get_headers();
        return ( !empty($request_headers['wk_token'])
            && !empty($request_headers['firebase_token']) ) || !empty($request_headers['wk_session']);
    }
}
