<?php

/**
 * Plugin Name:       Tag Gallery
 * Description:       Tag-based automatically-updating gallery for Tikaka!
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           1.2.3
 * Author:            Dew (Phosphorite)
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       tag-gallery
 * Plugin URI:        https://github.com/PhosphoriteArt/tag-gallery
 * Update URI:        https://wordpress.phosphorite.art/tag-gallery
 *
 * @package           create-block
 */

if (!defined('ABSPATH')) {
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
function create_block_tikaka_gallery_block_init()
{
	register_block_type(__DIR__ . '/build');
}

function tag_gallery_on_update(WP_Post $post)
{
	TagGalleryDB::update_cached_post($post->ID);
}
function tag_gallery_on_save($post_id)
{
	TagGalleryDB::update_cached_post($post_id);
}
function tag_gallery_on_delete($post_id)
{
	TagGalleryDB::delete_cached_post($post_id);
}

function tag_gallery_on_tag_edit()
{
	TagGalleryDB::init();
}

function tag_gallery_init()
{
	TagGalleryDB::init();
	if (!wp_next_scheduled('tag_gallery_refresh_cron')) {
		wp_schedule_event(time(), 'twicedaily', 'tag_gallery_refresh_cron');
	}
}

function tag_gallery_deinit()
{
	wp_clear_scheduled_hook('tag_gallery_refresh_cron');
}


register_activation_hook(__FILE__, 'tag_gallery_init');
register_deactivation_hook(__FILE__, 'tag_gallery_deinit');

add_action('init', 'create_block_tikaka_gallery_block_init');

add_action('rest_after_insert_post', 'tag_gallery_on_update');
add_action('deleted_post', 'tag_gallery_on_delete');
add_action('trashed_post', 'tag_gallery_on_save');
add_action('untrashed_post', 'tag_gallery_on_save');

add_action('delete_post_tag', 'tag_gallery_on_tag_edit');
add_action('saved_post_tag', 'tag_gallery_on_tag_edit');

add_action('tag_gallery_refresh_cron', 'tag_gallery_refresh_cron');
