<?php
/**
 * Fetch property IDs.
 *
 * @link       http://spacento.com
 * @since      1.0.0
 *
 * @package    Spacento
 * @subpackage Spacento/public
 */

/**
 * Fetch property IDs.
 *
 * @package    Spacento
 * @subpackage Spacento/public
 * @author     Sproutient <dev@sproutient.com>
 */
class Spacento_Property_Ids {

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
	public function properties( $request ) {

		$data['result']         = false;
		$data['data']           = array();
		$data['data']['errors'] = array();
		$data['data']['error']  = '';

		$property_type = '';
		if ( isset( $request['type'] ) ) {
			$property_type = $request['type'];
			$property_type = (int) $property_type;
		}
		$property_location = '';
		if ( isset( $request['location'] ) ) {
			$property_location = $request['location'];
			$property_location = (int) $property_location;
		}

		$data['data']['aur']                     = array();
		$data['data']['aur']['propertyType']     = $property_type;
		$data['data']['aur']['propertyLocation'] = $property_location;

		$data['data']['payload'][] = array(

			'value' => 'all',
			'label' => esc_html__( 'All', 'spacento' ),

		);

		if ( ! empty( $property_type ) || ! empty( $property_location ) ) :

			if ( empty( $property_type ) && ! empty( $property_location ) ) :

				$prop_array = array(
					'post_type' => 'spacento_property',
					'tax_query' => array(
						array(
							'taxonomy' => 'spacento-property-location',
							'field'    => 'term_id',
							'terms'    => array( $property_location ),
						),
					),
					'fields'    => 'ids',
				);

			elseif ( ! empty( $property_type ) && empty( $property_location ) ) :

				$prop_array = array(
					'post_type' => 'spacento_property',
					'tax_query' => array(
						array(
							'taxonomy' => 'spacento-property-type',
							'field'    => 'term_id',
							'terms'    => array( $property_type ),
						),
					),
					'fields'    => 'ids',
				);

			elseif ( ! empty( $property_type ) && ! empty( $property_location ) ) :

				$prop_array = array(
					'post_type' => 'spacento_property',
					'tax_query' => array(
						'relation' => 'AND',
						array(
							'taxonomy' => 'spacento-property-type',
							'field'    => 'term_id',
							'terms'    => array( $property_type ),
						),
						array(
							'taxonomy' => 'spacento-property-location',
							'field'    => 'term_id',
							'terms'    => array( $property_location ),
						),
					),
					'fields'    => 'ids',
				);

			endif;

			$temp_posts = get_posts( $prop_array );

			if ( ! empty( $temp_posts ) ) :
				foreach ( $temp_posts as $id ) :

					$temp_post                 = get_post( $id );
					$temp_array                = array();
					$temp_array['value']       = esc_html( $temp_post->ID );
					$temp_array['label']       = esc_html( $temp_post->post_title );
					$data['data']['payload'][] = $temp_array;

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
