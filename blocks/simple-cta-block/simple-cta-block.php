<?php

function sb_register_block() {
	// Editor script registered manually because there is no build step
	wp_register_script(
		'simple-block-editor',
		plugins_url( 'index.js', __FILE__ ),
		[ 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components' ],
		filemtime( __DIR__ . '/index.js' )
	);

	register_block_type( __DIR__ ); // Reads block.json
}
add_action( 'init', 'sb_register_block' );