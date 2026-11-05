<?php

class Company_Resources_Custom_Fields {

	const NONCE_ACTION = 'save_fields';
	const NONCE_NAME   = 'nonce_fields';

	public function __construct() {
		add_action( 'init', [ $this, 'register_meta' ] );
		add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
		add_action( 'save_post', [ $this, 'save' ], 10, 2 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
	}

	/** Post types where the box appears (filterable). */
	private function post_types() {
		return apply_filters( 'company_resources_post_types', [ 'company_resources' ] );
	}

	/** Field definitions (filterable). Types: text, select, textarea, image, url. */
	private function fields() {
		return apply_filters( 'company_resources_fields', [
			'client_name' => [
				'label' => __( 'Client Name', 'company-resources-plugin' ),
				'type'  => 'text',
			],
			'industry'   => [
				'label'   => __( 'Industry', 'company-resources-plugin' ),
				'type'    => 'select',
				'options' => [
					''        => __( '— Default —', 'company-resources-plugin' ),
					'technology'    => __( 'Technology', 'company-resources-plugin' ),
					'finance' => __( 'Finance', 'company-resources-plugin' ),
					'medical'  => __( 'Medical', 'company-resources-plugin' ),
				],
			],
			'summary'  => [
				'label' => __( 'Summary', 'company-resources-plugin' ),
				'type'  => 'textarea',
			],
			'project_image'    => [
				'label' => __( 'Project Image', 'company-resources-plugin' ),
				'type'  => 'image',
			],
			'project_url'     => [
				'label' => __( 'Project URL', 'company-resources-plugin' ),
				'type'  => 'url',
			],
		] );
	}

	/** Sanitize a single value according to its field type. */
	private function sanitize( $value, $field ) {
		switch ( $field['type'] ) {
			case 'textarea':
				return sanitize_textarea_field( $value );
			case 'url':
				return esc_url_raw( $value );
			case 'image':
				return absint( $value );
			case 'select':
				return array_key_exists( $value, $field['options'] ) ? $value : '';
			case 'text':
			default:
				return sanitize_text_field( $value );
		}
	}

	/** Register meta so it is also available in the REST API / block editor. */
	public function register_meta() {
		foreach ( $this->post_types() as $post_type ) {
			foreach ( $this->fields() as $key => $field ) {
				register_post_meta( $post_type, $key, [
					'type'              => 'image' === $field['type'] ? 'integer' : 'string',
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => function ( $value ) use ( $field ) {
						return $this->sanitize( $value, $field );
					},
					'auth_callback'     => function () {
						return current_user_can( 'edit_posts' );
					},
				] );
			}
		}
	}

	public function add_meta_box() {
		add_meta_box(
			'company_resources_box',
			__( 'Custom Fields', 'company-resources-plugin' ),
			[ $this, 'render' ],
			$this->post_types(),
			'normal',
			'high'
		);
	}

	public function render( $post ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
		?>
		<style>
			.company-resources-row { margin: 0 0 16px; }
			.company-resources-row label { display: block; font-weight: 600; margin-bottom: 4px; }
			.company-resources-row input[type="text"], .company-resources-row input[type="url"],
			.company-resources-row select, .company-resources-row textarea { width: 100%; max-width: 100%; }
			.company-resources-image-preview img { max-width: 240px; height: auto; display: block; margin-bottom: 8px; }
		</style>
		<?php
		foreach ( $this->fields() as $key => $field ) {
			$value = get_post_meta( $post->ID, $key, true );
			echo '<div class="company-resources-row">';
			printf( '<label for="%s">%s</label>', esc_attr( $key ), esc_html( $field['label'] ) );

			switch ( $field['type'] ) {
				case 'text':
					printf( '<input type="text" id="%1$s" name="%1$s" value="%2$s">', esc_attr( $key ), esc_attr( $value ) );
					break;

				case 'url':
					printf( '<input type="url" id="%1$s" name="%1$s" value="%2$s" placeholder="https://">', esc_attr( $key ), esc_url( $value ) );
					break;

				case 'textarea':
					printf( '<textarea id="%1$s" name="%1$s" rows="4">%2$s</textarea>', esc_attr( $key ), esc_textarea( $value ) );
					break;

				case 'select':
					printf( '<select id="%1$s" name="%1$s">', esc_attr( $key ) );
					foreach ( $field['options'] as $opt_value => $opt_label ) {
						printf(
							'<option value="%s"%s>%s</option>',
							esc_attr( $opt_value ),
							selected( $value, $opt_value, false ),
							esc_html( $opt_label )
						);
					}
					echo '</select>';
					break;

				case 'image':
					$image_id = absint( $value );
					echo '<div class="company-resources-image-field">';
					printf( '<input type="hidden" id="%1$s" name="%1$s" value="%2$d" class="company-resources-image-id">', esc_attr( $key ), $image_id );
					echo '<div class="company-resources-image-preview">';
					if ( $image_id ) {
						echo wp_get_attachment_image( $image_id, 'medium' );
					}
					echo '</div>';
					printf( '<button type="button" class="button company-resources-image-select">%s</button> ', esc_html__( 'Select image', 'company-resources-plugin' ) );
					printf(
						'<button type="button" class="button company-resources-image-remove"%s>%s</button>',
						$image_id ? '' : ' style="display:none"',
						esc_html__( 'Remove', 'company-resources-plugin' )
					);
					echo '</div>';
					break;
			}
			echo '</div>';
		}
	}

	public function save( $post_id, $post ) {
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) || ! in_array( $post->post_type, $this->post_types(), true ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		foreach ( $this->fields() as $key => $field ) {
			$raw   = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
			$value = $this->sanitize( $raw, $field );

			if ( '' === $value || 0 === $value ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $value );
			}
		}
	}

	public function enqueue( $hook ) {
		if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->post_type, $this->post_types(), true ) ) {
			return;
		}

		wp_enqueue_media();
		wp_add_inline_script( 'jquery-core', <<<'JS'
jQuery(function ($) {
	$(document).on('click', '.company-resources-image-select', function (e) {
		e.preventDefault();
		var $wrap = $(this).closest('.company-resources-image-field');
		var frame = wp.media({
			title: 'Select image',
			button: { text: 'Use this image' },
			library: { type: 'image' },
			multiple: false
		});
		frame.on('select', function () {
			var att = frame.state().get('selection').first().toJSON();
			var url = (att.sizes && att.sizes.medium) ? att.sizes.medium.url : att.url;
			$wrap.find('.company-resources-image-id').val(att.id);
			$wrap.find('.company-resources-image-preview').html($('<img>', { src: url }));
			$wrap.find('.company-resources-image-remove').show();
		});
		frame.open();
	});
	$(document).on('click', '.company-resources-image-remove', function (e) {
		e.preventDefault();
		var $wrap = $(this).closest('.company-resources-image-field');
		$wrap.find('.company-resources-image-id').val('');
		$wrap.find('.company-resources-image-preview').empty();
		$(this).hide();
	});
});
JS
		);
	}
}

new Company_Resources_Custom_Fields();

/**
 * Template helper: get a field value.
 * Usage: echo esc_html( company_resources_get_field( 'client_name' ) );
 */
function company_resources_get_field( $key, $post_id = null ) {
	return get_post_meta( $post_id ?: get_the_ID(), $key, true );
}

/**
 * Template helper: output the image field.
 * Usage: echo company_resources_get_image( 'company_resources_image', 'large' );
 */
function company_resources_get_image( $key, $size = 'large', $post_id = null ) {
	$id = absint( company_resources_get_field( $key, $post_id ) );
	return $id ? wp_get_attachment_image( $id, $size ) : '';
}