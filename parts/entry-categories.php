<span class="categories">
	{if isset($taxonomy)}
	<span class="cat-links">{!$post->categoryList(', ', '', $taxonomy)}</span>
	{else}
	<span class="cat-links">{!$post->categoryList(', ')}</span>
	{/if}
</span>