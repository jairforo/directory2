{block content}

	{* VARIABLES *}
	{var $isAdvanced = false}
	{var $enableFiltering = false}

	{var $taxQueries = array()}

	{var $settings = $options->theme->items}

	{var $filterCounts = array(5, 10, 20)}
	{capture $dateLabel}{__ 'Date'}{/capture}
	{capture $titleLabel}{__ 'Title'}{/capture}

	{var $filterOrderBy = array( array("date", $dateLabel), array("title", $titleLabel))}
	{if defined('AIT_REVIEWS_ENABLED')}
		{capture $ratingLabel}{__ 'Rating'}{/capture}
		{? array_push($filterOrderBy, array("rating", $ratingLabel))}
	{/if}

	{var $filterCountsSelected = isset($_REQUEST['count']) && $_REQUEST['count'] != "" ? $_REQUEST['count'] : $settings->sortingDefaultCount}

	{if defined("AIT_ADVANCED_FILTERS_ENABLED")}
		{if isset($_REQUEST['filters']) && $_REQUEST['filters'] != ""}
			{var $filterCountsSelected = -1}
		{/if}
	{/if}

	{var $filterOrderBySelected = isset($_REQUEST['orderby']) && $_REQUEST['orderby'] != "" ? $_REQUEST['orderby'] : $settings->sortingDefaultOrderBy}
	{var $filterOrderSelected = isset($_REQUEST['order']) && $_REQUEST['order'] != "" ? $_REQUEST['order'] : $settings->sortingDefaultOrder}
	{* VARIABLES *}

	{if isset($_REQUEST['a']) && $_REQUEST['a'] != ""}
		{var $isAdvanced = true}
	{/if}

	{if isset($_REQUEST['category']) && $_REQUEST['category'] != ""}
		{? array_push($taxQueries, array('taxonomy' => 'ait-items', 'field' => 'term_id', 'terms' => $_REQUEST['category']))}
	{/if}
	{if isset($_REQUEST['location']) && $_REQUEST['location'] != ""}
		{? array_push($taxQueries, array('taxonomy' => 'ait-locations', 'field' => 'term_id', 'terms' => $_REQUEST['location']))}
	{/if}

	{if isset($_REQUEST['lat']) && $_REQUEST['lat'] != "" && isset($_REQUEST['lon']) && $_REQUEST['lon'] != "" && isset($_REQUEST['rad']) && $_REQUEST['rad'] != ""}
		{var $enableFiltering = true}

		{var $geoRadiusUnits = isset($_REQUEST['runits']) && $_REQUEST['runits'] != "" ? $_REQUEST['runits'] : 'km'}
		{var $geoRadiusValue = isset($_REQUEST['rad']) && $_REQUEST['rad'] != "" ? $_REQUEST['rad'] * 1000 : 100 * 1000}
		{var $geoRadiusValue = $geoRadiusUnits == 'mi' ? $geoRadiusValue * 1.609344 : $geoRadiusValue}

		{var $geoLat = $_REQUEST['lat']}
		{var $geoLon = $_REQUEST['lon']}
	{/if}

	{if $isAdvanced}
		{? global $query_string}
		{* ALL QUERIES WILL BE ORDERED BY FEATURED AND RATING - filter removed after all search queries *}
		{? add_filter('posts_join', 'aitPostsJoin')}
		{? add_filter('posts_orderby', 'aitPostsOrderby')}

		{var $query_args = explode("&", $query_string)}
		{var $search_query = array()}

		{foreach $query_args as $key => $string}
			{var $query_split = explode("=", $string)}
			{var $search_query[$query_split[0]] = isset($query_split[1]) ? urldecode($query_split[1]) : ''}
		{/foreach}

		{var $search_query['post_type'] => "ait-item"}
		{if isset($taxQueries) && count($taxQueries) > 0}
		{var $search_query['tax_query'] => $taxQueries}
		{/if}

		{var $search_query['posts_per_page'] = $filterCountsSelected}

		{var $search_query['order'] = $filterOrderSelected}

		{if $enableFiltering}
			{* THIS IS GEOLOCATION SEARCH *}

			{var $search_query['posts_per_page'] = -1}
			{customQuery as $query, $search_query}

			{* FILTER BY RADIUS *}
			{var $filtered_query = $search_query}
			{var $filtered = array()}
			{customLoop from $query as $post}
				{var $meta = $post->meta('item-data')}
				{if isPointInRadius($geoRadiusValue, $geoLat, $geoLon, $meta->map['latitude'], $meta->map['longitude']) == false}
					{? array_push($filtered, $post->id)}
				{/if}
			{/customLoop}
			{var $filtered_query['posts_per_page'] = $filterCountsSelected}
			{var $filtered_query['post__not_in'] = $filtered}
			{* FILTER BY RADIUS *}

			{customQuery as $filteredQuery, $filtered_query}

			{var $allItems_filtered_query = $filtered_query}

			{* ADVANCED FILTERING *}
			{if defined("AIT_ADVANCED_FILTERS_ENABLED")}
				{if isset($_REQUEST['filters']) && $_REQUEST['filters'] != ""}
					{var $defined_filters = explode(";",$_REQUEST['filters'])}


					{var $advanced_filter_query = $search_query}
					{var $advanced_filter_query['posts_per_page'] = -1}
					{var $advanced_filter_query['meta_query'] = aitFiltersMetaQuery($defined_filters)}


					{customQuery as $query, $advanced_filter_query}

					{var $allItems_filtered_query = $advanced_filter_query}
				{/if}
			{/if}
			{* ADVANCED FILTERING *}

			{var $allItems_filtered_query['posts_per_page'] = -1}
			{customQuery as $allItemsQuery, $allItems_filtered_query}

			{if $filteredQuery->havePosts}
				{includePart portal/parts/search-filters, current => $filteredQuery->post_count, max => $allItemsQuery->post_count}

				{if defined("AIT_ADVANCED_FILTERS_ENABLED")}
					{includePart portal/parts/advanced-filters, query => $allItemsQuery}
				{/if}

				{includePart parts/pagination, location => pagination-above, max => $filteredQuery->max_num_pages}

				{customLoop from $filteredQuery as $post}
					{includePart parts/post-content}
				{/customLoop}

				{includePart parts/pagination, location => pagination-below, max => $filteredQuery->max_num_pages}

			{else}
				{includePart parts/none, message => nothing-found}
			{/if}
		{else}
			{* THIS IS NORMAL SEARCH *}

			{customQuery as $query, $search_query}

			{var $allItems_filtered_query = $search_query}

			{* ADVANCED FILTERING *}
			{if defined("AIT_ADVANCED_FILTERS_ENABLED")}
				{if isset($_REQUEST['filters']) && $_REQUEST['filters'] != ""}
					{var $defined_filters = explode(";",$_REQUEST['filters'])}
					{var $advanced_filter_query = $search_query}
					{var $advanced_filter_query['posts_per_page'] = -1}
					{var $advanced_filter_query['meta_query'] = aitFiltersMetaQuery($defined_filters)}

					{customQuery as $query, $advanced_filter_query}

					{var $allItems_filtered_query = $advanced_filter_query}
				{/if}
			{/if}
			{* ADVANCED FILTERING *}

			{var $allItems_filtered_query['posts_per_page'] = -1}
			{customQuery as $allItemsQuery, $allItems_filtered_query}

			{if $query->havePosts}
				{includePart portal/parts/search-filters, current => $query->post_count, max => $allItemsQuery->post_count}

				{if defined("AIT_ADVANCED_FILTERS_ENABLED")}
					{includePart portal/parts/advanced-filters, query => $allItemsQuery}
				{/if}

				{includePart parts/pagination, location => pagination-above, max => $query->max_num_pages}

				{customLoop from $query as $post}
					{includePart parts/post-content}
				{/customLoop}

				{includePart parts/pagination, location => pagination-below, max => $query->max_num_pages}
			{else}
				{includePart parts/none, message => nothing-found}
			{/if}
		{/if}

		{? remove_filter('posts_join', 'aitPostsJoin')}
		{? remove_filter('posts_orderby', 'aitPostsOrderby');}

	{else}
		{* STANDARD SEARCH *}

		{? global $query_string}
		{var $query_args = explode("&", $query_string)}
		{var $search_query = array()}

		{foreach $query_args as $key => $string}
			{var $query_split = explode("=", $string)}
			{var $search_query[$query_split[0]] = isset($query_split[1]) ? urldecode($query_split[1]) : ''}
		{/foreach}
		{var $search_query['post_type'] => array('post', 'page')}

		{customQuery as $query, $search_query}

		{if $query->havePosts}
			{includePart parts/pagination, location => pagination-above, max => $query->max_num_pages}

			{customLoop from $query as $post}
				{includePart parts/post-content}
			{/customLoop}

			{includePart parts/pagination, location => pagination-below, max => $query->max_num_pages}
		{else}
			{includePart parts/none, message => nothing-found}
		{/if}
	{/if}





