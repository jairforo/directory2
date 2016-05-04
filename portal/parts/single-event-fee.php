{if isset($meta->fee)}

{var $count = count($meta->fee) > 1 ? 'multiple-fees' : ''}

<div class="fee-container data-container">
	<div class="content">
		<div class="fee data">
			<h6><i class="fa fa-money"></i> {__ 'Fees & Tickets'}</h6>
			<div class="fee-text data-content {!$count}">
				{if !$meta->fee}
					<div class="fee-data">
						<div class="fee-info">
							<div class="fee-arrow"><i class="fa fa-chevron-right"></i></div>
							<div class="fee-label">
								<span>{__ 'Free'}</span>
							</div>
							<div class="fee-price free">
									<div>
										<span class="currency">{$meta->currency}</span>
										<span>0</span>
									</div>
							</div>
						</div>
					</div>
				{else}
					{foreach $meta->fee as $feeData}
					<div class="fee-data">
						<div class="fee-info">
							<div class="fee-arrow"><i class="fa fa-chevron-right"></i></div>
							{if $feeData['name']}
							<div class="fee-label">
								<span>{$feeData['name']}</span>
								{if $feeData['desc']}
								<div class="fee-desc">{$feeData['desc']}</div>
								{/if}
							</div>
							{/if}
							<div class="fee-price">
								{if isset($feeData['url']) and $feeData['url'] != ''}
									<a href="{!$feeData['url']}" target="_blank" title="{__ 'Buy Ticket'}">
								{/if}
									<div>
										{if $feeData['price'] == "0" or ""}
											<span>{__ 'Free'}</span>
										{else}
											<span class="currency">{$meta->currency}</span>
											<span>{$feeData['price']}</span>
										{/if}
									</div>
								{if isset($feeData['url']) and $feeData['url'] != ''}
									</a>
								{/if}
							</div>
						</div>
					</div>
					{/foreach}
				{/if}
			</div>
		</div>
	</div>
</div>

{/if}
