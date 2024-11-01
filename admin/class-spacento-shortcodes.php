<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       sproutient.com
 * @since      1.0.0
 *
 * @package    Spacento
 * @subpackage Spacento/admin
 */

/**
 * Add shortcoder.
 */
class Spacento_Shortcodes {

	use Spacento_Property_Renderer;

	/**
	 * Get sub string.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $output       The string.
	 * @param      string $max_char     length of string to output.
	 */
	public function limitedstring( $output, $max_char = 100 ) {

		$output = str_replace( ']]>', ']]&gt;', $output );
		$output = wp_strip_all_tags( $output );
		$output = strip_shortcodes( $output );

		if ( ( strlen( $output ) > $max_char ) ) {

			$output = substr( $output, 0, $max_char );

			return $output;

		} else {

			return $output;

		}

	}

	/**
	 * Get sub string.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $atts Widget Attributes.
	 */
	public function spacento_func( $atts ) {

		$instance = shortcode_atts(
			array(
				'propertytype'     => '',
				'propertylocation' => '',
				'property'         => '',
				'propertylayout'   => 'one',
				'title'            => '',
			),
			$atts
		);

		$args = array();

		return $this->property_display( $args, $instance );

	}

}

