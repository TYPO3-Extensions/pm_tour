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
	var $markers = array();
	var $polylines = array();
	
		
	function tx_pmtour_googleMapAPI_PMPro($api_key, $map_type_id, $conf_panoramio) {
		$this->api_key = $api_key;
		
		$this->map_id = "map".md5(uniqid(rand())); //"map"; //"map".md5(uniqid(rand()));
		$this->setMapTypeId($map_type_id);
		$this->conf_panoramio=$conf_panoramio;
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
	
	function setTitle($title) {
		$this->title=$title;
	}
	
	function setImageMarkerMaxLength($imageMarkerMaxLength) {
		$this->imageMarkerMaxLength = $imageMarkerMaxLength;
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
	
	function setMapTypeId($map_type_id) {
		$this->map_type_id = $map_type_id;
	}
	
	function clear() {
		$this->markers = array();
		$this->polylines = array();
		$this->title ="";
	}
	
	function addMarker($lat, $lon, $title, $hover, $html = '', $iconImage = '', $iconShadowImage = '', $image=null, $href=null) {
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
		if ($image) {
			$marker['image'] = $image;
		}
		if ($href) {
			$marker['href'] = $href;
		}
		$this->markers[] = $marker;
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
		return count($this->polylines[$polyline]["points"]) - 1;
	}
	
	function getHeaderScript($includeJQuery) {
		if ($includeJQuery) {
			$ret .= $this->addLine('<script type="text/javascript" src="typo3conf/ext/pm_tour/pi1/res/jquery-1.5.1.min.js"></script>');
		}
		$libraries = $this->conf_panoramio["enabled"] ? 'libraries=panoramio&' : '';
		$ret .= $this->addLine('<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?'.$libraries.'sensor=false&key=%s"></script>');
		$ret .= '<script src="typo3conf/ext/pm_tour/pi1/res/pmtourmap.js" type="text/javascript"></script>';
		$ret .= '<script src="typo3conf/ext/pm_tour/pi1/res/imagePopup.js" type="text/javascript"></script>';
		return sprintf($ret, $this->api_key);
	}
	
	function getContentElement() {
		$mapname = $this->map_id;
		$markersname = $this->map_id."markers";
		$iconsname = $this->map_id."icons";
		$polylinesname = $this->map_id."polylines";
		// create div ...
		$ret .= $this->addLine(sprintf('<div id="%s"></div>',$this->map_id));
		// ... and map spec in javascript
		$ret .= $this->addLine('<script type="text/javascript">');
		$ret .= $this->addLine(sprintf("pmtourmap.map_specs['%s'] =  {", $this->map_id),2);
		$ret .= $this->addLine("mapTypeId:'" . $this->map_type_id . "',", 3);
		$ret .= $this->addLine("title:'" . $this->title . "',", 3);
		$ret .= $this->addLine("width:'" . $this->width . "',", 3);
		$ret .= $this->addLine("height:'" . $this->height . "',", 3);
		$ret .= $this->addLine("imageMarkerMaxLength: " . $this->imageMarkerMaxLength . ",", 3);
		if ($this->conf_panoramio["enabled"]) {
			$ret .= $this->addLine("panoramio: { button_label: '".$this->conf_panoramio["button_label"]."', button_title: '".$this->conf_panoramio["button_title"]."', is_enabled: true },", 3);
		}
		
		
		//Create Markers
		$ret .= $this->addLine('waypoints: [', 3);
		$i=0;
		$icons = array();
		foreach($this->markers as $marker) {
			if ($i > 0) {
				$ret .= $this->addLine(",",4);
			}
			$ret .= $this->addLine('{ // start waypoint',4);
			$gmarkeroptions = array(); // java script options, e.g. {title:"My Way Point",icon:mapa390e50e2d0c7fc8f27e2ea71a559d5aicons['0']}
			if ($marker["icon"]) {
				$icon = $icons[$marker["icon"]["image"].$marker["icon"]["shadow"]];
				/*
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
				*/
				array_push($gmarkeroptions, "icon:'".$marker["icon"]["image"]."'");
			}
			array_push($gmarkeroptions, "title:'".$marker["hover"]."'");
			array_push($gmarkeroptions, "lat:".$marker["lat"]."");
			array_push($gmarkeroptions, "lng:".$marker["lon"]."");
			if ($marker["image"]) {
				array_push($gmarkeroptions, "image:'".$marker["image"]."'");
			}
			if ($marker["href"]) {
				array_push($gmarkeroptions, "href:'".$marker["href"]."'");
			}
			if ($marker["html"] != null) {
				$html = str_replace("'","\'",$marker["html"]);
				array_push($gmarkeroptions, "popup_html:'".$html."'");
				// without setting maxWidth the popup window can become quite broad, so that the user must pan the map to reach the close button
				// todo: set maxWidth of popup_html to $this->width-100)
			}
			$ret .= $this->addLine(implode(",",$gmarkeroptions),5);
			$ret .= $this->addLine('}// end waypoint',4);
			$i++;
		}
		$ret .= $this->addLine('], // end waypoints', 3);
		
		$ret .= $this->addLine('tracks: [', 3);
		$i=0;
		$icons = array();
		foreach($this->polylines as $polyline) {
			$j=0;
			$points = "[";
			foreach($polyline["points"] as $point) {
				if ($j > 0) {
					$points .= ",";
				}
				$points .= '['.$point["lat"].','.$point["lon"]."]";
				$j++;
			}
			$points .= "],";
			$properties = 'strokeColor:"'.$polyline["color"].'", strokeWeight:'.$polyline["weight"].", strokeOpacity:".$polyline["opacity"]/100;
			if ($i > 0) {
				$ret .= $this->addLine(",",4);
			}
			$ret .= $this->addLine("{", 4);
			$ret .= $this->addLine("points: ".$points, 5);
			$ret .= $this->addLine("properties: {".$properties."}", 5);
			$ret .= $this->addLine("} // end track", 4);
			$i++;
		}
		
		$ret .= $this->addLine('] // end tracks', 3);
		$ret .= $this->addLine('}; // end map_spec',2);
		
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