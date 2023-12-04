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
        $info->post_id = intval($row["post_id"]);
        $info->tag = $row["tag"];
        $info->post_date_gmt = $row["post_date_gmt"];

        return $info;
    }

    function equivalentTo(TagGalleryImageInfo $other): bool
    {
        return $other->src == $this->src && $other->srcset == $this->srcset && $other->alt == $this->alt && $other->sizes == $this->sizes;
    }
}

function tag_gallery_get_image_info_from_posts(string $tag): array
{
    if (!$tag || empty($tag)) {
        return array();
    }

    $posts = get_posts(array(
        'numberposts' => -1,
        'tag' => $tag,
    ));

    $media_posts = array();
    if (!$posts) {
        return $media_posts;
    }

    foreach ($posts as $post) {
        $dom = new DOMDocument();
        $html = apply_filters('the_content', $post->post_content);
        @$dom->loadHTML($html);
        foreach ($dom->getElementsByTagName('img') as $img) {
            $imgInfo = new TagGalleryImageInfo();
            $imgInfo->src = $img->getAttribute('src');
            $imgInfo->alt = $img->getAttribute('alt');
            $imgInfo->srcset = $img->getAttribute('srcset');
            $imgInfo->sizes = $img->getAttribute('sizes');
            $imgInfo->tag = $tag;
            $imgInfo->post_id = $post->ID;
            $imgInfo->post_date_gmt = $post->post_date_gmt;
            array_push($media_posts, $imgInfo);
        }
    }

    return $media_posts;
}

function tag_gallery_get_all_tags(): array
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
