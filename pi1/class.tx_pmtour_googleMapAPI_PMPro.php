<?php

/**
 * Project:	GoogleMapAPI: a PHP library inteface to the Google Map API
 * File:	GoogleMapAPI.class.php
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * For questions, help, comments, discussion, etc., please join the
 * Smarty mailing list. Send a blank e-mail to
 * smarty-general-subscribe@lists.php.net
 *
 * @link http://www.phpinsider.com/php/code/GoogleMapAPI/
 * @copyright 2005 New Digital Group, Inc.
 * @author Monte Ohrt <monte at ohrt dot com>
 * @package GoogleMapAPI
 * @version 1.7
 */

/*

For best results with GoogleMaps, use XHTML compliant web pages with this header:

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">

*/

class tx_pmtour_googleMapAPI_PMPro {
	 
	var $api_key = "";
	var $map_id = "";
	var $width = "500px";
	var $height = "500px";
	var $map_type = "G_MAP_TYPE";
	var $markers = array();
	var $polylines = array();
	var $max_lon = -1000000;
	var $min_lon = 1000000;
	var $max_lat = -1000000;
	var $min_lat = 1000000;
	var $center_lat = 46.77429;
	var $center_lon = 7.57164;
	var $start_filter = 1;
	
		
	function tx_pmtour_googleMapAPI_PMPro($api_key, $map_type="map") {
		$this->api_key = $api_key;
		
		$this->map_id = "map".md5(uniqid(rand())); //"map"; //"map".md5(uniqid(rand()));
		$this->setMapType($map_type);
	}
	
	function setWidth($width) {
		if (!preg_match('!^(\d+)(.*)$!', $width, $_match)) {
			return false;
		}

		$_width = $_match[1];
		$_type = $_match[2];
		if ($_type == '%') {
			$this->width = $_width . '%';
		}
		else {
			$this->width = $_width . 'px';
		}

		return true;
	}
	
	function setStartFilter($filter) {
		if (is_numeric($filter)){
			if ($filter > 0) {
				$this->start_filter=$filter;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	function setHeight($height) {
		if (!preg_match('!^(\d+)(.*)$!', $height,$_match)) {
			return false;
		}

		$_height = $_match[1];
		$_type = $_match[2];
		if ($_type == '%') {
			$this->height = $_height . '%';
		}
		else {
			$this->height = $_height . 'px';
		}

		return true;
	}
	
	function setSize($width, $height) {
		$this->setWidth($width);
		$this->setHeight($height);
	}
	
	function setMapType($type) {
		switch ($type) {
			case 'hybrid':
				$this->map_type = 'G_HYBRID_MAP';
				break;
			case 'satellite':
				$this->map_type = 'G_SATELLITE_MAP';
				break;
			case 'map':
			default:
				$this->map_type = 'G_NORMAL_MAP';
				break;
		}
	}
	
	function clear() {
		$this->markers = array();
		$this->polylines = array();
		$this->max_lon = -1000000;
		$this->min_lon = 1000000;
		$this->max_lat = -1000000;
		$this->min_lat = 1000000;
		$this->center_lat = 46.77429;
		$this->center_lon = 7.57164;
	}
	
	function addMarker($lat, $lon, $title, $hover, $html = '', $iconImage = '', $iconShadowImage = '') {
		$marker['lon'] = $lon;
		$marker['lat'] = $lat;
		$marker['title'] = $title;
		$marker['html'] = $html;
		$marker['hover'] = $hover;
		if ($iconImage) {
			if (!($_image_info = @getimagesize($iconImage))) {
				die('GoogleMapAPI:createMarkerIcon: Error reading image: ' . $iconImage);
			}
			if ($iconShadowImage) {
				if (!($_shadow_info = @getimagesize($iconShadowImage))) {
					die('GoogleMapAPI:createMarkerIcon: Error reading image: ' . $iconShadowImage);
				}
			}
			$iconAnchorX = (int) ($_image_info[0] / 2);
			$iconAnchorY = (int) ($_image_info[1] / 2);
			$infoWindowAnchorX = (int) ($_image_info[0] / 2);
			$infoWindowAnchorY = (int) ($_image_info[1] / 2);
			$icon_info = array(
								'image' => $iconImage,
								'iconWidth' => $_image_info[0],
								'iconHeight' => $_image_info[1],
								'iconAnchorX' => $iconAnchorX,
								'iconAnchorY' => $iconAnchorY,
								'infoWindowAnchorX' => $infoWindowAnchorX,
								'infoWindowAnchorY' => $infoWindowAnchorY
							);
			if ($iconShadowImage) {
				$icon_info = array_merge($icon_info, array(
															'shadow' => $iconShadowImage,
															'shadowWidth' => $_shadow_info[0],
															'shadowHeight' => $_shadow_info[1])
															);
			}
			$marker['icon'] = $icon_info;
		}
		$this->markers[] = $marker;
		$this->adjustCenterCoords($marker['lon'], $marker['lat']);
		return count($this->markers) - 1;
	}
	
	function addPolyline($color = '', $weight = 0, $opacity = 0) {
		$polyline["color"] = $color;
		$polyline["weight"] = $weight;
		$polyline["opacity"] = $opacity;
		$this->polylines[] = $polyline;
		return count($this->polylines) - 1;
	}
	
	function addPolylinePoint($polyline,$lat,$lon) 
	{
		$pt["lat"] = $lat;
		$pt["lon"] = $lon;
		$this->polylines[$polyline]["points"][] = $pt;
		$this->adjustCenterCoords($pt['lon'], $pt['lat']);
		return count($this->polylines[$polyline]["points"]) - 1;
	}
	
	function adjustCenterCoords($lon, $lat) {
		if (strlen((string)$lon) == 0 || strlen((string)$lat) == 0) {
			return false;
		}
		$this->max_lon = max($lon, $this->max_lon);
		$this->min_lon = min($lon, $this->min_lon);
		$this->max_lat = max($lat, $this->max_lat);
		$this->min_lat = min($lat, $this->min_lat);
		$this->center_lon = ($this->min_lon + $this->max_lon) / 2;
		$this->center_lat = ($this->min_lat + $this->max_lat) / 2;
		return true;
	}	

	
	function getHeaderScript() {
		$ret .= $this->addLine('<script src="http://maps.google.com/maps?file=api&v=2.s&key=%s" charset="utf-8"></script>');
	    $ret .= $this->addLine('<script type="text/javascript">');
		$ret .= $this->addLine('/*<![CDATA[*/',1);
		$ret .= $this->addLine('<!--',1);	
		$ret .= $this->addLine('window.onunload = GUnload;',1);
		$ret .= $this->addLine('document.onunload = GUnload;',1);
		$ret .= $this->addLine('// -->',1);
		$ret .= $this->addLine('/*]]>*/',1);
		$ret .= $this->addLine('</script>');
		return sprintf($ret, $this->api_key);
	}
	
	function getDrawFilterLink($filter,$title) {
		return '<a href="javascript: draw_overlays_'.$this->map_id.'('.$filter.');">'.$title.'</a>';
	}
	
	function getContentElement() {
		$mapname = $this->map_id;
		$markersname = $this->map_id."markers";
		$iconsname = $this->map_id."icons";
		$polylinesname = $this->map_id."polylines";
		//Div
		$ret .= $this->addLine(sprintf('<div id="%s" style="width: %s; height: %s"></div>',$this->map_id, $this->width, $this->height));
		$ret .= $this->addLine('<script type="text/javascript">');
		$ret .= $this->addLine('/*<![CDATA[*/',1);
		$ret .= $this->addLine('<!--',1);	
		//Initializing
		$ret .= $this->addLine(sprintf('var %s = null;',$mapname),2);	
		$ret .= $this->addLine(sprintf('var %s = Array();',$markersname),2);
		$ret .= $this->addLine(sprintf('var %s = Array();',$iconsname),2);
		$ret .= $this->addLine(sprintf('var %s = Array();',$polylinesname),2);
		$ret .= $this->addLine();
		
		//Create Markers
		$ret .= $this->addLine(sprintf('function create_markers_%s() {', $this->map_id),2);
		$i=0;
		$icons = array();
		foreach($this->markers as $marker) {
			$gmarkeroptions = array(); // java script options, e.g. {title:"My Way Point",icon:mapa390e50e2d0c7fc8f27e2ea71a559d5aicons['0']}
			if ($marker["icon"]) {
				$icon = $icons[$marker["icon"]["image"].$marker["icon"]["shadow"]];
				
				if (!is_numeric($icon)) {
					$icon = count($icons);
					$icons[$marker["icon"]["image"].$marker["icon"]["shadow"]] = $icon;
					$ret .= $this->addLine(sprintf('%s['.$icon.'] = new GIcon();',$iconsname),3);
					$ret .= $this->addLine(sprintf('%s['.$icon.'].image = "%s";',$iconsname,$marker["icon"]["image"]),3);
					if ($marker["icon"]["shadow"]) {
						$ret .= $this->addLine(sprintf('%s['.$icon.'].shadow = "%s";', $iconsname, $marker["icon"]["shadow"]),3);
						$ret .= $this->addLine(sprintf('%s['.$icon.'].shadowSize = new GSize(%s, %s);', $iconsname, $marker["icon"]["shadowWidth"], $marker["icon"]["shadowHeight"]) , 3);
					}
					$ret .= $this->addLine(sprintf('%s['.$icon.'].iconSize = new GSize(%s, %s);', $iconsname, $marker["icon"]["iconWidth"], $marker["icon"]["iconHeight"]),3);
					$ret .= $this->addLine(sprintf('%s['.$icon.'].iconAnchor = new GPoint(%s, %s);', $iconsname, $marker["icon"]["iconAnchorX"], $marker["icon"]["iconAnchorY"]),3);
					$ret .= $this->addLine(sprintf('%s['.$icon.'].infoWindowAnchor = new GPoint(%s, %s);', $iconsname, $marker["icon"]["infoWindowAnchorX"], $marker["icon"]["infoWindowAnchorY"]),3);
				}
				array_push($gmarkeroptions, "icon:".$iconsname."['".$icon."']");
			}
			array_push($gmarkeroptions, "title:'".$marker["hover"]."'");
			$ret .= $this->addLine(sprintf('%s['.$i.'] = new GMarker(new GLatLng(%s,%s),{%s});',$markersname, $marker["lat"], $marker["lon"], implode(",",$gmarkeroptions)), 3);
			if ($marker["html"] != null) {
				$html = str_replace("'","\'",$marker["html"]);
				$ret .= $this->addLine(sprintf('GEvent.addListener(%s['.$i.'], "click", function() { %s['.$i.'].openInfoWindowHtml(\'%s\'); });',$markersname,$markersname,$html),3);
			}
			$i++;
		}
		$ret .= $this->addLine('}',2);
		$ret .= $this->addLine();

		//Create PolyLines
		$ret .= $this->addLine(sprintf('function create_polylines_%s() {', $this->map_id),2);
		$i=0;
		$icons = array();
		foreach($this->polylines as $polyline) {
			$ret .= $this->addLine(sprintf('%s['.$i.'] = Array();',$polylinesname),3);
			$j=0;
			foreach($polyline["points"] as $point) {
				$ret .= $this->addLine(sprintf('%s['.$i.']['.$j.'] = new GLatLng(%s,%s);',$polylinesname, $point["lat"], $point["lon"]),3);
				$j++;
			}
			$i++;
		}
		$ret .= $this->addLine('}',2);
		$ret .= $this->addLine();

		//Draw Overlays
		$ret .= $this->addLine(sprintf('function draw_overlays_%s(filter) {', $this->map_id),2);
		$ret .= $this->addLine(sprintf('%s.clearOverlays();',$mapname),3);	
		$ret .= $this->addLine(sprintf('for (var i=0; i<%s.length; i++) {', $markersname),3);
		$ret .= $this->addLine(sprintf('%s.addOverlay(%s[i]);', $mapname, $markersname),4);
		$ret .= $this->addLine('}',3);
		$i=0;
		foreach($this->polylines as $polyline) {				
			$ret .= $this->addLine(sprintf('for (var i=filter; i<%s['.$i.'].length-2; i=i+filter) {', $polylinesname),3);
			$ret .= $this->addLine(sprintf('%s.addOverlay(new GPolyline([%s['.$i.'][i-filter],%s['.$i.'][i]], "%s", %s, %s));', $mapname, $polylinesname, $polylinesname, $polyline["color"], $polyline["weight"], $polyline["opacity"]/100),4);
			$ret .= $this->addLine('}',3);
			$ret .= $this->addLine(sprintf('if (i-filter != %s['.$i.'].length-1){', $polylinesname),3);
			$ret .= $this->addLine(sprintf('%s.addOverlay(new GPolyline([%s['.$i.'][i-filter],%s['.$i.'][%s['.$i.'].length-1]], "%s", %s, %s));', $mapname, $polylinesname, $polylinesname, $polylinesname, $polyline["color"], $polyline["weight"], $polyline["opacity"]/100),4);
			$ret .= $this->addLine('}',3);
			$i++;
		}
		$ret .= $this->addLine('}',2);
		$ret .= $this->addLine();
		
		//Loading
		$ret .= $this->addLine(sprintf('function load_%s() {', $this->map_id),2);		
		$ret .= $this->addLine(sprintf('%s = new GMap2(document.getElementById("%s"));',$mapname,$this->map_id),3);
		$ret .= $this->addLine(sprintf('%s.addControl(new GLargeMapControl());',$mapname),3);
		$ret .= $this->addLine(sprintf('%s.addControl(new GMapTypeControl());',$mapname),3);		
		$ret .= $this->addLine(sprintf('%s.setCenter(new GLatLng(%s,%s), 10);',$mapname, $this->center_lat,$this->center_lon),3);	
		$ret .= $this->addLine(sprintf('var zoom = %s.getBoundsZoomLevel(new GLatLngBounds(new GLatLng(%s,%s),new GLatLng(%s,%s)));',$mapname,$this->min_lat,$this->min_lon,$this->max_lat,$this->max_lon),3);	
		$ret .= $this->addLine(sprintf('%s.setCenter(new GLatLng(%s,%s), zoom);',$mapname, $this->center_lat,$this->center_lon),3);	
		$ret .= $this->addLine(sprintf('%s.setMapType(%s);',$mapname,$this->map_type),3);						
		$ret .= $this->addLine(sprintf('create_markers_%s();', $this->map_id),3);		
		$ret .= $this->addLine(sprintf('create_polylines_%s();', $this->map_id),3);		
		$ret .= $this->addLine(sprintf('draw_overlays_%s(%s);', $this->map_id, $this->start_filter),3);
		$ret .= $this->addLine('}',2);
		$ret .= $this->addLine();
		
		//Running
		$ret .= $this->addLine('if (GBrowserIsCompatible()) {',2);
		$ret .= $this->addLine(sprintf('setTimeout(load_%s,500);', $this->map_id),3);
		$ret .= $this->addLine('} else {',2);
		$ret .= $this->addLine('document.write(\'<b>Javascript must be enabled in order to use Google Maps.</b>\');',3);
		$ret .= $this->addLine('}',2);
		$ret .= $this->addLine('// -->',1);
		$ret .= $this->addLine('/*]]>*/',1);
		$ret .= $this->addLine('</script>');
		return $ret;
	}	
	
	function addLine($text='', $indent = 0) {
		$ret = "";
		for ($i=0;$i<$indent;$i++) {
			$ret .= "   ";
		}
		return $ret.$text."\n";
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pm_tour/pi1/class.tx_pmtour_googleMapAPI_PMPro.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pm_tour/pi1/class.tx_pmtour_googleMapAPI_PMPro.php']);
}
?>