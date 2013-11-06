var gmaps = (function() {
	
	var map, lat_input, long_input, zoom_select;

	function update_map()
	{
		var myLatlng = new google.maps.LatLng(lat_input.val(), long_input.val());
		map.panTo(myLatlng);
		map.setZoom(parseInt(zoom_select.val()));
	}

	function update_latlong()
	{
		var latlong = map.getCenter();

		lat_input.val(latlong.lat());
		long_input.val(latlong.lng());
	}

	function update_zoom()
	{
		zoom_select.val(map.getZoom());
	}

	return function() {
		
		lat_input = $("input[name=latitude]"),
		long_input = $("input[name=longitude]"),
		zoom_select = $("select[name=zoom]");
		
		// Take them all and bind click and change events to update the map
		lat_input.add(long_input).add(zoom_select).bind("change, click", update_map);
		
		
		var myLatlng = new google.maps.LatLng(lat_input.val(), long_input.val()),
			myOptions = {
				zoom: parseInt(zoom_select.val()),
				center: myLatlng,
				scrollwheel: EE.gmaps.scroll,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			};

		map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

		update_latlong();
		update_zoom();

		var zoomLevel, defaultLatLong;

		google.maps.event.addListener(map, 'center_changed', function() {
			update_latlong();
		});

		google.maps.event.addListener(map, 'zoom_changed', function() {
			update_zoom();
		});
	}
	
})();