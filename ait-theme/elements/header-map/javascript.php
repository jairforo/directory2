{var $settings = $options->theme->items}
{var $noFeatured = $options->theme->item->noFeatured}

<script id="{$htmlId}-container-script">

	jQuery(window).load(function($){
		var $mapDiv = jQuery("#{!$htmlId}-container");

		var styles = [
			{
				stylers: [
					{ hue: "{$el->option(mapHue)|noescape}" },
					{ saturation: "{$el->option(mapSaturation)|noescape}" },
					{ lightness: "{$el->option(mapBrightness)|noescape}" },
				]
			},
			{ featureType: "landscape", stylers: [
					{ visibility: "{if $el->option(landscapeShow) == false}off{else}on{/if}"},
					{ hue: "{$el->option(landscapeColor)|noescape}"},
					{ saturation: "{if $el->option(landscapeColor) != ''} {$el->option(objSaturation)|noescape} {/if}"},
					{ lightness: "{if $el->option(landscapeColor) != ''} {$el->option(objBrightness)|noescape} {/if}"},
				]
			},
			{ featureType: "administrative", stylers: [
					{ visibility: "{if $el->option(administrativeShow) == false}off{else}on{/if}"},
					{ hue: "{$el->option(administrativeColor)|noescape}"},
					{ saturation: "{if $el->option(administrativeColor) != ''} {$el->option(objSaturation)|noescape} {/if}"},
					{ lightness: "{if $el->option(administrativeColor) != ''} {$el->option(objBrightness)|noescape} {/if}"},
				]
			},
			{ featureType: "road", stylers: [
					{ visibility: "{if $el->option(roadsShow) == false}off{else}on{/if}"},
					{ hue: "{$el->option(roadsColor)|noescape}"},
					{ saturation: "{if $el->option(roadsColor) != ''} {$el->option(objSaturation)|noescape} {/if}"},
					{ lightness: "{if $el->option(roadsColor) != ''} {$el->option(objBrightness)|noescape} {/if}"},
				]
			},
			{ featureType: "water", stylers: [
					{ visibility: "{if $el->option(waterShow) == false}off{else}on{/if}"},
					{ hue: "{$el->option(waterColor)|noescape}"},
					{ saturation: "{if $el->option(waterColor) != ''} {$el->option(objSaturation)|noescape} {/if}"},
					{ lightness: "{if $el->option(waterColor) != ''} {$el->option(objBrightness)|noescape} {/if}"},
				]
			},
			{ featureType: "poi", stylers: [
					{ visibility: "{if $el->option(poiShow) == false}off{else}on{/if}"},
					{ hue: "{$el->option(poiColor)|noescape}"},
					{ saturation: "{if $el->option(poiColor) != ''} {$el->option(objSaturation)|noescape} {/if}"},
					{ lightness: "{if $el->option(poiColor) != ''} {$el->option(objBrightness)|noescape} {/if}"},
				]
			},
		];

		jQuery("#{!$htmlId}-container").gmap3({

			{customQuery as $query, $markerQuery}
			{var $filtered = array()}
			{if $query->havePosts}
				{if $enableFiltering}
					{customLoop from $query as $post}
						{var $meta = $post->meta('item-data')}
						{if isPointInRadius($geoRadiusValue, $geoLat, $geoLon, $meta->map['latitude'], $meta->map['longitude'])}
							{? array_push($filtered, $post)}
						{/if}
					{/customLoop}
				{else}
					{customLoop from $query as $post}
						{? array_push($filtered, $post)}
					{/customLoop}
				{/if}
			{/if}

			{* ADVANCED FILTERING *}
			{if defined("AIT_ADVANCED_FILTERS_ENABLED")}
				{if isset($_REQUEST['filters']) && $_REQUEST['filters'] != ""}
					{var $defined_filters = explode(";",$_REQUEST['filters'])}
					{var $valid = array()}

					{foreach $filtered as $post}
						{var $meta = $post->meta('filters-options')}
						{if isset($meta->filters) && is_array($meta->filters)}
							{var $check = array_intersect($defined_filters, $meta->filters)}
							{if is_array($check) && count($check) >= count($defined_filters)}
								{? array_push($valid, $post)}
							{/if}
						{else}

						{/if}
					{/foreach}

					{var $filtered = $valid}
				{/if}
			{/if}
			{* ADVANCED FILTERING *}

			{*if $wp->isSingular(item)*}
			{if count($filtered) == 1}
				{*/*disable autozoom if one item is on the map*/*}
				{var $autoZoomAndFit = ''}
				{*/*get meta of one item to get position for map center*/*}
				{var $oneItemMeta = $filtered[0]->meta('item-data')}
			{/if}

			{if $isAdvancedSearch && $geoLat != "" && $geoLon != ""}

			map:{
				options:{
					center: [{!$geoLat}, {!$geoLon}],
					mapTypeId: google.maps.MapTypeId.{$el->option(type)|noescape},
					zoom: {$el->option(zoom)|noescape},
					scrollwheel: {!$scrollWheel},
					styles: styles,
					zoomControl: true,
					zoomControlOptions: {
						position: google.maps.ControlPosition.RIGHT_TOP
					},
					scaleControl: true,
					streetViewControl: true,
					streetViewControlOptions: {
						position: google.maps.ControlPosition.RIGHT_TOP
					},
				}
			},
			circle:{
				options:{
					center: [{!$geoLat}, {!$geoLon}],
					radius : {!$geoRadiusValue},
					fillColor : "#008BB2",
					strokeColor : "#005BB7"
				},
			},

			{else}

				{if count($filtered) == 1}
					map:{
						options:{
							center: [{!$oneItemMeta->map[latitude]}, {!$oneItemMeta->map[longitude]}],
							mapTypeId: google.maps.MapTypeId.{$el->option(type)|noescape},
							zoom: {$el->option(zoom)|noescape},
							scrollwheel: {!$scrollWheel},
							styles: styles,
							zoomControl: true,
							zoomControlOptions: {
								position: google.maps.ControlPosition.RIGHT_TOP
							},
							scaleControl: true,
							streetViewControl: true,
							streetViewControlOptions: {
								position: google.maps.ControlPosition.RIGHT_TOP
							},
						}
					},

				{else}
					map:{
						options:{
							center: [{!$address['latitude']},{!$address['longitude']}],
							mapTypeId: google.maps.MapTypeId.{$el->option(type)|noescape},
							zoom: {$el->option(zoom)|noescape},
							scrollwheel: {!$scrollWheel},
							styles: styles,
							zoomControl: true,
							zoomControlOptions: {
								position: google.maps.ControlPosition.RIGHT_TOP
							},
							scaleControl: true,
							streetViewControl: true,
							streetViewControlOptions: {
								position: google.maps.ControlPosition.RIGHT_TOP
							},
						}
					},
				{/if}
			{/if}

			marker:{
				values:[
					{if $isAdvancedSearch && $geoLat != "" && $geoLon != ""}
					{
						lat: {!$geoLat}, lng: {!$geoLon},
						data: 'disabled',
						options:
						{
							icon: "{!aitPaths()->url->img}/pins/geoloc_pin.png"
						}
					},
					{/if}

					{if count($filtered) > 0}
						{foreach $filtered as $item}
							{var $itemMeta = $item->meta('item-data')}
							{var $itemCats = get_the_terms($item->id, 'ait-items')}
							{if $itemCats != false}
								{var $itemCat = array_shift($itemCats)}
								{var $itemCatData = get_option('ait-items_category_'.$itemCat->term_id)}
								{var $itemMarker = $settings->categoryDefaultPin}

								{if isset($itemCatData['map_icon']) && $itemCatData['map_icon'] != ""}
									{var $itemMarker = $itemCatData['map_icon']}
								{else}
									{var $parent = get_term($itemCat->parent, 'ait-items')}
									{if isset($parent) && !($parent instanceof WP_Error)}
										{var $parentCatData = get_option('ait-items_category_'.$parent->term_id)}
										{if isset($parentCatData['map_icon']) && $parentCatData['map_icon'] != ""}
											{var $itemMarker = $parentCatData['map_icon']}
										{else}
											{var $itemMarker = $settings->categoryDefaultPin}
										{/if}
									{else}
										{var $itemMarker = $settings->categoryDefaultPin}
									{/if}
								{/if}
							{/if}

							{capture $buttonLabel}{__ 'Show More'}{/capture}

							{var $itemImage = $noFeatured}
							{if $item->imageUrl}
								{var $itemImage = $item->imageUrl}
							{/if}

							{* if the item has default 1:1 coords , dont show it on the map *}
							{if ($itemMeta->map['latitude'] === "1" && $itemMeta->map['longitude'] === "1") != true}

							{
								lat: {!$itemMeta->map['latitude']}, lng: {!$itemMeta->map['longitude']},

								{* JAVASCRIPT DATA VALIDATION *}
								{var $itemMeta->map[address] = str_replace("\xe2\x80\xa8", '', $itemMeta->map[address])}
								{var $itemMeta->map[address] = str_replace("\xe2\x80\xa9", '', $itemMeta->map[address])}
								{var $itemTitle = str_replace("\xe2\x80\xa8", '', $item->title)}
								{var $itemTitle = str_replace("\xe2\x80\xa9", '', $itemTitle)}

								{var $itemMeta->map[address] = trim(preg_replace('/\s+/', ' ', $itemMeta->map[address]))}
								{var $itemMeta->map[address] = str_replace(array('"',"'"), "", $itemMeta->map[address])}
								{* JAVASCRIPT DATA VALIDATION *}

								{if $el->option('infoboxEnableTelephoneNumbers') && $itemMeta->telephone }
								data: "<div class=\"headermap-infowindow-container\"><div class=\"item-data\"><h3>{!$itemTitle}</h3><span class=\"item-address\">{!$itemMeta->map[address]}</span><a href=\"{!$item->permalink}\"><span class=\"item-button\">"+{!$buttonLabel}+"</span></a></div><div class=\"item-picture\"><img src=\"{imageUrl $itemImage, width => 145, height => 180, crop => 1}\" alt=\"image\"><a href=\"tel:{!$itemMeta->telephone}\" class=\"phone\">{!$itemMeta->telephone}</a></div></div>",
								{else}
								data: "<div class=\"headermap-infowindow-container\"><div class=\"item-data\"><h3>{!$itemTitle}</h3><span class=\"item-address\">{!$itemMeta->map[address]}</span><a href=\"{!$item->permalink}\"><span class=\"item-button\">"+{!$buttonLabel}+"</span></a></div><div class=\"item-picture\"><img src=\"{imageUrl $itemImage, width => 145, height => 180, crop => 1}\" alt=\"image\"></div></div>",
								{/if}
								{if isset($itemMarker)}
								options:
								{
									icon: "{!$itemMarker}"
								}
								{/if}
							},

							{/if}

						{/foreach}
					{/if}
				],
				{if $clustering}
				cluster:{
					radius: parseInt({$el->option('clusterRadius')}),
					5: {
						content: "<div class='cluster cluster-1'>CLUSTER_COUNT</div>",
						width: 53,
						height: 52
					},
					20: {
						content: "<div class='cluster cluster-2'>CLUSTER_COUNT</div>",
						width: 56,
						height: 55
					},
					50: {
						content: "<div class='cluster cluster-3'>CLUSTER_COUNT</div>",
						width: 66,
						height: 65
					},
					events: {
						click: function(cluster) {
							var map = jQuery(this).gmap3("get");
							map.panTo(cluster.main.getPosition());
							map.setZoom(map.getZoom() + 2);
						}
					}
				},
				{/if}
				options:{
					draggable: false
				},
				events:{
					click: function(marker, event, context){
						var map = jQuery(this).gmap3("get");

						/* Remove All previous infoboxes */
						jQuery("#{!$htmlId}-container").find('.infoBox').remove();

						if(context.data != "disabled"){

							var infoBoxOptions = {
								content: context.data,
								disableAutoPan: false,
								maxWidth: 150,
								pixelOffset: new google.maps.Size(-145, -233),
								zIndex: 99,
								boxStyle: {
									background: "#FFFFFF",
									opacity: 1,
									width: "290px"
								},
								closeBoxMargin: "2px 2px 2px 2px",
								closeBoxURL: "{!aitPaths()->url->img}/infobox_close.png",
								infoBoxClearance: new google.maps.Size(1, 1),
								position: marker.position
							};

							var infoBox = new InfoBox(infoBoxOptions);
							infoBox.open(map, marker);
						}

						map.panTo(marker.getPosition());

					},
				},
			},
			{if $geoLocation}
			getgeoloc:{
				callback : function(latLng){
					if (latLng){
						jQuery("#latitude-search").attr('value', latLng.lat());
						jQuery("#longitude-search").attr('value', latLng.lng());

						jQuery("#{!$htmlId}-container").gmap3({
							marker:{
								values:[
									{ latLng: latLng, options: { icon: "{!aitPaths()->url->img}/pins/geoloc_pin.png" }}
								]
							},
							map:{
								options:{
									center: latLng,
									zoom: {$el->option(zoom)|noescape}
								}
							}
						});
					}
				}
			},
			{/if}
			{if is_array($address) and isset($address['streetview'])}
				{if $address['streetview']}
			streetviewpanorama:{
				options:{
					container: jQuery("#{!$htmlId}-container"),
					opts:{
						position: new google.maps.LatLng({!$address['latitude']},{!$address['longitude']}),
						pov: {
							heading: parseInt({!$address['swheading']}),
							pitch: parseInt({!$address['swpitch']}),
							zoom: parseInt({!$address['swzoom']})
						},
						scrollwheel: {!$scrollWheel},
						panControl: false,
						enableCloseButton: true
					}
				}
			},
				{/if}
			{/if}
		}, {$autoZoomAndFit});

		setTimeout(function(){
			checkTouchDevice();
		},4000);

		/* google earth test */
		if(typeof GoogleEarth != 'undefined'){
			var gmap = $mapDiv.gmap3("get");
			var googleEarth = new GoogleEarth(gmap);
		}
		/* google earth test */


		{if $options->theme->general->progressivePageLoading}
			if(!isResponsive(1024)){
				jQuery("#{!$htmlId}").waypoint(function(){
					jQuery("#{!$htmlId}").parent().parent().addClass('load-finished');
				}, { triggerOnce: true, offset: "95%" });
			} else {
				jQuery("#{!$htmlId}").parent().parent().addClass('load-finished');
			}
		{else}
			jQuery("#{!$htmlId}").parent().parent().addClass('load-finished');
		{/if}



		var checkTouchDevice = function() {
			if (Modernizr.touch){
				map = $mapDiv.gmap3("get");
				var swPanorama = $mapDiv.gmap3({
					get: {
						name:"streetviewpanorama",
					}
				})
				if (typeof swPanorama !== "undefined") {
					var svBox = $mapDiv.children('.gm-style').get(1);
					jQuery(svBox).css({'pointer-events': 'none'});
				}
				map.setOptions({ draggable : false });
				var draggableClass = 'inactive', draggableTitle = {__ 'Activate map'};
				var draggableButton = jQuery('<div class="draggable-toggle-button '+draggableClass+'">'+draggableTitle+'</div>').appendTo($mapDiv);

				draggableButton.click(function () {
					if(jQuery(this).hasClass('active')){
						jQuery(this).removeClass('active').addClass('inactive').text({__ 'Activate map'});
						if (typeof svBox !== "undefined") {
							jQuery(svBox).css({'pointer-events': 'none'});
						}
						map.setOptions({ draggable : false });
					} else {
						jQuery(this).removeClass('inactive').addClass('active').text({__ 'Deactivate map'});

						if (typeof svBox !== "undefined") {
							jQuery(svBox).css({'pointer-events': 'initial'});
						}
						map.setOptions({ draggable : true });
					}
				});
			}
		}

		// {if isset($address['streetview'])}
		// 	{if $address['streetview']}
		// 		if(isMobile() && !{!$scrollWheel}){
		// 			jQuery("#{!$htmlId}").css({'pointer-events': 'none'});
		// 		}
		// 	{/if}
		// {/if}

		{if !isset($_REQUEST['rad'])}
		google.maps.event.addListenerOnce($mapDiv.gmap3("get"), 'tilesloaded', function(){
			jQuery("#{!$htmlId}").removeClass('deactivated');
		});
		{/if}

	});
</script>
