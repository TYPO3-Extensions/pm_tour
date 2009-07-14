// This Javascript is based on code provided by the
// Community Church Javascript Team
// http://www.bisphamchurch.org.uk/   
// http://econym.org.uk/gmap/

// ==== The "More..." control simply accepts a mouseover to reveal the "Layer" control ===

function MoreControl() {
}

MoreControl.prototype = new GControl();

MoreControl.prototype.initialize = function(map) {
	var container = document.createElement("div");
	container.style.border = "2px solid black";
	container.style.fontSize = "12px";
	container.style.fontFamily = "Arial, sans-serif";
	container.style.width = "80px";
	container.style.backgroundColor = "#ffffff";
	container.style.textAlign = "center";
	container.innerHTML = "More...";

	map.getContainer().appendChild(container);

	GEvent.addDomListener(container, "mouseover", function() {
		map.addControl(layerControl);
	});

	return container;
}

MoreControl.prototype.getDefaultPosition = function() {
	return new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(210, 7));
}

// ==== The "Layer" control displays the "More..." plus the checkboxes ====
// ==== The checkbox info is passed in the "opts" parameter ====

function LayerControl(opts) {
	this.opts = opts;
}
LayerControl.prototype = new GControl();

LayerControl.prototype.initialize = function(map) {
	var container = document.createElement("div");

	container.style.border = "2px solid black";
	container.style.fontSize = "12px";
	container.style.fontFamily = "Arial, sans-serif";
	container.style.width = "80px";
	container.style.backgroundColor = "#ffffff";
	container.innerHTML = '<center><b>More...<\/b><\/center>';
	for ( var i = 0; i < this.opts.length; i++) {
		var c = "helolo";
		if (layers[i].Visible) {
			c = 'checked';
		} else {
			c = '';
		}
		window.map = map;
		container.innerHTML += '<input type="checkbox" onclick="toggleLayer('
				+ i + ')" ' + c + ' /> ' + this.opts[i] + '<br>';		
	}

	map.getContainer().appendChild(container);

	// === This doesn't do what I want. It kills the control if I mouseover a
	// checkbox ===
	// === If you know how to do this better, let me know ===

	// GEvent.addDomListener(container, "mouseout", function() {
	// map.removeControl(layerControl);
	// });

	setTimeout("window.map.removeControl(layerControl)", 5000);

	return container;
}

LayerControl.prototype.getDefaultPosition = function() {
	return new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(210, 7));
}

// ==== toggleLayer adds and removes the layers ====
function toggleLayer(i) {
	if (layers[i].Visible) {
		layers[i].hide();
	} else {
		if (layers[i].Added) {
			layers[i].show();
		} else {
			window.map.addOverlay(layers[i]);
			layers[i].Added = true;
		}
	}
	layers[i].Visible = !layers[i].Visible;
}

//var map = new GMap2(document.getElementById("map"));

//map.setCenter(new GLatLng(43.907787, -79.359741), 8);
//map.addControl(new GMapTypeControl());
//map.addControl(new GLargeMapControl());

// ==== Create the GLayer()s, and set them Visible=false Added=false ====
// If you want a GLayer open by default, addOverlay() it and set it Visible=true
// Added=true

//var layers = [];
//layers[0] = new GLayer("org.wikipedia.en");
//layers[0].Visible = false;
//layers[0].Added = false;

// === Create the layerControl, but don't addControl() it ===
// = Pass it an array of names for the checkboxes =
//var layerControl = new LayerControl( [ "Wiki", "Wike DE", "Photos", "Popular" ]);

// === Create the MoreControl(), and do addControl() it ===
//map.addControl(new MoreControl());
    

