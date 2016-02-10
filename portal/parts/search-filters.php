{* VARIABLES *}
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
{var $filterOrderBySelected = isset($_REQUEST['orderby']) && $_REQUEST['orderby'] != "" ? $_REQUEST['orderby'] : $settings->sortingDefaultOrderBy}
{var $filterOrderSelected = isset($_REQUEST['order']) && $_REQUEST['order'] != "" ? $_REQUEST['order'] : $settings->sortingDefaultOrder}
{* VARIABLES *}

<div class="filters-wrap">
	{if !empty($postType) && $postType == "ait-event-pro"}
		<h2>{!_x 'Showing %1$s from %2$s Upcoming Events', 'event pro taxonomy'|printf: $current, $max}</h2>
	{else}
		<h2>{!_x 'Showing %1$s from %2$s Items', 'item taxonomy'|printf: $current, $max}</h2>
	{/if}
	<div class="filters-container">
		<div class="content">
			<div class="filter-container filter-count" data-filterid="count">
				<div class="content">
					<div class="selected">{__ 'Count'}:</div>
					<select class="filter-data">
						{foreach $filterCounts as $filter}
							{if $filter == $filterCountsSelected}
								<option value="{$filter}" selected>{$filter}</option>
							{else}
								<option value="{$filter}">{$filter}</option>
							{/if}
						{/foreach}
					</select>
				</div>
			</div>
			<div class="filter-container filter-orderby" data-filterid="orderby">
				<div class="content">
					<div class="selected">{__ 'Sort by'}:</div>
					<select class="filter-data">
						{foreach $filterOrderBy as $filter}
							{if $filter[0] == $filterOrderBySelected}
								<option value="{$filter[0]}" selected>{$filter[1]}</option>
							{else}
								<option value="{$filter[0]}">{$filter[1]}</option>
							{/if}
						{/foreach}
					</select>
				</div>
			</div>
			<div class="filter-container filter-order" data-filterid="order">
				<div class="content">
					<div class="selected title">{__ 'Order'}:</div>
					<a n:class='$filterOrderSelected == "ASC" ? selected' title="ASC" href="#" data-value="ASC"><i class="fa fa-angle-down"></i></a>
					<a n:class='$filterOrderSelected == "DESC" ? selected' title="DESC" href="#" data-value="DESC"><i class="fa fa-angle-up"></i></a>
				</div>
			</div>
			<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery('.filters-container .filter-container').each(function(){
					$select = jQuery(this).find('select');
					$select.change(function(){
						{if !isset($disableRedirect)}
						getItems();
						{/if}
					});
					$order = jQuery(this).find('a');
					$order.click(function(e){
						e.preventDefault();
						$order.parent().find('.selected').removeClass('selected');
						jQuery(this).addClass('selected');
						{if !isset($disableRedirect)}
						getItems();
						{/if}
					})
				});
			});

			function getItems(){
				// defaults
				var data = {
					count: 10,
					orderby: 'date',
					order: 'ASC'
				}
				jQuery('.filters-container .filter-container').each(function(){
					var key = jQuery(this).data('filterid');
					if(key == "order"){
						var val = jQuery(this).find('a.selected').data('value');
					} else {
						var val = jQuery(this).find('select option:selected').attr('value');
					}
					data[key] = val;
				});

				// build url
				var baseUrl = window.location.protocol+"//"+window.location.host+window.location.pathname;
				var eParams = window.location.search.replace("?", "").split('&');
				var nParams = {};
				jQuery.each(eParams, function(index, value){
					var val = value.split("=");
					if(typeof val[1] == "undefined"){
						nParams[val[0]] = "";
					} else {
						nParams[val[0]] = val[1];
					}
				});
				var query = jQuery.extend({}, nParams, data);
				var queryString = jQuery.param(query);
				window.location.href = baseUrl + "?" + queryString;
			}
			</script>
		</div>
	</div>
</div>