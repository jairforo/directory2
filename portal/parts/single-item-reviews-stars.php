{var $rating_count = intval(get_post_meta($post->id, 'rating_count', true))}
{var $rating_mean = floatval(get_post_meta($post->id, 'rating_mean', true))}

{var $showCount = isset($showCount) ? $showCount : false}
<div class="review-stars-container">
	{if $rating_count > 0}
		<div class="content rating-star-shown" itemscope itemtype="http://data-vocabulary.org/Review-aggregate">
			{* RICH SNIPPETS *}
			<span style="display: none" itemprop="itemreviewed">{!$post->title}</span>
			<span style="display: none" itemprop="rating">{$rating_mean}</span>
			<span style="display: none" itemprop="count">{$rating_count}</span>
			{* RICH SNIPPETS *}
			<span class="review-stars" data-score="{$rating_mean}"></span>
			{if $showCount}<span class="review-count">({$rating_count})</span>{/if}
			<a href="{$post->permalink}#review"><?php _e('Submit your rating', 'ait-item-reviews') ?></a>
		</div>
	{else}
		<div class="content rating-text-shown">
			<a href="{$post->permalink}#review"><?php _e('Submit your rating', 'ait-item-reviews') ?></a>
		</div>
	{/if}
</div>