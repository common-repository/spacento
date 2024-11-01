<?php
/**
 * Blocks for the plugin.
 *
 * @link       sproutient.com
 * @since      1.0.0
 *
 * @package    Spacento
 * @subpackage Spacento/admin
 */

/**
 * Add Property Blocks.
 *
 * @package    Spacento
 * @subpackage Spacento/admin
 * @author     Sproutient <dev@sproutient.com>
 */
class Spacento_Blocks {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the JavaScript/CSS for the blocks in editor.
	 *
	 * @since    1.0.0
	 */
	public function admin_block_assets() {

		$this->blockvariables['api']                               = array();
		$this->blockvariables['api']['getPropertyLocationOptions'] = esc_url( get_rest_url( null, 'spacento/v1/getproprtylocationoptions' ) );
		$this->blockvariables['api']['getPropertyTypeOptions']     = esc_url( get_rest_url( null, 'spacento/v1/getproprtytypeoptions' ) );
		$this->blockvariables['api']['getPropertyIds']             = esc_url( get_rest_url( null, 'spacento/v1/getpropertyids' ) );

		$this->blockvariables['properties'] = array();

		$this->blockvariables['properties']['types']     = array();
		$this->blockvariables['properties']['locations'] = array();
		$this->blockvariables['properties']['property']  = array();

		$terms = get_terms(
			array(
				'taxonomy'   => 'spacento-property-type',
				'hide_empty' => false,
			)
		);

		$this->blockvariables['properties']['types'][] = array(
			'value' => 'all',
			'label' => esc_html__( 'All', 'spacento' ),
		);

		foreach ( $terms as $term ) {

			$temp_array = array();

			$temp_array['value'] = esc_html( $term->term_id );
			$temp_array['label'] = esc_html( $term->name );

			$this->blockvariables['properties']['types'][] = $temp_array;

		}

		$terms = get_terms(
			array(
				'taxonomy'   => 'spacento-property-location',
				'hide_empty' => false,
			)
		);

		$this->blockvariables['properties']['locations'][] = array(
			'value' => 'all',
			'label' => esc_html__( 'All', 'spacento' ),
		);

		foreach ( $terms as $term ) {

			$temp_array = array();

			$temp_array['value'] = esc_html( $term->term_id );
			$temp_array['label'] = esc_html( $term->name );

			$this->blockvariables['properties']['locations'][] = $temp_array;

		}

		$this->blockvariables['properties']['propertiesids'][] = array(
			'value' => 'all',
			'label' => esc_html__( 'All', 'spacento' ),
		);

		$prop_array = array(
			'post_type' => 'spacento_property',
			'fields'    => 'ids',
		);

		$temp_posts = get_posts( $prop_array );
		foreach ( $temp_posts as $post ) {

			$temp_post           = get_post( $post );
			$temp_array          = array();
			$temp_array['value'] = esc_html( $temp_post->ID );
			$temp_array['label'] = esc_html( $temp_post->post_title );

			$this->blockvariables['properties']['propertiesids'][] = $temp_array;

		}

		$this->blockvariables['pluginUrl'] = esc_url( SPACENTO_URL );

		wp_enqueue_style( $this->plugin_name . '-editor', esc_url( SPACENTO_URL ) . 'admin/css/spacento-blocks.css', array( 'wp-edit-blocks' ), $this->version, 'all' );
		wp_register_script( $this->plugin_name . '-editor', esc_url( SPACENTO_URL ) . 'admin/js/spacento-blocks.js', array( 'jquery', 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components' ), $this->version, true );
		wp_localize_script( $this->plugin_name . '-editor', 'spacentoBlockVariables', $this->blockvariables );
		wp_enqueue_script( $this->plugin_name . '-editor' );

	}

	/**
	 * Register the JavaScript/CSS for the blocks.
	 *
	 * @since    1.0.0
	 */
	public function block_assets() {

		wp_enqueue_style( $this->plugin_name . '-blocks-css', esc_url( SPACENTO_URL ) . 'public/css/spacento-blocks.css', array(), $this->version, 'all' );

		wp_register_script( $this->plugin_name . '-blocks', esc_url( SPACENTO_URL ) . 'public/js/spacento-blocks.js', array( 'jquery', 'wp-blocks', 'wp-polyfill' ), $this->version, true );
		wp_enqueue_script( $this->plugin_name . '-blocks' );

	}

	/**
	 * Register blocks.
	 *
	 * @since    1.0.0
	 */
	public function register_spacento_blocks() {

		register_block_type(
			'spacento/properties',
			array(

				'attributes'      => array(
					'propertyType'     => array(
						'type'    => 'string',
						'default' => 'all',
					),
					'propertyLocation' => array(
						'type'    => 'string',
						'default' => 'all',
					),
					'property'         => array(
						'type' => 'string',
					),
					'layout'           => array(
						'type'    => 'string',
						'default' => 'layoutOne',
					),
				),
				'render_callback' => array( $this, 'spacento_properties_render' ),

			)
		);

	}

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
	 * Register blocks.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $args The block attributes.
	 */
	public function spacento_property_render( $args ) {

		$block_content = '';

		$block_content .= '<div class="spacento-property-container">';

		$block_content .= '<div class="spacento-property-media">';
		$block_content .= '<img src="' . esc_url( $args['image'] ) . '"/>';
		$block_content .= '</div>';

		$block_content .= '<div class="spacento-property-content">';

		$block_content .= '<h2>' . esc_html( $args['title'] ) . '</h2>';
		$block_content .= '<p>' . esc_html( $args['text'] ) . '</p>';
		if ( '' !== $args['price'] ) :
			$block_content .= '<div class="spacento-property-price">';
			$block_content .= '<p><span>' . esc_html( $args['currency'] ) . '</span>' . esc_html( $args['price'] ) . '</p>';
			$block_content .= '</div">';
		endif;
		$block_content .= '<p><a href="' . esc_url( $args['url'] ) . '" >' . esc_html__( 'View Details', 'spacento' ) . '</a></p>';

		$block_content .= '</div>';

		$block_content .= '</div>';

		return $block_content;

	}

	/**
	 * Register blocks.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $args The block attributes.
	 */
	public function spacento_properties_render( $args ) {

		$block_content = '';
		$block_layout  = 'layoutOne';
		if ( array_key_exists( 'layout', $args ) && '' !== $args['layout'] ) {
			$block_layout = wp_strip_all_tags( $args['layout'] );
		}

		$block_content_actual = '';

		$property_type     = '';
		$property_location = '';

		$fetchall_properties_switch = false;
		if (

			( array_key_exists( 'propertyType', $args ) && '' !== $args['propertyType'] && 'all' === $args['propertyType'] ) &&
			( array_key_exists( 'propertyLocation', $args ) && '' !== $args['propertyLocation'] && 'all' === $args['propertyLocation'] )

		) {

			$fetchall_properties_switch = true;

		}

		if ( array_key_exists( 'propertyType', $args ) && '' !== $args['propertyType'] && 'all' !== $args['propertyType'] ) {
			$property_type = $args['propertyType'];
			$property_type = absint( (int) $property_type );
		}

		if ( array_key_exists( 'propertyLocation', $args ) && '' !== $args['propertyLocation'] && 'all' !== $args['propertyLocation'] ) {
			$property_location = $args['propertyLocation'];
			$property_location = absint( (int) $property_location );
		}

		if ( array_key_exists( 'property', $args ) && '' !== $args['property'] && 'all' !== $args['property'] ) :

			$temp_postid = $args['property'];
			$temp_postid = absint( (int) $temp_postid );
			$temp_post   = get_post( $temp_postid );

			$property_gallery  = sanitize_text_field( get_post_meta( $temp_postid, 'spacento-property-gallery', true ) );
			$property_price    = '';
			$property_currency = '';
			$property_price    = get_post_meta( $temp_postid, 'spacento-property-price', true );
			$property_currency = get_post_meta( $temp_postid, 'spacento-property-currency', true );

			if ( ! empty( $property_gallery ) ) :

				$current_property_gallery = rtrim( $property_gallery, ',' );
				$current_property_gallery = explode( ',', $current_property_gallery );

			endif;

			$temp_post_array          = array();
			$temp_post_array['title'] = esc_html( $temp_post->post_title );
			$temp_post_array['image'] = esc_url( SPACENTO_URL ) . 'public/images/property.webp';
			if ( isset( $current_property_gallery ) && is_array( $current_property_gallery ) ) {
				$temp_post_array['image'] = wp_get_attachment_url( $current_property_gallery[0] );
			}
			$temp_post_array['text']     = esc_html( $temp_post->post_excerpt );
			$temp_post_array['url']      = esc_url( get_permalink( $temp_postid ) );
			$temp_post_array['currency'] = esc_html( $property_currency );
			$temp_post_array['price']    = esc_html( $property_price );

			if ( empty( $temp_post_array['text'] ) ) {
				$temp_post_array['text'] = esc_html( $this->limitedstring( $temp_post->post_content, 200 ) );
			}

			$block_content_actual = $this->spacento_property_render( $temp_post_array );

		else :

			if ( ! empty( $property_type ) || ! empty( $property_location ) || $fetchall_properties_switch ) :

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

				elseif ( $fetchall_properties_switch ) :

					$prop_array = array(
						'post_type' => 'spacento_property',
						'fields'    => 'ids',
					);

				endif;

				$temp_posts = get_posts( $prop_array );

				if ( ! empty( $temp_posts ) ) :
					foreach ( $temp_posts as $id ) :

						$temp_post = get_post( $id );

						$property_gallery = sanitize_text_field( get_post_meta( $id, 'spacento-property-gallery', true ) );

						$property_price    = '';
						$property_currency = '';
						$property_price    = get_post_meta( get_the_ID(), 'spacento-property-price', true );
						$property_currency = get_post_meta( get_the_ID(), 'spacento-property-currency', true );

						if ( ! empty( $property_gallery ) ) :

							$current_property_gallery = rtrim( $property_gallery, ',' );
							$current_property_gallery = explode( ',', $current_property_gallery );

						endif;

						$temp_post_array          = array();
						$temp_post_array['title'] = $temp_post->post_title;
						$temp_post_array['image'] = esc_url( SPACENTO_URL ) . 'public/images/property.webp';
						if ( isset( $current_property_gallery ) && is_array( $current_property_gallery ) ) {
							$temp_post_array['image'] = wp_get_attachment_url( $current_property_gallery[0] );
						}
						$temp_post_array['text']     = $temp_post->post_excerpt;
						$temp_post_array['url']      = get_permalink( $id );
						$temp_post_array['currency'] = esc_html( $property_currency );
						$temp_post_array['price']    = esc_html( $property_price );

						if ( empty( $temp_post_array['text'] ) ) {
							$temp_post_array['text'] = $this->limitedstring( $temp_post->post_content, 200 );
						}

						$block_content_actual .= $this->spacento_property_render( $temp_post_array );

					endforeach;

				endif;

			else :

				$data['data']['error'] = esc_html__( 'Empty', 'spacento' );

			endif;

		endif;

		if ( 'layoutOne' === $block_layout ) :
			$block_content .= '<div class="spacento-properties-one-container">';
		elseif ( 'layoutTwo' === $block_layout ) :
			$block_content .= '<div class="spacento-properties-two-container">';
		else :
			$block_content .= '<div class="spacento-properties-three-container">';
		endif;
			$block_content .= $block_content_actual;
			$block_content .= '</div>';

		return $block_content;

	}

	/**
	 * Register blocks.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $categories The block attributes.
	 * @param      string $post The block attributes.
	 */
	public function spacento_block_categories( $categories, $post ) {
		if ( 'post' !== $post->post_type ) {
			return $categories;
		}
		return array_merge(
			$categories,
			array(
				array(
					'slug'  => 'spacento',
					'title' => esc_html__( 'Spacento', 'spacento' ),
					'icon'  => 'wordpress',
				),
			)
		);
	}

}
