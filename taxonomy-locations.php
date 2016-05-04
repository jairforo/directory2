{block content}
{? global $wp_query}

{var $currentCategory = get_term_by('slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) )}

{includePart portal/parts/taxonomy-category-list, taxonomy => "ait-locations"}

{if $currentCategory->description}
<div class="entry-content">
	{!$currentCategory->description}
</div>
{/if}

<div n:class="items-container, !$wp->willPaginate($wp_query) ? 'pagination-disabled'">
	<div class="content">

		{if $wp_query->have_posts()}

		{includePart portal/parts/search-filters, taxonomy => "ait-locations", current => $wp_query->post_count, max => $wp_query->found_posts}

		{if defined("AIT_ADVANCED_FILTERS_ENABLED")}
			{includePart portal/parts/advanced-filters, query => $wp_query}
		{/if}

		<div class="ajax-container">
			<div class="content">

				{customLoop from $wp_query as $post}
					
					{includePart "portal/parts/item-container"}

				{/customLoop}

				{includePart parts/pagination, location => pagination-below, max => $wp_query->max_num_pages}
			</div>
		</div>

		{else}
			{includePart parts/none, message => empty-site}
		{/if}
	</div>
</div>
