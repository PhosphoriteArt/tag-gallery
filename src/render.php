<?php

declare(strict_types=1);

$arrow = '<svg width="100%" height="100%" viewBox="0 0 15 15" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" xmlns:serif="http://www.serif.com/" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;"><path d="M2.363,7.5l10.274,-7.5l-0,15l-10.274,-7.5Z"/></svg>';

$tag = $attributes["query"];

$getUrlsByTag = function (string $tag): array {
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
			array_push($media_posts, array(
				'src' => $img->getAttribute('src'),
				'alt' => $img->getAttribute('alt'),
				'srcset' => $img->getAttribute('srcset'),
				'sizes' => $img->getAttribute('sizes')
			));
		}
	}

	return $media_posts;
};

$makeImageFromMedia = function (array $image, string $extraClasses = ""): string {
	$src = $image['src'];
	$alt = $image['alt'];
	$extra = '';
	if (!empty($image['srcset'])) {
		$extra .= sprintf('srcset="%s"', $image['srcset']);
	}
	if (!empty($image['sizes'])) {
		$extra .= sprintf('sizes="%s"', $image['sizes']);
	}
	return sprintf(
		<<<HTML
<picture class="post-image %s">
	<img src="%s" alt="%s" %s>
</picture>
HTML,
		$extraClasses,
		$src,
		$alt,
		$extra
	);
};

$media = $getUrlsByTag($tag);

?>


<div <?php echo get_block_wrapper_attributes(); ?>>
	<div class="tag-gallery-container" <?php echo count($media) > 0 ? '' : 'data-empty' ?>>
		<?php if (count($media) > 0) : ?>
			<div class="tag-popover" aria-hidden="true">
				<div class="popover-close clickable">ⓧ</div>
				<div class="popover-main">
					<div class="popover-left popover-nav clickable">
						<?php echo $arrow ?>
					</div>
					<picture class="popover-image">
						<img>
					</picture>
					<div class="popover-right popover-nav clickable rotated">
						<?php echo $arrow ?>
					</div>
				</div>
				<div class="popover-flipper tikaka">
					<?php foreach ($media as $image) : ?>
						<?php echo $makeImageFromMedia($image, "clickable") ?>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="tag-gallery tikaka tikaka-animated">
				<?php foreach ($media as $image) : ?>
					<?php echo $makeImageFromMedia($image, "clickable"); ?>
				<?php endforeach; ?>
			</div>
		<?php elseif (!empty($tag)) : ?>
			<div>No images found with tag <code><?php echo $tag ?></code></div>
		<?php else : ?>
			<div>No tag selected, can't locate images. Enter a tag in the block settings to the right!</div>
		<?php endif; ?>
	</div>
</div>
