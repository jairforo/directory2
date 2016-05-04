{block content}
{? global $wp_query}
{var $query = $wp_query}


{if $query->have_posts()}
	{includePart portal/parts/search-filters, current => $query->post_count, max => $query->found_posts}

	{if defined("AIT_ADVANCED_FILTERS_ENABLED")}
		{includePart portal/parts/advanced-filters, query => $query}
	{/if}

	{includePart parts/pagination, location => pagination-above, max => $query->max_num_pages}

	<div class="items-container">
		{customLoop from $query as $post}
			{includePart parts/post-content}
		{/customLoop}
	</div>

	{includePart parts/pagination, location => pagination-below, max => $query->max_num_pages}

{else}
	{includePart parts/none, message => nothing-found}
{/if}

