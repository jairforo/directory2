{* ********************************************************* *}
{* COMMON DATA                                               *}
{* ********************************************************* *}

	{capture $navPrevText}{!_x '%s Previous', 'previous' |printf: '<span class="meta-nav">&larr;</span>'}{/capture}
	{capture $navNextText}{!_x 'Next %s', 'next' |printf: '<span class="meta-nav">&rarr;</span>'}{/capture}

	{if !isset($location)} {var $location = ''} {/if}
	{if !isset($arrow)} {var $arrow = ''} {/if}

	{var $arrowLeft = ''}
	{var $arrowRight = ''}

{* ********************************************************* *}
{* for ATTACHMENT				                             *}
{* ********************************************************* *}
{if $wp->isAttachment}
	{var $arrowLeft = 'yes'}
	{var $arrowRight = 'yes'}
	{capture $navPrevLink}<span class="nav-previous">{prevImageLink false, $navPrevText}</span>{/capture}
	{capture $navNextLink}<span class="nav-next">{nextImageLink false, $navNextText}</span>{/capture}
{* ********************************************************* *}
{* for POST DETAIL, IMAGE DETAIL and PORTFOLIO DETAIL 		 *}
{* ********************************************************* *}
{elseif $wp->isSingle and !isset($ignoreSingle)}
	{if $wp->hasPreviousPost or $wp->hasNextPost}
		{if $wp->hasPreviousPost}
			{var $arrowLeft = 'yes'}
			{capture $navPrevLink}<span class="nav-previous">{prevPostLink $navPrevText}</span>{/capture}
		{/if}
		{if $wp->hasNextPost}
			{var $arrowRight = 'yes'}
			{capture $navNextLink}<span class="nav-next">{nextPostLink $navNextText}</span>{/capture}
		{/if}
	{/if}
{* ********************************************************* *}
{* for OTHER										 		 *}
{* ********************************************************* *}
{else}
	{if $wp->willPaginate}
		{if $wp->hasPreviousPosts}
			{var $arrowLeft = 'yes'}
			{capture $navPrevLink}<span class="nav-previous">{prevPostsLink $navPrevText}</span>{/capture}
		{/if}
		{if $wp->hasNextPosts}
			{var $arrowRight = 'yes'}
			{if isset($max)}
				{capture $navNextLink}<span class="nav-next">{nextPostsLink $navNextText, intval($max)}</span>{/capture}
			{else}
				{capture $navNextLink}<span class="nav-next">{nextPostsLink $navNextText}</span>{/capture}
			{/if}
		{/if}
	{/if}
{/if}

{* ********************* *}
{* RESULTS               *}
{* ********************* *}
{if $arrow != ''}
	{if $arrow == 'left'}
		{if $arrowLeft == 'yes'}{!$navPrevLink}{/if}
	{else}
		{if $arrowRight == 'yes'}{!$navNextLink}{/if}
	{/if}
{else}
	<nav class="nav-single {$location}" role="navigation">
	{if $arrowLeft == 'yes'}
		{if isset($max) && intval($max) > 0}
			{!$navPrevLink}
		{elseif !isset($max)}
			{!$navPrevLink}
		{/if}
	{/if}

	{if isset($max)}
		{if $wp->willPaginate}{if !$wp->isSingular}{pagination max => intval($max), show_all => false}{/if}{/if}
	{else}
		{if $wp->willPaginate}{if !$wp->isSingular}{pagination show_all => false}{/if}{/if}
	{/if}

	{if $arrowRight == 'yes'}
		{if isset($max) && intval($max) > 0}
			{!$navNextLink}
		{elseif !isset($max)}
			{!$navNextLink}
		{/if}
	{/if}
	</nav>
{/if}
