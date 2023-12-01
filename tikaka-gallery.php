<?php
/**
 * Plugin Name:       Tag Gallery
 * Description:       Tag-based automatically-updating gallery for Tikaka!
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           1.0
 * Author:            Dew (Phosphorite)
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       tikaka-gallery
 * Plugin URI:        https://github.com/PhosphoriteArt/tag-gallery
 * Update URI:        https://wordpress.phosphorite.art/tag-gallery
 *
 * @package           create-block
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function tikaka_gallery_tikaka_gallery_block_init() {
	register_block_type( __DIR__ . '/build' );
}
add_action( 'init', 'tikaka_gallery_tikaka_gallery_block_init' );
