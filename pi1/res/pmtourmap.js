
// load async:
// var map_spec = { ... }
// onload="";
// setTimeout(pmtourmap(map_spec),500);

// Using styles: hidden, visible on fullscreen_background_div_id
// fullscreen, embedded
// do not set style on div. It is removed when going fullscreen.
// set size and height via map_spec attributes

// spec.fullscreen_background_div_id
// spec.div_id
// spec.track {
//  
// properties: { strokeColor: "#FF0000",strokeOpacity: 1.0, strokeWeight: 5}
// points : [ [lat1, lng1], [lat2, lng2], ..]
// spec.waypoints { lat, long, popup, ... }

// returns object with properties map, map_div
// and methods show_embedded, show_fullscreen

pmtourmap = function() {
	var namespace = {};

	var /* private */id_to_map = {};
	var /* private */create_map = function(map_id, spec) {
		var that;

		// private variable: the bounds where all waypoints and markers fit in
		var bounds = new google.maps.LatLngBounds();

		var default_spec = {
			title : 'GPX on map with pm_tour', // used in toolbar below map
			width : '800px',
			height : '640px',
			fullscreen_background_div_id : 'fullscreen_background',
			fullscreen_exit_text : 'Exit full screen modus',
			fullscreen_show_text : 'Enlarge map',
			waypoints : [],
			tracks : [],
			image_folder : 'typo3conf/ext/pm_tour/pi1/res'
		}

		var extend = function(first, second) {
			for (element in second) {
				first[element] = second[element];
			}
			return first;
		}

		var map_spec = extend(default_spec, spec);

		var create_latlng = function(lat, lng) {
			var lat_lng = new google.maps.LatLng(lat, lng);
			bounds.extend(lat_lng);
			return lat_lng;
		};

		var fitBounds = function() {
			that.map.fitBounds(bounds);
		};

		var create_markers = function() {
			map_spec.waypoints.map(create_marker)
		}

		var create_marker = function(waypoint) {
			// guard
			if (!(waypoint.lat && waypoint.lng)) {
				that.errors.push('invalid waypoint: '
						+ JSON.stringify(waypoint))
				return;
			}
			var marker = new google.maps.Marker({
				position : create_latlng(waypoint.lat, waypoint.lng),
				map : that.map,
				title : waypoint.title || '',
				icon : waypoint.icon
			});
			var showInfoWindow = function() {
				var infowindow = new google.maps.InfoWindow({
					content : waypoint.popup_html
				});
				infowindow.open(that.map, marker);
			};
			google.maps.event.addListener(marker, 'click', showInfoWindow);
		}

		var create_polylines = function() {
			map_spec.tracks.map(create_polyline);
		}

		var create_polyline = function(track) {
			var path = [], latlng;
			(track.points || []).map(function(point) {
				if (point.length != 2) {
					errors.push("invalid track point " + point);
				} else {
					path.push(create_latlng(point[0], point[1]));
				}
			});
			;
			var polyline = new google.maps.Polyline(extend({
				path : path
			}, track.properties));
			polyline.setMap(that.map);
		}

		var create_map = function() {
			that.map_div = document.getElementById(map_id) || create_map_div();
			set_map_size_embedded();
			var mapOptions = {
				mapTypeId : google.maps.MapTypeId.TERRAIN
			};
			that.map = new google.maps.Map(that.map_div, mapOptions);
			create_markers();
			create_polylines();
			fitBounds();
			create_map_toolbar_div();
		};

		var set_map_size_embedded = function() {
			that.map_div.setAttribute("style", "width:"+map_spec.width+";height: "+map_spec.height+";");
			that.map_div.setAttribute("class", "embedded");
		}
		
		/* creating divs with plain javascript */
		var create_map_div = function() {
			var new_div = document.createElement("div");
			new_div.setAttribute("id", map_id);
			document.body.appendChild(new_div);
			return new_div;
		};

		var create_fullscreen_div = function() {
			var new_div = document.createElement("div");
			new_div.setAttribute("id", map_spec.fullscreen_background_div_id)
			document.body.appendChild(new_div);
			var a = document.createElement("a");
			a.setAttribute("href", "javascript:pmtourmap.exit_fullscreen('"
					+ map_id + "');");
			new_div.appendChild(a)
			var img = document.createElement("img");
			a.appendChild(img);
			img.setAttribute("src", map_spec.image_folder + "/close.png")
			img.setAttribute("title", map_spec.fullscreen_exit_text);
			return new_div;
		};

		var create_map_toolbar_div = function() {
			var new_toolbar = document.createElement("div");
			that.map_div.parentNode.insertBefore(new_toolbar,
					that.map_div.nextSibling);
			new_toolbar.setAttribute("class", "map_toolbar");
			new_toolbar.setAttribute("style", "width: "+map_spec.width);
			var title_span = document.createElement("span");
			new_toolbar.appendChild(title_span);
			title_span.appendChild(document.createTextNode(map_spec.title));
			var a = document.createElement("a");
			a.setAttribute("href", "javascript:pmtourmap.show_fullscreen('"
					+ map_id + "');");
			new_toolbar.appendChild(a)
			var img = document.createElement("img");
			a.appendChild(img);
			img.setAttribute("src", map_spec.image_folder + "/fullscreen.png")
			img.setAttribute("title", map_spec.fullscreen_show_text);
		}

		that = {
			errors : []
		};

		// public methods
		that.show_fullscreen = function() {
			that.fullscreen_background_div = document
					.getElementById(map_spec.fullscreen_background_div_id)
					|| create_fullscreen_div();
			that.map_div.setAttribute("class", "fullscreen");
			that.map_div.removeAttribute("style");
			google.maps.event.trigger(that.map, 'resize');
			that.fullscreen_background_div.setAttribute("class", "visible");
			fitBounds();
		};

		that.exit_fullscreen = function() {
			that.map_div.setAttribute("class", "embedded");
			set_map_size_embedded();
			google.maps.event.trigger(that.map, 'resize');
			that.fullscreen_background_div.setAttribute("class", "hidden");
			fitBounds();
		};

		create_map();

		return that;
	}

	var /* private */create_maps = function() {
		for ( var map_id in namespace.map_specs) {
			var map_spec = namespace.map_specs[map_id];
			console.log(map_id);
			id_to_map[map_id] = create_map(map_id, map_spec);
		}
	}

	namespace.map_specs = {};

	window.onload = create_maps;

	namespace.show_fullscreen = function(map_id) {
		id_to_map[map_id] && id_to_map[map_id].show_fullscreen();
	};

	namespace.exit_fullscreen = function(map_id) {
		id_to_map[map_id] && id_to_map[map_id].exit_fullscreen();
	}
	return namespace;

}();// end namespace
