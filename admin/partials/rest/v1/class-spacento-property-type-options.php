<?php
/**
 * Fetch property type options.
 *
 * @link       http://spacento.com
 * @since      1.0.0
 *
 * @package    Spacento
 * @subpackage Spacento/public
 */

/**
 * Fetch property type options.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Spacento
 * @subpackage Spacento/public
 * @author     Sproutient <dev@sproutient.com>
 */
class Spacento_Property_Type_Options {

	/**
	 * The helper functions for REST API.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Class    $aux    Helper functions.
	 */
	private $aux;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		require_once SPACENTO_PATH . 'admin/partials/rest/v1/class-spacento-rest-aux.php';

		$this->aux = new Spacento_REST_Aux();

	}

	/**
	 * Get user details.
	 *
	 * @param WP_REST_Request $request Current request.
	 */
	public function types( $request ) {

		$data['result']         = false;
		$data['data']           = array();
		$data['data']['errors'] = array();
		$data['data']['error']  = '';

		$property_type = '';
		if ( isset( $request['id'] ) ) {
			$property_type = $request['id'];
		}

		$data['data']['aur']                 = array();
		$data['data']['aur']['propertyType'] = $property_type;

		$data['data']['payload'][] = array(

			'value' => 'all',
			'label' => esc_html__( 'All', 'spacento' ),

		);

		if ( ! empty( $property_type ) ) :

			$temp_posts = get_posts(
				array(
					'post_type' => 'spacento_property',
					'tax_query' => array(
						array(
							'taxonomy' => 'spacento-property-location',
							'field'    => 'term_id',
							'terms'    => array( $property_type ),
						),
					),
					'fields'    => 'ids',
				)
			);

			if ( ! empty( $temp_posts ) ) :
				foreach ( $temp_posts as $id ) :
					$temp_tax = get_the_terms( $id, 'spacento-property-type' );
					if ( ! empty( $temp_tax ) ) :
						foreach ( $temp_tax as $term ) :
							$temp_array                = array();
							$temp_array['value']       = esc_html( $term->term_id );
							$temp_array['label']       = esc_html( $term->name );
							$data['data']['payload'][] = $temp_array;
						endforeach;
					endif;
				endforeach;
			endif;

			$data['result'] = true;

		else :

			$data['data']['error'] = esc_html__( 'Empty', 'spacento' );

		endif;

		$response = $this->aux->prepare( $data, $request );

		// Return all of our post response data.
		return $response;

	}

}
