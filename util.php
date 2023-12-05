<?php

declare(strict_types=1);

class TagGalleryImageInfo
{
    public string $src;
    public string $alt;
    public string $srcset;
    public string $sizes;
    public int $post_id;
    public string $post_date_gmt;

    public string $tag;

    function as_array(): array
    {
        return array(
            'src' => $this->src,
            'alt' => $this->alt,
            'srcset' => $this->srcset,
            'sizes' => $this->sizes,
            'post_id' => $this->post_id,
            'post_date_gmt' => $this->post_date_gmt,
            'tag' => $this->tag
        );
    }

    function types(): array
    {
        return array(
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s'
        );
    }

    static function from_db(array $row): TagGalleryImageInfo
    {
        $info = new TagGalleryImageInfo();
        $info->src = $row["src"];
        $info->alt = $row["alt"];
        $info->srcset = $row["srcset"];
        $info->sizes = $row["sizes"];
        $info->post_id = array_key_exists("post_id", $row) ? intval($row["post_id"]) : -1;
        $info->tag = array_key_exists("tag", $row) ? $row["tag"] : "UNSET";
        $info->post_date_gmt = $row["post_date_gmt"];

        return $info;
    }

    function equivalent_to(TagGalleryImageInfo $other): bool
    {
        return $other->src == $this->src && $other->srcset == $this->srcset && $other->alt == $this->alt && $other->sizes == $this->sizes;
    }

    static function from_img_tag(DOMElement $img, WP_Post $post, string $tag): TagGalleryImageInfo
    {
        $imgInfo = new TagGalleryImageInfo();
        $imgInfo->src = $img->getAttribute('src');
        $imgInfo->alt = $img->getAttribute('alt');
        $imgInfo->srcset = $img->getAttribute('srcset');
        $imgInfo->sizes = $img->getAttribute('sizes');
        $imgInfo->tag = $tag;
        $imgInfo->post_id = $post->ID;
        $imgInfo->post_date_gmt = $post->post_date_gmt;
        return $imgInfo;
    }

    static function from_post(WP_Post $post): array
    {
        if (get_post_status($post) != 'publish') {
            // Don't include trashed or private posts
            return array();
        }

        $tags = wp_get_post_tags($post->ID);
        $infos = array();
        $dom = new DOMDocument();
        $html = apply_filters('the_content', $post->post_content);
        if (empty($html)) {
            return array();
        }
        try {
            @$dom->loadHTML($html);
            foreach ($dom->getElementsByTagName('img') as $img) {
                foreach ($tags as $tag) {
                    array_push($infos, TagGalleryImageInfo::from_img_tag($img, $post, $tag->slug));
                }
            }
            return $infos;
        } catch (Exception $e) {
            error_log($e->getMessage() . "\n" . $e->getTraceAsString());
            return array();
        }
    }
}

class TagGalleryUtils
{
    static function get_all_tag_slugs(): array
    {
        $tags = get_tags(array(
            'hide_empty' => false
        ));
        $tagSlugs = array();
        foreach ($tags as $tag) {
            array_push($tagSlugs, $tag->slug);
        };

        return $tagSlugs;
    }
}
