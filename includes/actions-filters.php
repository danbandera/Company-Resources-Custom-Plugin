<?php

function add_text_to_footer_post( $content ) {
	// Only append on the singular company resource view, never in REST/feeds/admin.
	if ( ! is_singular( 'company_resources' ) ) {
		return $content;
	}

	$client_name = company_resources_get_field( 'client_name' );
	$industry    = company_resources_get_field( 'industry' );

	$content .= '<p><strong>Thank you for reading this company resource!</strong></p>';

	if ( $client_name ) {
		$content .= '<p><strong>Client:</strong> ' . esc_html( $client_name ) . '</p>';
	}
	if ( $industry ) {
		$content .= '<p><strong>Industry:</strong> ' . esc_html( $industry ) . '</p>';
	}

	return $content;
}

add_filter( 'the_content', 'add_text_to_footer_post' );

function custom_excerpt_text( $excerpt, $post ) {
	if ( 'company_resources' === $post->post_type ) {
		return wp_trim_words( $excerpt, 25 );
	}
	return $excerpt;
}

add_filter( 'get_the_excerpt', 'custom_excerpt_text', 999, 2 );

function company_resources_add_category_column( $columns ) {
	$columns['resource_category'] = __( 'Resource Category', 'company-resources-plugin' );

	return $columns;
}

add_filter( 'manage_company_resources_posts_columns', 'company_resources_add_category_column' );

function company_resources_display_category_column( $column, $post_id ) {
	if ( 'resource_category' !== $column ) {
		return;
	}

	$terms = get_the_terms( $post_id, 'company_resource_tags' );

	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		echo '—';
		return;
	}

	$term_names = wp_list_pluck( $terms, 'name' );

	echo esc_html( implode( ', ', $term_names ) );
}

add_action( 'manage_company_resources_posts_custom_column', 'company_resources_display_category_column', 10, 2 );