<?php
require_once(__DIR__ . '/util.php');

function tag_gallery_table_name()
{
    global $wpdb;
    return $wpdb->prefix . 'tag_gallery_cache';
}

function tag_gallery_drop_table()
{
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
    post_date_gmt TEXT
);
SQL);
}

function tag_gallery_init()
{    
    global $wpdb;
    $wpdb->query('START TRANSACTION');
    tag_gallery_setup_db();
    $tags = tag_gallery_get_all_tags();

    foreach ($tags as $tag) {
        tag_gallery_update_tag_cache($tag);
    }
    $wpdb->query('COMMIT');
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

function tag_gallery_sort_by_gmt(TagGalleryImageInfo $a, TagGalleryImageInfo $b): int
{
    return strcmp($b->post_date_gmt, $a->post_date_gmt);
}

function tag_gallery_push_if_new(array &$arr, TagGalleryImageInfo $info)
{
    foreach ($arr as $el) {
        if ($info->equivalentTo($el)) {
            return;
        }
    }
    array_push($arr, $info);
}
function tag_gallery_remove_equivalent(array &$arr, TagGalleryImageInfo $info)
{
    for ($i = count($arr) - 1; $i >= 0; $i--) {
        $el = $arr[$i];
        if ($info->equivalentTo($el)) {
            array_splice($arr, $i, 1);
        }
    }
}

function tag_gallery_get_all_cached(): array
{
    $name = tag_gallery_table_name();
    global $wpdb;

    $rows = $wpdb->get_results(<<<SQL
        SELECT * FROM $name
    SQL, ARRAY_A);

    $results = array();
    foreach ($rows as $row) {
        tag_gallery_push_if_new($results, TagGalleryImageInfo::from_db($row));
    }
    return $results;
}

function tag_gallery_get_tag_cached(string $tag): array
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
        tag_gallery_push_if_new($results, TagGalleryImageInfo::from_db($row));
    }

    return $results;
}

function tag_gallery_get_cached_info(string $query, bool $ascending): array
{
    $instructions = explode(' ', $query);
    $entries = array();

    foreach ($instructions as $instruction) {
        $negative = substr($instruction, 0, 1) == '!';
        if ($negative) {
            $instruction = substr($instruction, 1);
        }
        $all = $instruction == '*';

        $results = $all ? tag_gallery_get_all_cached() : tag_gallery_get_tag_cached($instruction);
        if ($negative) {
            foreach ($results as $result) {
                tag_gallery_remove_equivalent($entries, $result);
            }
        } else {
            foreach ($results as $result) {
                tag_gallery_push_if_new($entries, $result);
            }
        }
    }

    usort($entries, 'tag_gallery_sort_by_gmt');
    if ($ascending) {
        $entries = array_reverse($entries);
    }

    return $entries;
}
