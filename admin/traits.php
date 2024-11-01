<?php
/**
 * REST Aux.
 *
 * @link       http://spacento.com
 * @since      1.0.0
 *
 * @package    Spacento
 * @subpackage Spacento/admin
 */

/**
 * Trait to e used for blocks/shorcodes.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Spacento
 * @subpackage Spacento/public
 * @author     Sproutient <dev@sproutient.com>
 */
trait Spacento_Property_Renderer {

	/**
	 * Prepare response.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $args   The reponse data.
	 * @param      string $instance The REST Request.
	 */
	public function property_display( $args, $instance ) {

		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		if ( ! isset( $before_widget ) ) {
			$before_widget = '';
		}
		if ( ! isset( $after_widget ) ) {
			$after_widget = '';
		}
		if ( ! isset( $before_title ) ) {
			$before_title = '';
		}
		if ( ! isset( $after_title ) ) {
			$after_title = '';
		}

		$property_type     = '';
		$property_location = '';
		$property          = '';
		$property_layout   = '';

		$temp_posts = array();

		if ( array_key_exists( 'propertytype', $instance ) ) {
			$property_type = sanitize_text_field( $instance['propertytype'] );
		}

		if ( array_key_exists( 'propertylocation', $instance ) ) {
			$property_location = sanitize_text_field( $instance['propertylocation'] );
		}

		if ( array_key_exists( 'property', $instance ) ) {
			$property = sanitize_text_field( $instance['property'] );
		}

		if ( array_key_exists( 'propertylayout', $instance ) ) {
			$property_layout = sanitize_text_field( $instance['propertylayout'] );
		}

		if ( empty( $property_type ) && empty( $property_location ) && empty( $property ) ) :

			$prop_array = array(
				'post_type' => 'spacento_property',
				'fields'    => 'ids',
			);

		elseif ( ! empty( $property ) && 'all' !== $property ) :

			$temp_posts[] = get_post( (int) $property );

		else :

			if ( empty( $property_type ) && ! empty( $property_location ) ) :

				$property_location = str_replace( 'all', '', $property_location );

				if ( empty( $property_type ) ) {
					$prop_array = array(
						'post_type' => 'spacento_property',
						'fields'    => 'ids',
					);
				} else {
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
				}

			elseif ( ! empty( $property_type ) && empty( $property_location ) ) :

				$property_type = str_replace( 'all', '', $property_type );
				if ( empty( $property_type ) ) {
					$prop_array = array(
						'post_type' => 'spacento_property',
						'fields'    => 'ids',
					);
				} else {
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
				}

			elseif ( ! empty( $property_type ) && ! empty( $property_location ) ) :

				$property_type     = str_replace( 'all', '', $property_type );
				$property_location = str_replace( 'all', '', $property_location );

				if ( empty( $property_type ) && empty( $property_location ) ) {
					$prop_array = array(
						'post_type' => 'spacento_property',
						'fields'    => 'ids',
					);
				} elseif ( empty( $property_type ) ) {
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
				} elseif ( empty( $property_location ) ) {
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
				} else {
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
				}

			endif;
			$temp_posts = get_posts( $prop_array );

		endif;

		$output  = '';
		$output .= $before_widget;
		$output .= '<div class="spacento-widget-container">';
		if ( ! empty( $title ) ) {
			esc_html( $before_title ) . esc_html( $title ) . esc_html( $after_title );
		}

		if ( ! empty( $temp_posts ) ) :
			foreach ( $temp_posts as $id ) :
				$temp_post = get_post( $id );

				$property_gallery         = sanitize_text_field( get_post_meta( $id, 'spacento-property-gallery', true ) );
				$current_property_gallery = '';
				if ( ! empty( $property_gallery ) ) :

					$current_property_gallery = rtrim( $property_gallery, ',' );
					$current_property_gallery = explode( ',', $current_property_gallery );

							endif;
				$property_price    = '';
				$property_currency = '';
				$property_price    = get_post_meta( $id, 'spacento-property-price', true );
				$property_currency = get_post_meta( $id, 'spacento-property-currency', true );

				$temp_post_array          = array();
				$temp_post_array['title'] = $temp_post->post_title;
				if ( ! empty( $current_property_gallery ) ) {
					$temp_post_array['image'] = wp_get_attachment_url( $current_property_gallery[0] );
				}
				$temp_post_array['text'] = $temp_post->post_excerpt;
				$temp_post_array['url']  = get_permalink( $id );

				if ( empty( $temp_post_array['text'] ) ) {
					$temp_post_array['text'] = esc_html( $this->limitedstring( $temp_post->post_content, 200 ) );
				}

				if ( 'two' === $instance['propertylayout'] ) {
					$output .= '<div class="spacento-widget-property-two-item">';
				} else {
					$output .= '<div class="spacento-widget-property-one-item">';
				}

				if ( array_key_exists( 'image', $temp_post_array ) ) :
					$output .= '<div class="spacento-property-media">';
					$output .= '<img src="' . esc_url( $temp_post_array['image'] ) . '"/>';
					$output .= '</div>';
				endif;
				$output .= '<div class="spacento-property-content">';
				$output .= '<h2>' . esc_html( $temp_post_array['title'] ) . '</h2>';
				$output .= '<p>' . esc_html( $temp_post_array['text'] ) . '</p>';
				if ( '' !== $property_price ) :
					$output .= '<div class="spacento-property-price">';
					$output .= '<p class=""><span>' . esc_html( $property_currency ) . '</span>' . esc_html( $property_price ) . '</p>';
					$output .= '</div>';
				endif;
				$output .= '<p><a href="' . esc_url( $temp_post_array['url'] ) . '" >' . esc_html__( 'View Details', 'spacento' ) . '</a></p>';
				$output .= '</div>';
				$output .= '</div>';

			endforeach;
		endif;

		$output .= '</div>';
		$output .= $after_widget;

		return $output;

	}

}
