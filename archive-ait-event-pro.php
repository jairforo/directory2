{block content}

	{* template for page title is in parts/page-title.php *}

	{if $wp->havePosts}

	{var $noFeatured = $eventsProOptions['noFeatured']}

		<div class="items-container">
			<div class="content">

				<div class="ajax-container">
					<div class="content">

						{loop as $post}
							{var $categories = get_the_terms($post->id, 'ait-events-pro')}

							{var $meta = $post->meta('event-pro-data')}

							{var $isFeatured = false }

							{var $imgWidth = 768}
							{var $imgHeight = 400}

							<div n:class='event-container, layout-box, $isFeatured ? "item-featured", defined("AIT_REVIEWS_ENABLED") ? reviews-enabled'>

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






								<div class="item-text{if !$itemExcerpt and !$itemLocation} categories-only{/if}">

									<div class="item-title"><a href="{$item->permalink}"><h3>{!$post->title}</h3></a></div>
									{if $itemExcerpt}
										<div class="item-excerpt txtrows-{$textRows}"><p>{!$post->excerpt(200)|striptags}</p></div>
									{/if}

									{if $itemLocation or $itemCategories}
									<div class="item-taxonomy">

										{if $itemCategories}
											<div class="item-categories">
												{foreach $post->categories('ait-events-pro') as $cat}
													<span class="item-category">{!$cat->title}</span>
												{/foreach}</div>
										{/if}

										{if $itemLocation}
										<div class="item-location">
											{foreach $post->categories('ait-locations') as $loc}
												<a href="{$loc->url()}" class="location">{!$loc->title}</a>
											{/foreach}
										</div>
										{/if}

									</div>
									{/if}
								</div>






							</div>

						{/loop}
						{includePart parts/pagination, location => pagination-below}
					</div>
				</div>


			</div>
		</div>










	{else}
		{includePart parts/none, message => no-posts}
	{/if}
