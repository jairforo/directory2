{block content}

	{var $settings = get_option('ait_events_pro_options', array())}
	{var $noFeatured = $settings['noFeatured']}

	{var $sortingSettings = $options->theme->items}

	{var $selectedCount   = $el->option(count)}

	{var $postType  = 'ait-event-pro'}
	{var $lang      = AitLangs::getCurrentLanguageCode()}
	{var $orderBy   = array()}
	{var $metaQuery = array()}
	{var $taxQuery  = array()}

	{var $pagination = (get_query_var('paged')) ? get_query_var('paged') : 1}

	{var $selectedOrderBy     = !empty($_REQUEST['orderby']) ? $_REQUEST['orderby'] : $sortingSettings->sortingDefaultOrderBy}
	{var $selectedOrder = !empty($_REQUEST['order']) ? $_REQUEST['order'] : $sortingSettings->sortingDefaultOrder}

	{var $currentCategory = get_term_by( 'slug', get_query_var('ait-events-pro'), 'ait-events-pro')}

	{? array_push($taxQuery, array(
			'taxonomy' 	=> 'ait-events-pro',
			'field'		=> 'term_id',
			'terms'		=> $currentCategory->term_id
		)
	)}

	{var $metaQuery['dates_clause'] = array(
		'key'     => 'ait-event-recurring-date',
		'value'   => date('Y-m-d'),
		'compare' => '>',
		'type'    => 'date',
	)}

	{if $selectedOrderBy == 'date'}
		{var $orderBy['dates_clause'] = $selectedOrder}
	{/if}

	{var $orderBy[$selectedOrderBy] = $selectedOrder}

	{var $args = array(
		'lang'           => $lang,
		'post_type'      => $postType,
		'posts_per_page' => $selectedCount,
		'meta_query'     => $metaQuery,
		'tax_query'      => $taxQuery,
		'orderby'        => $orderBy,
		'paged'			 => $pagination
	)}


	{var $query = aitGetItems($args)}

	{includePart portal/parts/taxonomy-category-list, taxonomy => "ait-events-pro"}

	{if $currentCategory->description}
	<div class="entry-content">
		{!$currentCategory->description}
	</div>
	{/if}


	<div n:class="items-container, !$wp->willPaginate($query) ? 'pagination-disabled'">
		<div class="content">

			{if $query->havePosts}

			{includePart portal/parts/search-filters, postType => $postType, taxonomy => "ait-events-pro", current => $query->post_count, max => $query->post_count}

			{if defined("AIT_ADVANCED_FILTERS_ENABLED")}
				{includePart portal/parts/advanced-filters, query => $query}
			{/if}

			<div class="ajax-container">
				<div class="content">

					{customLoop from $query as $post}
						{var $categories = get_the_terms($post->id, 'ait-events-pro')}

						{var $meta = $post->meta('event-pro-data')}

						{var $isFeatured = false }

						{var $imgWidth = 768}
						{var $imgHeight = 195}

						<div n:class='event-container, $isFeatured ? "item-featured", defined("AIT_REVIEWS_ENABLED") ? reviews-enabled'>

							<a href="{$post->permalink}">
								{var $imgHeight = ($imgWidth / 4) * 3}
								<div class="item-thumbnail">
									{if $post->hasImage}
									<div class="item-thumbnail-wrap" style="background-image: url('{imageUrl $post->imageUrl, width => $imgWidth, height => $imgHeight, crop => 1}')"></div>
									{else}
									<div class="item-thumbnail-wrap" style="background-image: url('{imageUrl $noFeatured, width => $imgWidth, height => $imgHeight, crop => 1}')"></div>
									{/if}
									<div class="item-text-wrap">
										<div class="item-more">{__ 'More info'}</div>
									</div>
								</div>

								{var $nextDates = aitGetNextDate($meta->dates)}
								{var $date_timestamp = strtotime($nextDates['dateFrom'])}
								{var $day = date('d', $date_timestamp)}
								{var $month = date('M', $date_timestamp)}
								{var $year = date('Y', $date_timestamp)}
								{var $moreDates = count(aitGetRecurringDates($post)) - 1}

								<div class="entry-date">
									<div class="day">{$day}</div>
									<span class="month">{$month}</span>
									<span class="year">{$year}</span>
								</div>

								{if $moreDates > 0}<div class="more">+ {$moreDates}</div>{/if}

							</a>
							<div class="item-text">
								<div class="item-title"><a href="{$post->permalink}"><h3>{!$post->title}</h3></a></div>
								<div class="item-excerpt"><p class="txtrows-3">{!$post->excerpt(8)|striptags}</p></div>

								<div class="item-taxonomy">
									<div class="item-categories">{includePart "portal/parts/event-taxonomy", itemID => $post->id, taxonomy => 'ait-events-pro', onlyParent => true, count => 3}</div>
								</div>
							</div>
							
							<div class="item-location">
								{foreach $post->categories('ait-locations') as $loc}
								<a href="{$loc->url()}" class="location">{!$loc->title}</a>
								{/foreach}
							</div>

						</div>


					{/customLoop}

					{includePart parts/pagination, location => pagination-below, max => $query->max_num_pages}
				</div>
			</div>

			{else}
				{includePart parts/none, message => empty-site}
			{/if}
		</div>
	</div>
	{? remove_filter('posts_join', 'aitPostsJoin')}
	{? remove_filter('posts_orderby', 'aitPostsOrderby')}