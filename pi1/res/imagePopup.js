
var imagePopup = new function() {

	var namespace = {};

	function ImagePopup(map, waypoint, imageInfo) {
		var ip = this;
		this.imageInfo = imageInfo;
		this.waypoint = waypoint;

		var icon = this.updateIcon(map, {url: waypoint.image});
		this.marker = new google.maps.Marker({
			position : new google.maps.LatLng(waypoint.lat, waypoint.lng),
			map : map,
			title : waypoint.title,
			icon: icon
		});
		
		google.maps.event.addListener(this.marker, "mouseover", function(e) {
			ip.open(e);
		});

		google.maps.event.addListener(this.marker, "mouseout", function(e) {
			ip.close();
		});
		
		google.maps.event.addListener(this.marker, "click", this.createClickCallback());
			
		google.maps.event.addListener(map, 'zoom_changed', function() {
			ip.marker.setIcon(ip.updateIcon(map, ip.marker.icon));
		  });

		this.animation_duration = 500;
		// the anchor of the image which is placed onto the position of the marker.
		// the anchor starts in the bottom left corner and increases in direction top and right
		this.anchor = new google.maps.Point(30, 0);

	}
	
	ImagePopup.prototype = new google.maps.OverlayView();
	
	ImagePopup.prototype.createClickCallback = function() {
		var img = this.imageInfo.imageJQuery;
		return function() { img.click(); return false; };	
	}
	
	ImagePopup.prototype.updateIcon = function(map, icon) {
		var scale = 0.3 + (0.1*(map.getZoom()-12));
		scale = Math.max(0.05, Math.min(0.8, scale));
		namespace.debug("Zoom: " + map.getZoom() + ", scale: " + scale);
		var iconSize = new google.maps.Size(scale*this.imageInfo.width, scale*this.imageInfo.height);
		icon.scaledSize = new google.maps.Size(iconSize.width, iconSize.height);
		icon.anchor = new google.maps.Point(iconSize.width/2,iconSize.height/2);
		return icon;
	};


	ImagePopup.prototype.createDiv = function() {
		var href = this.waypoint.href || "";
		var src = this.waypoint.image;
		var title = this.waypoint.title;
		// there can be several divs be opened in parallel
		var html = '<div id="abc" class="imagePopup"><a href="' + href + '"><img src="'
				+ src + '" title="' + title + '" ></a></div>';

		// Add the InfoBox DIV to the pane
		this.div = $(html).appendTo($(this.getPanes()["floatPane"]));
		$(this.div, "a").click(this.createClickCallback());

		// This handler prevents an event in the popup div from being passed on to
		// the map.
		var cancelHandler = function(e) {
			e.cancelBubble = true;
			if (e.stopPropagation) {
				e.stopPropagation();
			}
		};
		var that = this;
		var endHandler = function(e) {
			that.close();
		};

		if (this.eventListeners) {
			namespace.debug("ERROR. listeners  not empty");
		}
		this.eventListeners = [];
		events = [ "mousedown", "mouseover", "mouseup", "mousemove", "cldick",
				"dblclick", "touchstart", "touchend", "touchmove" ];

		for (i = 0; i < events.length; i++) {
			this.eventListeners.push(google.maps.event.addDomListener(this.div
					.get(0), events[i], cancelHandler));
		}
		this.eventListeners.push(google.maps.event.addDomListener(this.div
				.get(0), "mouseout", endHandler));
		
	}

	ImagePopup.prototype.funCleanup = function() {
		// is not called as method, therefore save this in that
		var that = this;
		return function() {
			that.div.remove();
			that.div = null;
		};
	}

	ImagePopup.prototype.draw = function() {
		this.createDiv();

		var position = this.getProjection().fromLatLngToDivPixel(
				this.marker.getPosition());

		
		var correctedAnchor = this.correctAnchor();
		
		var left = Math.round(position.x) - correctedAnchor.x;
		// why negative? it should be positive
		var bottom = -1 * (Math.round(position.y) + correctedAnchor.y);
		
		this.div.css("left", left + "px");
		this.div.css("bottom", bottom + "px");
		
		this.div.animate({
			"width" : this.imageInfo.width,
			"opacity" : 1
		}, this.animation_duration);

	}

	
	/*
	 * check if the fully expanded image would overlap on the top or on the right
	 * and provide a corrected anchor which avoids overlapping. E.g if the image
	 * would exceed the right bound, the anchor would be set further right to move 
	 * the image to the left.
	 */
	ImagePopup.prototype.correctAnchor = function(anchor) {
		var correction_y = 0;
		var correction_x = 0;
		
		var position = this.getProjection().fromLatLngToContainerPixel(this.marker.getPosition());
		
		var mapDiv = $(this.map.getDiv());
		
		var left = Math.round(position.x) - this.anchor.x;
		var right = left + this.imageInfo.width;
		if ( right > mapDiv.width() ) {
			correction_x = right-mapDiv.width(); 
		} 
		
		
		var bottom =  (Math.round(position.y) + this.anchor.y);
		var top = bottom - this.imageInfo.height;
		if (top < 0) {
			correction_y = -1*top; 
		}
		return new google.maps.Point(this.anchor.x + correction_x, this.anchor.y+correction_y);
	}
	

	ImagePopup.prototype.onRemove = function() {

		this.div.animate({
			"width" : 75,
			"opacity" : 0
		}, {
			"duration" : this.animation_duration,
			"complete" : this.funCleanup()
		});

	}

	ImagePopup.prototype.open = function(e) {
		if (this.div) {
			namespace.debug("Still open, therefore not opening again");
			return;
		}
		this.setMap(this.marker.map);

	};

	ImagePopup.prototype.close = function() {
		if (this.eventListeners) {

			for (i = 0; i < this.eventListeners.length; i++) {

				google.maps.event.removeListener(this.eventListeners[i]);
			}
			this.eventListeners = null;
		}
		this.setMap(null);
	}
	
	namespace.debug = console.log
	//namespace.debug = function() {}

	/* public */
	namespace.create_image_popup = function(map, image, waypoint) {
		// now the image is loaded and height and width can be read
		namespace.debug("Image loaded from " + waypoint.image);
		var imageInfo = {
				height: image.height(),
				width: image.width(),
				imageJQuery: image
		}
		new ImagePopup(map, waypoint,imageInfo);
	}
	
	namespace.search_image = function(images, src) {
		// Need to create our own search because jquery searches for the src attribute  when using
		// a selector like [src="myImage.jpg"]. Unfortunately that attribute is not necessarily the same
		// as what you can see in the html source. E.g. it might have been translated into an absolute path
		// therefore we use <img>.attr("src") instead of <img>.src
		var img = null
		images.each(function(x, e) {
			if ($(e).attr("src") == src) {
				img = $(e)
			}
		})
		return img;
	}

	namespace.add_waypoints = function(map, waypoints) {
		var images = $('body img');
		namespace.debug("Found " + images.length + " images.");
		for ( var waypoint_key in waypoints) {
			var waypoint = waypoints[waypoint_key];
			var img = namespace.search_image(images, waypoint.image);
			if (!img) {
				namespace.debug("No image " + waypoint.image);
			} else {
				// bind img and waypoint to load callback function
				var load_cb = function(map, image, waypoint) {
					return function() {
						namespace.create_image_popup(map, image, waypoint);
					}
				}(map, img, waypoint);
				
				namespace.debug("Waiting for loading of image " + waypoint.image + " ...");
				// Doing the tmpImg trick here to get always a load event triggered, even if 
				// the image is already loaded and in cache
				var tmpImg = new Image() ;
				tmpImg.onload = load_cb;
			    tmpImg.src = img.attr('src') ;
			}
		}
	}

	return namespace;
}(); // end namespace imagePopup


