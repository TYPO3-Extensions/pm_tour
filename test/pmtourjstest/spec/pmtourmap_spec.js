describe("Pm Tour map", function() {

  var base_spec = { div_id : 'map' };
  
  // for some reason spyOn(google.maps, Marker).andCallTrough does not
  // work as expected. The interceptor is a recplacement.
  var intercept = function(targetObject, targetFunctionName) {
	  var result = {};
	  var orig = targetObject[targetFunctionName];
	  
	  targetObject[targetFunctionName] = function(args) { 
		  result.args = args; 
		  result.created = new orig(args); 
		  return result.created;
      };
	  
	  return result;
  }


  beforeEach(function() {
	  
	  var map_div = document.getElementById(base_spec.div_id);
	  if (map_div) {
		  map_div.parentNode.removeChild(map_div);
	  }
     });
 
  
  it("should create a marker for a minimal waypoint", function() {
	spyOn(google.maps, "Marker");
	var spec= _.extend(base_spec, { waypoints : [ { // correct minimal waypoint
	                           		lat: 5,
	                           		lng: 10
	                           	 } ] });
	var tourmap = pmtourmap(spec);
	expect(google.maps.Marker).toHaveBeenCalledWith({map: tourmap.map, title: '', position: new google.maps.LatLng(5,10)});
  });
  
  it("should ignore invalid waypoints", function() {
	spyOn(google.maps, "Marker");
	var spec = { waypoints : [ { lng: 10 }, { lat: 10}, {} ] };
	var tourmap = pmtourmap(_.extend(base_spec, spec));
	expect(google.maps.Marker).not.toHaveBeenCalled();
	expect(tourmap.errors).toEqual(['invalid waypoint: {"lng":10}', 'invalid waypoint: {"lat":10}', 'invalid waypoint: {}' ]);
  });
  
  it("should create a title and popup for waypoints", function() {
	var html = '<br>test<br>';
	var interceptor = intercept(google.maps, 'Marker');
	var spec = { waypoints : [ {    lat: 1,
	                           		lng: 2,
	                           		title: 'My Marker',
	                           		popup_html: html
	                           	 } ] };
	var tourmap = pmtourmap(_.extend(base_spec, spec));
	expect(interceptor.created).not.toBeNull()
	google.maps.event.trigger(interceptor.created, 'click');
	
	waitsFor(function() {
		return tourmap.map_div.outerHTML.indexOf(html) > -1 ;
	}, "The popup should be displaying and contain the html", 2000);
  });
  
  
  it ("should create a polyline for tracks", function() {
	var interceptor = intercept(google.maps, 'Polyline');
	var props = { strokeColor: "#FF0010",strokeOpacity: 1.0, strokeWeight: 7}
	var spec = { tracks : [ {
		properties: props, 
		points : [ [1, 2], [2, 3] ]
		}]
	};
	var tourmap = pmtourmap(_.extend(base_spec, spec));
	var expectedPath = [ new google.maps.LatLng(1,2), new google.maps.LatLng(2,3) ]
	expect(interceptor.args).toEqual(_.extend({ path: expectedPath, visible: true}, props))  
  });
 
});