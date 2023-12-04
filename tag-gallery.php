<?php
/**
 * Plugin Name:       Tag Gallery
 * Description:       Tag-based automatically-updating gallery for Tikaka!
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           1.2.1
 * Author:            Dew (Phosphorite)
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       tag-gallery
 * Plugin URI:        https://github.com/PhosphoriteArt/tag-gallery
 * Update URI:        https://wordpress.phosphorite.art/tag-gallery
 *
 * @package           create-block
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once(__DIR__ . '/db.php');

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function create_block_tikaka_gallery_block_init() {
	register_block_type( __DIR__ . '/build' );
}

function tag_gallery_on_save($post_id) {
	TagGalleryDB::init();
}

register_activation_hook(__FILE__, 'tag_gallery_init');

add_action( 'init', 'create_block_tikaka_gallery_block_init' );

add_action( 'save_post', 'tag_gallery_on_save' );
