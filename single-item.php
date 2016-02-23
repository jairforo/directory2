{block content}

	{loop as $post}
		{* SETTINGS AND DATA *}
		{var $meta = $post->meta('item-data')}
		{var $settings = $options->theme->item}
		{* SETTINGS AND DATA *}

		{*RICH SNIPPET WRAP*}
		<div class="item-content-wrap" itemscope itemtype="http://schema.org/LocalBusiness">
			<meta itemprop="name" content="{$post->title}">
		{*RICH SNIPPET WRAP*}

			{var $wouldGalleryDisplay = false}
			{if $post->hasImage}
				{var $wouldGalleryDisplay = true}
			{/if}
			{if $meta->displayGallery && is_array($meta->gallery)}
				{var $wouldGalleryDisplay = true}
			{/if}

			{if $wouldGalleryDisplay == false}
				{if defined('AIT_REVIEWS_ENABLED')}
					{includePart portal/parts/single-item-reviews-stars, showCount => true}
				{/if}
			{/if}

			{* CONTENT SECTION *}
			<div class="entry-content">
				{if $wouldGalleryDisplay == false}
                                    {if $post->hasContent}
                                        <div class="column-grid column-grid-1">
                                            <div class="column column-span-1 column-narrow column-first column-last">
                                                <div class="entry-content-wrap" itemprop="description">
                                                    {if $post->hasContent}
                                                        {!$post->content}
                                                    {else}
                                                        {!$post->excerpt}
                                                    {/if}
                                                </div>
                                            </div>
                                        </div>
                                    {/if}
				{else}
				<div class="column-grid column-grid-3">
                                    <div class="column column-span-1 column-narrow column-first">
                                    {* GALLERY SECTION *}
                                    {includePart portal/parts/single-item-gallery}
                                    {* GALLERY SECTION *}
                                    </div>
                                    {if $post->hasContent}
					<div class="column column-span-2 column-narrow column-last">
                                            <div class="entry-content-wrap" itemprop="description">
                                                {if $post->hasContent}
                                                    {!$post->content}
                                                {else}
                                                    {!$post->excerpt}
                                                {/if}
                                            </div>
					</div>
                                    {/if}
				</div>
				{/if}
			</div>
			{* CONTENT SECTION *}


			<div class="column-grid column-grid-3">
				<div class="column column-span-1 column-narrow column-first">
					{* OPENING HOURS SECTION *}
					{includePart portal/parts/single-item-opening-hours}
					{* OPENING HOURS SECTION *}
				</div>

				<div class="column column-span-2 column-narrow column-last">
					{* ADDRESS SECTION *}
					{includePart portal/parts/single-item-address}
					{* ADDRESS SECTION *}

					{* CLAIM LISTING SECTION *}
					{*includePart portal/parts/claim-listing*}
					{* CLAIM LISTING SECTION *}

					{* CONTACT OWNER SECTION *}
					{includePart portal/parts/single-item-contact-owner}
					{* CONTACT OWNER SECTION *}
					<div class="ait-get-directions-button">
					</div>
				</div>
			</div>

			{* CLAIM LISTING SECTION *}
			{if defined('AIT_CLAIM_LISTING_ENABLED')}
				{includePart portal/parts/claim-listing}
			{/if}
			{* CLAIM LISTING SECTION *}

			<input type="hidden" id="item_address" value="{$meta->map['address']}"/>
			<input type="hidden" id="item_lat" value="{$meta->map['latitude']}"/>
			<input type="hidden" id="item_lng" value="{$meta->map['longitude']}"/>
			<div id="mapcontainer" class="getDirGoogleMap"></div>
			<div id="map-directions" class="getDirDirections"></div>

			{* MAP SECTION *}
			{includePart portal/parts/single-item-map}
			{* MAP SECTION *}

			{* SOCIAL SECTION *}
			{includePart portal/parts/single-item-social}

			{*includePart portal/parts/single-item-social-icons*}
			{* SOCIAL SECTION *}

			{* FEATURES SECTION *}
			{includePart portal/parts/single-item-features}
			{* FEATURES SECTION *}

			{* REVIEWS SECTION *}
			{if defined('AIT_REVIEWS_ENABLED')}
			{includePart portal/parts/single-item-reviews}
			{/if}
			{* REVIEWS SECTION *}

			{* UPCOMING EVENTS SECTION *}
			{if (defined('AIT_EVENTS_PRO_ENABLED'))}
				{includePart portal/parts/single-item-events, itemId => $post->id}
			{/if}
			{* UPCOMING EVENTS SECTION *}

		{*RICH SNIPPET WRAP*}
		</div>
		{*RICH SNIPPET WRAP*}

	{/loop}
