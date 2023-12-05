<?php
require_once(__DIR__ . '/util.php');

class TagGalleryDB
{
    static function table_name(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'tag_gallery_cache';
    }

    static function drop_table()
    {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS " . TagGalleryDB::table_name());
    }
    private static function setup_db()
    {
        global $wpdb;
        TagGalleryDB::drop_table();
        $name = TagGalleryDB::table_name();
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

    static function init()
    {
        global $wpdb;
        $wpdb->query('START TRANSACTION');
        TagGalleryDB::setup_db();
        foreach (get_posts(array(
            'numberposts' => -1
        )) as $post) {
            TagGalleryDB::add_post_to_cache($post);
        }
        $wpdb->query('COMMIT');
    }

    private static function add_post_to_cache(WP_Post $post)
    {
        global $wpdb;
        $infos = TagGalleryImageInfo::from_post($post);
        foreach ($infos as $image) {
            $wpdb->insert(TagGalleryDB::table_name(), $image->as_array(), $image->types());
        }
    }

    static function delete_cached_post(int $postId) {
        global $wpdb;
        $name = TagGalleryDB::table_name();
        $wpdb->query($wpdb->prepare("DELETE FROM $name WHERE post_id = %d", $postId));
    }

    static function update_cached_post(int $postId) {
        global $wpdb;
        $post = get_post($postId);
        $wpdb->query("START TRANSACTION");
        TagGalleryDB::delete_cached_post($postId);
        TagGalleryDB::add_post_to_cache($post);
        $wpdb->query("COMMIT");
    }

    static function query(string $query, bool $ascending): array
    {
        global $wpdb;
        $name = TagGalleryDB::table_name();
        $instructions = explode(' ', $query);

        $query = '';

        foreach ($instructions as $instruction) {
            $negative = substr($instruction, 0, 1) == '!';
            if ($negative) {
                $instruction = substr($instruction, 1);
                if (empty($query)) {
                    // Negative is meaningless with nothing yet captured
                    continue;
                }
            }
            $all = $instruction == '*';

            if ($negative && $all) {
                // Clear query, equivalent to removing everything.
                $query = '';
                continue;
            }

            if ($negative) {
                $query .= "\n\nEXCEPT\n" . $wpdb->prepare(<<<SQL
                    SELECT DISTINCT
                        src,
                        alt,
                        srcset,
                        sizes,
                        MAX(post_date_gmt) as post_date_gmt
                    FROM
                        $name t
                    WHERE
                        t.tag = %s
                    GROUP BY src, alt, srcset, sizes, t.tag
                SQL, $instruction);
            } else if ($all) {
                if (!empty($query)) {
                    $query .= "\n\nUNION";
                }

                $query .= "\n\n" . $wpdb->prepare(<<<SQL
                    SELECT DISTINCT
                        src,
                        alt,
                        srcset,
                        sizes,
                        MAX(post_date_gmt) as post_date_gmt
                    FROM
                        $name
                    GROUP BY src, alt, srcset, sizes, tag
                SQL);
            } else {
                if (!empty($query)) {
                    $query .= "\n\nUNION";
                }

                $query .= "\n\n" . $wpdb->prepare(<<<SQL
                    SELECT DISTINCT
                        src,
                        alt,
                        srcset,
                        sizes,
                        MAX(post_date_gmt) as post_date_gmt
                    FROM
                        $name t
                    WHERE
                        t.tag = %s
                    GROUP BY src, alt, srcset, sizes, t.tag
                SQL, $instruction);
            }
        }

        if (empty($query)) {
            return array();
        }
        if ($ascending) {
            $query .= "\nORDER BY post_date_gmt ASC";
        } else {
            $query .= "\nORDER BY post_date_gmt DESC";
        }

        $rows = $wpdb->get_results($query, ARRAY_A);
        $entries = array();

        foreach ($rows as $row) {
            array_push($entries, TagGalleryImageInfo::from_db($row));
        }

        return $entries;
    }
}
