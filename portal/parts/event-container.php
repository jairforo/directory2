{var $eventOptions = get_option('ait_events_pro_options', array())}
{var $noFeatured = $eventOptions['noFeatured']}
{var $categories = get_the_terms($post->id, 'events-pro')}
{var $meta = $post->meta('event-pro-data')}


{var $imgWidth = 768}
{var $imgHeight = 195}

<div n:class='event-container'>

	<a href="{$post->permalink}">
	{var $imgHeight = ($imgWidth / 4) * 3}
	<div class="item-thumbnail">
		{if $post->hasImage}
		<div class="item-thumbnail-wrap" style="background-image: url('{imageUrl $post->imageUrl, width => $imgWidth, height => $imgHeight, crop => 1}')"></div>
		{else}
		<div class="item-thumbnail-wrap" style="background-image: url('{imageUrl $noFeatured, width => $imgWidth, height => $imgHeight, crop => 1}')"></div>
		{/if}
	</div>

	{var $nextDates = aitGetNextDate($meta->dates)}
	{var $date_timestamp = strtotime($nextDates['dateFrom'])}
	{var $day = date('d', $date_timestamp)}
	{var $month = date('F', $date_timestamp)}
	{var $moreDates = count(aitGetRecurringDates($post)) - 1}

	<div class="entry-date">
		<div class="day">{$day}</div>
		<div class="month">{$month}</div>
		{if $moreDates > 0}
		<div class="more">+ {$moreDates}</div>
		{/if}
	</div>

	</a>
	<div class="item-text">
		{*if defined('AIT_REVIEWS_ENABLED')}
			{includePart "portal/parts/carousel-reviews-stars", item => $item, showCount => false}
		{/if*}
		<div class="item-title"><a href="{$post->permalink}"><h3>{!$post->title}</h3></a></div>
		<div class="item-excerpt"><p class="txtrows-3">{!$post->excerpt(200)|striptags}</p></div>

		<div class="item-taxonomy">

			<div class="item-location">
				{foreach $post->categories('ait-locations') as $loc}
				<a href="{$loc->url()}" class="location">{!$loc->title}</a>
				{/foreach}
			</div>

			<div class="item-categories">{includePart "portal/parts/event-taxonomy", itemID => $post->id, taxonomy => 'ait-events-pro', onlyParent => true, count => 3}</div>

		</div>

	</div>
	<div class="item-more"><a href="{$post->permalink}">{__ 'More info'}</a></div>

</div>