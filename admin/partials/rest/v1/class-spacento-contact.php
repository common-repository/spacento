<?php
/**
 * Send email to admin with details of interested people.
 *
 * @link       http://spacento.com
 * @since      1.0.0
 *
 * @package    Spacento
 * @subpackage Spacento/public
 */

/**
 * Send email to admin with details of interested people.
 *
 * @package    Spacento
 * @subpackage Spacento/public
 * @author     Sproutient <dev@sproutient.com>
 */
class Spacento_Contact {

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
	public function contact( $request ) {

		$data['result']         = false;
		$data['data']           = array();
		$data['data']['errors'] = array();
		$data['data']['error']  = '';

		$nonce = '';
		$name  = '';
		$email = '';
		$phone = '';

		if ( isset( $request['nonce'] ) ) {
			$nonce = $request['nonce'];
		}
		if ( isset( $request['name'] ) ) {
			$name = $request['name'];
		}
		if ( isset( $request['email'] ) ) {
			$email = $request['email'];
		}
		if ( isset( $request['phone'] ) ) {
			$phone = $request['phone'];
		}
		if ( isset( $request['property'] ) ) {
			$property = $request['property'];
		}

		if ( $nonce ) :

			if ( '' !== $name && '' !== $email && '' !== $phone ) :

				if ( ctype_alpha( $name ) && filter_var( $email, FILTER_VALIDATE_EMAIL ) && is_numeric( $phone ) ) {

					$to       = sanitize_email( get_option( 'admin_email' ) );
					$subject  = $property . ' : ' . $name . esc_html__( ' is interested.', 'spacento' );
					$message  = '<p>' . esc_html__( 'Name : ', 'spacento' ) . $name . '</p>';
					$message .= '<p>' . esc_html__( 'Email : ', 'spacento' ) . $email . '</p>';
					$message .= '<p>' . esc_html__( 'Phone : ', 'spacento' ) . $phone . '</p>';
					$headers  = array( 'Content-Type: text/html; charset=UTF-8' );
					if ( wp_mail( $to, $subject, $message, $headers, array( '' ) ) ) {
						$data['result'] = true;
					} else {
						$data['data']['error'] = esc_html__( 'Something went wrong, Please try again.', 'spacento' );
					}
				} else {

					if ( ! ctype_alpha( $name ) ) {
						$data['data']['errors']['name'] = esc_html__( 'Name should be only letters.', 'spacento' );
					}

					if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
						$data['data']['errors']['email'] = esc_html__( 'Email does not seem to be valid.', 'spacento' );
					}

					if ( ! is_numeric( $phone ) ) {
						$data['data']['errors']['phone'] = esc_html__( 'Phone should be numbers.', 'spacento' );
					}
				}

			else :

				if ( '' === $name ) {
					$data['data']['errors']['name'] = esc_html__( 'Name is required.', 'spacento' );
				}
				if ( '' === $email ) {
					$data['data']['errors']['email'] = esc_html__( 'Email is required.', 'spacento' );
				}
				if ( '' === $phone ) {
					$data['data']['errors']['phone'] = esc_html__( 'Phone is required.', 'spacento' );
				}

			endif;

		else :

			$data['data']['error'] = esc_html__( 'unAuthorized', 'spacento' );

		endif;

		$response = $this->aux->prepare( $data, $request );

		// Return all of our post response data.
		return $response;

	}

}
