<script id="{$htmlId}-container-script">

(function($, $window, $document, globals){
"use strict";

$window.load(function(){

	addHeaderMapControls();

	function addHeaderMapControls() {
		var map = globals.globalMaps.headerMap.map;
		if (Modernizr.touchevents || Modernizr.pointerevents) {
			var disableControlDiv = document.createElement('div');
			var disableControl = new DisableHeaderControl(disableControlDiv, map);
			map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(disableControlDiv);
		}
	}

	function isAdvancedSearch() {
		var sPageURL = decodeURIComponent(window.location.search.substring(1)),
			sURLVariables = sPageURL.split('&'),
			sParameterName,
			i;

		for (i = 0; i < sURLVariables.length; i++) {
			sParameterName = sURLVariables[i].split('=');

			if (sParameterName[0] === "a") {
				return true;
			}
		}
		return false;
	}

	/**
	 * The DisableControl adds a control to the map.
	 * This constructor takes the control DIV as an argument.
	 * @constructor
	 */
	function DisableHeaderControl(controlDiv, map) {
		var containerID = jQuery("#{!$htmlId} .google-map-container").attr('id');
		var disableButton = document.createElement('div');
		disableButton.className = "draggable-toggle-button";
		jQuery(disableButton).html('<i class="fa fa-lock"></i>');

		controlDiv.appendChild(disableButton);

		jQuery(this).removeClass('active').html('<i class="fa fa-lock"></i>');
		map.setOptions({ draggable : false });

		google.maps.event.addDomListener(disableButton, 'click', function(e) {
			if(jQuery(this).hasClass('active')){
				jQuery(this).removeClass('active').html('<i class="fa fa-lock"></i>');
				map.setOptions({ draggable : false });
			} else {
				jQuery(this).addClass('active').html('<i class="fa fa-unlock"></i>');
				map.setOptions({ draggable : true });
			}
		});
	}
	
});

})(jQuery, jQuery(window), jQuery(document), this);

</script>