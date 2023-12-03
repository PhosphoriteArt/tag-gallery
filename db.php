<?php
require_once(__DIR__ . '/util.php');

function tag_gallery_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'tag_gallery_cache';
}

function tag_gallery_drop_table() {
    global $wpdb;
    $name = tag_gallery_table_name();
    $wpdb->query("DROP TABLE IF EXISTS $name");
}

function tag_gallery_setup_db()
{
    global $wpdb;
    $name = tag_gallery_table_name();
    tag_gallery_drop_table();
    $wpdb->query(<<<SQL
CREATE TABLE $name (
    post_id INT,
    src TEXT,
    alt TEXT,
    srcset TEXT,
    sizes TEXT,
    tag TEXT,
    _id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (_id)
);
SQL);
}

function tag_gallery_init()
{
    tag_gallery_setup_db();
    $tags = tag_gallery_get_all_tags();

    foreach ($tags as $tag) {
        tag_gallery_update_tag_cache($tag);
    }
}

function tag_gallery_update_tag_cache(string $tag)
{
    global $wpdb;
    $name = tag_gallery_table_name();

    $wpdb->query($wpdb->prepare("DELETE FROM $name WHERE tag = %s", $tag));
    $images = tag_gallery_get_image_info_from_posts($tag);
    foreach ($images as $image) {
        $wpdb->insert($name, $image->as_array(), $image->types());
    }
}

function updatePost(WP_Post $post)
{
    $tags = wp_get_post_tags($post->ID);
    foreach ($tags as $tag) {
        tag_gallery_update_tag_cache($tag->slug);
    }
}

function tag_gallery_get_cached_info(string $tag): array
{
    if (empty($tag)) {
        return array();
    }

    $name = tag_gallery_table_name();
    global $wpdb;

    $results = array();
    $rows = $wpdb->get_results($wpdb->prepare(<<<SQL
        SELECT * FROM $name
        WHERE tag = %s
SQL, $tag), ARRAY_A);

    foreach ($rows as $row) {
        array_push($results, TagGalleryImageInfo::from_db($row));
    }

    return $results;
}
