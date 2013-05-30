
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
			mapTypeId: 'terrain',
			title : 'GPX on map with pm_tour', // used in toolbar below map
			width : '800px',
			height : '640px',
			imageMarkerMaxLength: 64, 
			fullscreen_background_div_id : 'fullscreen_background',
			fullscreen_exit_text : 'Exit full screen modus',
			fullscreen_show_text : 'Enlarge map',
			waypoints : [],
			tracks : [],
			image_folder : 'typo3conf/ext/pm_tour/pi1/res',
			panoramio: false
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
			for (var marker in map_spec.waypoints) {
				create_marker(map_spec.waypoints[marker])
			}
		}

		var create_marker = function(waypoint) {
			// guard
			if (!(waypoint.lat && waypoint.lng)) {
				that.errors.push('invalid waypoint: '
						+ JSON.stringify(waypoint))
				return;
			}
			if (waypoint.image) {
				imagePopup.add_waypoints(that.map, [waypoint], {max_length: map_spec.imageMarkerMaxLength });
			} else {
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
		}

		var create_polylines = function() {
			for (var marker in map_spec.tracks) {
				create_polyline(map_spec.tracks[marker])
			}
		}

		var create_polyline = function(track) {
			var path = [], latlng;
			var points = track.points || [];
			for (var i=0; i<points.length; i++) {
				var point = points[i];
				if (point.length != 2) {
					errors.push("invalid track point " + point);
				} else {
					path.push(create_latlng(point[0], point[1]));
				}
			}
			var polyline = new google.maps.Polyline(extend({
				path : path
			}, track.properties));
			polyline.setMap(that.map);
		}

		var create_map = function() {
			that.map_div = document.getElementById(map_id) || create_map_div();
			set_map_size_embedded();
			var mapOptions = {
				mapTypeId : map_spec.mapTypeId
			};
			that.map = new google.maps.Map(that.map_div, mapOptions);
			create_markers();
			create_polylines();
			fitBounds();
			create_map_toolbar_div();
			add_panoramio_layer();
		};

		var set_map_size_embedded = function() {
			that.map_div.style.height=map_spec.height;
			that.map_div.style.width=map_spec.width;
			$(that.map_div).removeClass("fullscreen").addClass("embedded");
		}
		
		/* creating divs with plain javascript
		 * that was before jquery was added, please rewrite */
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
			$(new_div).click(function() {that.exit_fullscreen()});
			return new_div;
		};

		var create_map_toolbar_div = function() {
			var href= "javascript:pmtourmap.show_fullscreen('"+ map_id + "');";
			var src = map_spec.image_folder + "/fullscreen.png";
			var title =  map_spec.fullscreen_show_text;
			var style = "width: " + map_spec.width +";";
			// the a element should be on the right side. In order to make IE not
			// insert a new line it is inserted before the non-floating span.
			var html = '<div class="map_toolbar" style="'+style+'"><a href="'+href+'"><img src="'+src+'" title="'+title+'"></a><span>'+map_spec.title+'</span></div>';
			$(that.map_div).after(html);
		}
		
		var add_panoramio_layer = function() {
			// google.maps.panoramio library must be loaded via libraries parameter
			if (!map_spec.panoramio || !google.maps.panoramio) {
				return;
			}
			// create the panoramio layer but do not show it initially
			var panoramioLayer = new google.maps.panoramio.PanoramioLayer()
			var button = create_panoramio_button();
			that.map.controls[google.maps.ControlPosition.TOP_RIGHT].push(button);
			google.maps.event.addDomListener(button, 'click', function() {
				panoramioLayer.setMap(panoramioLayer.getMap() ? null : that.map);
			});
		};
		
		var create_panoramio_button = function () {
			  var controlDiv = document.createElement("div");
			  // example for custom control from 
			  // https://developers.google.com/maps/documentation/javascript/examples/control-custom
			  controlDiv.style.padding = '5px';

			  // Set CSS for the control border
			  var controlUI = document.createElement('div');
			  controlUI.style.backgroundColor = 'white';
			  controlUI.style.borderStyle = 'solid';
			  controlUI.style.borderWidth = '1px';
			  controlUI.style.cursor = 'pointer';
			  controlUI.style.textAlign = 'center';
			  controlUI.title = 'Show/Hide Panoramio Photos';
			  controlDiv.appendChild(controlUI);

			  // Set CSS for the control interior
			  var controlText = document.createElement('div');
			  controlText.style.fontFamily = 'Arial,sans-serif';
			  controlText.style.fontSize = '12px';
			  controlText.style.paddingLeft = '4px';
			  controlText.style.paddingRight = '4px';
			  controlText.innerHTML = '<b>Panoramio</b>';
			  controlUI.appendChild(controlText);
			  
			  return controlDiv;
		}

		that = {
			errors : []
		};

		// public methods
		that.show_fullscreen = function() {
			that.fullscreen_background_div = document
					.getElementById(map_spec.fullscreen_background_div_id)
					|| create_fullscreen_div();
			$(that.map_div).removeClass("embedded").addClass("fullscreen");
			$(that.map_div).removeAttr("style");
			google.maps.event.trigger(that.map, 'resize');
			$(that.fullscreen_background_div).removeClass("hidden").addClass("visible");
			fitBounds();
		};

		that.exit_fullscreen = function() {
			set_map_size_embedded();
			google.maps.event.trigger(that.map, 'resize');
			$(that.fullscreen_background_div).removeClass("visible").addClass("hidden");
			fitBounds();
		};

		create_map();

		return that;
	}

	var /* private */create_maps = function() {
		for ( var map_id in namespace.map_specs) {
			var map_spec = namespace.map_specs[map_id];
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
