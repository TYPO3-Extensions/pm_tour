<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Patrick Matusz
*  (c) 2007 Markus Barchfeld (Markus.Barchfeld@gmx.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Plugin 'Tour' for the 'pm_tour' extension.
 *
 * @author	Patrick Matusz
 * @author	Markus Barchfeld <Markus.Barchfeld@gmx.de>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('pm_tour').'pi1/class.tx_pmtour_googleMapAPI_PMPro.php'); 
require_once(t3lib_extMgm::extPath('pm_tour').'pi1/class.tx_pmtour_gpxParser.php'); 
require_once(t3lib_extMgm::extPath('pm_tour').'pi1/class.tx_pmtour_googleChart.php');
require_once(t3lib_extMgm::extPath('pm_tour').'pi1/class.tx_pmtour_altitude.php');
require_once(t3lib_extMgm::extPath('pm_tour').'pi1/class.tx_pmtour_geotagging.php');

class tx_pmtour_pi1 extends tslib_pibase {
	var $prefixId = 'tx_pmtour_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_pmtour_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'pm_tour';	// The extension key.
	var $gmap = null;
	
	/**
	 * [Put your description here]
	 */
	function main($content,$conf)	{
	

		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
				
		$this->initTemplate();
		
		
		if ($this->piVars['pm_tour'] != "") {			
			// use intval to avoid SQL-Injection, results in empty page if manipulated
			$content = $this->singleView(intval($this->piVars['pm_tour']));						
			return  $content;			
		} else
		{
			$markerArray = array();
			$subpartArray = array();
			$wrappedSubpartArray = array();
			$subpartArray['###COUNTRY###'] = $this->getCountries();
			$listType = $this->conf["listType"];
			if($listType==1)
				$templateItem="countriesselect";
			elseif($listType==2)
				$templateItem="countrieslist";
			$content = $this->cObj->substituteMarkerArrayCached($this->templateItems[$templateItem], $markerArray, $subpartArray, $wrappedSubpartArray);
		}

		return $content;
	}
	
	/*
	 * param: $uid as integer
	 */
	function singleView($uid) {
		$this->initMap();

		$selectFields = "*";
		$tables = "tx_pmtour_tour";
		$where = "uid=".$uid." ".$this->cObj->enableFields("tx_pmtour_tour");	
		$order = $this->conf["list."]["tour_sort"];   
		$tours = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($selectFields, $tables, $where, null, $order);
		foreach ($tours as $tour=>$val) {
			$this->gmap->setTitle($val["name"]);
		
			$markerArray = array();
			$subpartArray = array();
			$wrappedSubpartArray = array();
			$markerArray['###TOURNAME###'] = $this->cObj->stdWrap($val["name"], $this->conf["singleView."]["tourname_stdWrap."]);
			$markerArray['###GPXFILE###'] = $this->cObj->stdWrap($this->cObj->filelink($val["gpxfile"], $this->conf["singleView."]["gpxfile."]),$this->conf["singleView."]["gpxfile_stdWrap."]);
			$markerArray['###TOURDESCRIPTION###'] = $this->cObj->stdWrap($val["description"], $this->conf["singleView."]["tourdescription_stdWrap."]);
			$markerArray['###TOURNUMBER###'] = $this->cObj->stdWrap($val["number"], $this->conf["singleView."]["tournumber_stdWrap."]);
			$markerArray['###TOURLENGTH###'] = $this->cObj->stdWrap($val["length_km"], $this->conf["singleView."]["tourlength_stdWrap."]);
			$markerArray['###TOURDURATION###'] = $this->cObj->stdWrap($val["duration_h"], $this->conf["singleView."]["tourduration_stdWrap."]);
			
			$this->imageNames = t3lib_div::trimExplode(",", $val["images"], 1);		
			reset($this->imageNames);
			$this->imageTags = array();			
			$this->imageCaptions = explode(chr(10), $val["imagecaptions"]);
			while (list($imgkey, $img) = each($this->imageNames)) {
				$l = $this->conf["singleView."]["tourimage_stdWrap."];				
				$l["file"] = $this->conf["singleView."]["tourimage_stdWrap."]["path"].$img;
				$imgTitle = $this->imageCaptions[$imgkey];
				if ($imgTitle != null) {
					$l['titleText'] = $imgTitle;
				}
				if ($l["stdWrap."]["typolink."] != null) {
				  $l["stdWrap."]["typolink."]["parameter."]["cObject."]["file"] = $l["file"];
				  if ($imgTitle != null) {
				  	$l["stdWrap."]["typolink."]["title"] = $imgTitle;
				  }
				}
				
				if($this->conf["singleView."]["geotagging"] == 1 && function_exists('exif_read_data')){
					$this->geotag = new tx_pmtour_geotagging($l, $this->cObj, $this->conf, $this->templateItems);
					if(is_numeric($this->geotag->output["Lat"]))
						$this->gmap->addMarker(floatval($this->geotag->output["Lat"]),floatval($this->geotag->output["Lon"]),$this->geotag->output["Title"],$this->geotag->output["Hover"],$this->geotag->output["Html"],$this->geotag->output["Ico_n"],$this->geotag->output["Ico_s"]);
				}
				if(!function_exists('exif_read_data'))
					echo "Function exif_read_data() not available";
					
				array_push($this->imageTags, $this->cObj->IMAGE($l));	
			}			
			$imageHtml = implode("",$this->imageTags);
			if (strlen($imageHtml)>0) {
				$markerArray['###TOURIMAGES###'] = $this->cObj->stdWrap($imageHtml, $this->conf["singleView."]["tourimages_stdWrap."]);
			} else {
				$markerArray['###TOURIMAGES###'] = "";
			}			
			if ($this->conf["googleMap."]["visible"] == "1") { 
				$markerArray['###GOOGLEMAP###'] = $this->cObj->stdWrap($this->drawMap($uid,$this->conf["singleView."]["gpxfile."]["path"].$val["gpxfile"],$val), $this->conf["singleView."]["googlemap_stdWrap."]);
			} else {
				$markerArray['###GOOGLEMAP###'] = "";
			}			
			if ($val["showaltitudeprofile"]) {
				// TODO: make sure gpx exists (can not if googleMap.visible is 0)
				$altitude = new tx_pmtour_altitude($this->gpx) ;
				$googleChart = new tx_pmtour_googleChart($altitude->getDistances(), $altitude->getElevations()) ;
				$subMarkerArray= array() ;
				$subMarkerArray['###ALTITUDEPROFILEIMG###']="<IMG SRC=\"".$googleChart->getUrl()."\"></IMG>" ;
				$subpartArray['###ALTITUDEPROFILESUBPART###']=$this->cObj->substituteMarkerArrayCached($this->templateItems["altitudeprofile"],$subMarkerArray);
			} else {
				$subpartArray['###ALTITUDEPROFILESUBPART###']="";
			}
			
			$content = $this->cObj->substituteMarkerArrayCached($this->templateItems["singleview"],$markerArray,$subpartArray) ;//,$wrappedSubpartArray);
		}
		
		return $content;		
	}	
	
	function initMap() {
		$this->gmap = new tx_pmtour_googleMapAPI_PMPro($this->conf["googleMap."]["key"],$this->conf["googleMap."]["mapType"], $this->conf["googleMap."]["layerIds"], $this->conf["googleMap."]["layerNames"]);
		$this->gmap->setWidth($this->conf["googleMap."]["width"]);
		$this->gmap->setHeight($this->conf["googleMap."]["height"]);
	}
	
	function drawMap($uid, $gpxfile, $tour) {
		
		$this->gpx = new tx_pmtour_gpxParser($this->conf["gpxEncoding"]);
		$this->gpx->parse($gpxfile);	

		
		//Zeichne Routen
		if ($tour["displaytype"] == 0) {
			$rte =0;
			while (!is_null($this->gpx->output["rte".$rte])) {
				$rtecolor = $this->conf["singleView."]["googlemap."]["routeColor"];
				$rteweight = $this->conf["singleView."]["googlemap."]["routeWeight"];
				$rteopacity = $this->conf["singleView."]["googlemap."]["routeOpacity"];
				if (strlen($this->conf["singleView."]["googlemap."]["routeColor".$rte])>0) {
					$rtecolor = $this->conf["singleView."]["googlemap."]["routeColor".$rte];
				}
				if (strlen($this->conf["singleView."]["googlemap."]["routeWeight".$rte])>0) {
					$rteweight = $this->conf["singleView."]["googlemap."]["routeWeight".$rte];
				}
				if (strlen($this->conf["singleView."]["googlemap."]["routeOpacity".$rte])>0) {
					$rteopacity = $this->conf["singleView."]["googlemap."]["routeOpacity".$rte];
				}
	
				$i = 0;			
				$rtepts = $this->gpx->output["rte".$rte];
				$gmaplineid = $this->gmap->addPolyline($rtecolor,$rteweight,$rteopacity);
				
				while (is_numeric($rtepts["rtept".$i]["LON"])) {
					$this->gmap->addPolylinePoint($gmaplineid,floatval($rtepts["rtept".$i]["LAT"]), floatval($rtepts["rtept".$i]["LON"]));
					$i++;
				}
				$rte++;
			}
		}
		
		//Tracks
		if ($tour["displaytype"] == 1) {
			$trk =0;
			while (!is_null($this->gpx->output["trk".$trk])) {									
				$trkcolor = $this->conf["singleView."]["googlemap."]["trackColor"];
				$trkweight = $this->conf["singleView."]["googlemap."]["trackWeight"];
				$trkopacity = $this->conf["singleView."]["googlemap."]["trackOpacity"];
				if (strlen($this->conf["singleView."]["googlemap."]["trackColor".$trk])>0) {
					$trkcolor = $this->conf["singleView."]["googlemap."]["trackColor".$trk];
				}
				if (strlen($this->conf["singleView."]["googlemap."]["trackWeight".$trk])>0) {
					$trkweight = $this->conf["singleView."]["googlemap."]["trackWeight".$trk];
				}
				if (strlen($this->conf["singleView."]["googlemap."]["trackOpacity".$trk])>0) {
					$trkopacity = $this->conf["singleView."]["googlemap."]["trackOpacity".$trk];
				}
	
				$i = 0;			
				$trkpts = $this->gpx->output["trk".$trk];
				$gmaplineid = $this->gmap->addPolyline($trkcolor,$trkweight,$trkopacity);

				while (is_numeric($trkpts["trkpt".$i]["LON"])) {
					$this->gmap->addPolylinePoint($gmaplineid,floatval($trkpts["trkpt".$i]["LAT"]), floatval($trkpts["trkpt".$i]["LON"]));
					$i++;
				}
				$trk++;
			}			
		}
		
		//Waypoints		
		$i = 0;			
		$symbolIcons = $this->conf["singleView."]["googlemap_waypoints."];
		reset($symbolIcons);

		$baseURL = "";
		if(!empty($GLOBALS['TSFE']->tmpl->setup['config.']['baseURL']))
			$baseURL = $GLOBALS['TSFE']->tmpl->setup['config.']['baseURL'];

		while (is_numeric($this->gpx->output["wpt".$i]["LON"])) {
			$wpt = $this->gpx->output["wpt".$i++];
			$symbol = strtolower(str_replace(array(" ",","),"",$wpt["SYM"]));

			if(array_key_exists($symbol,$symbolIcons) && file_exists($GLOBALS['TSFE']->tmpl->getFileName($symbolIcons[$symbol]))){
				$image = $baseURL.$GLOBALS['TSFE']->tmpl->getFileName($symbolIcons[$symbol]);
			}
			else
				$image = null;
				
			$html = $this->createMarkerPopupHtml($wpt,$image);
			$hover = $html == null ? $wpt["NAME"] : $this->cObj->stdWrap($wpt["NAME"], $this->conf["marker."]["hoverPopupAvailable_stdWrap."]) ;
			$this->gmap->addMarker(floatval($wpt["LAT"]),$wpt["LON"],$wpt["NAME"],$hover, $html, $image,null);
		
		}


		//Markers Database with Images
		$selectFields = "*";
		$tables = "tx_pmtour_tourpoints";
		$where = "tour=".$uid." ".$this->cObj->enableFields("tx_pmtour_tourpoints");	
		$tourpts = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($selectFields, $tables, $where, null, $order);
		foreach ($tourpts as $pt=>$val) {
			$ico_n="";
			$ico_s="";
			if ($val["icon"]>0) {
				$selectFields = "*";
				$tables = "tx_pmtour_icons";
				$where = "uid=".$val["icon"]." ".$this->cObj->enableFields("tx_pmtour_icons");	
				$icons = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($selectFields, $tables, $where, null, $order);
				foreach ($icons as $i=>$icon) {					 
					if (strlen($icon["icon"])>0) {
						$ico_n=$baseURL."uploads/tx_pmtour/".$icon["icon"];
					}
					if (strlen($icon["shadowicon"])>0) {
						$ico_s="uploads/tx_pmtour/".$icon["shadowicon"];
					}
				}
			} else {
				$ico_n = $GLOBALS['TSFE']->tmpl->getFileName($this->conf["singleView."]["googlemap."]["defaultDatabaseMarkerIcon"]);
			}
			$markerArray = array();
			$subpartArray = array();
			$wrappedSubpartArray = array();
			$markerArray['###TITLE###'] = $this->cObj->stdWrap($val["name"], $this->conf["marker."]["title_stdWrap."]);
			if (strlen($val["elevation"])==0) {
				$markerArray['###ELEVATION###'] = "";
			} else {
				$markerArray['###ELEVATION###'] = $this->cObj->stdWrap($val["elevation"], $this->conf["marker."]["elevation_stdWrap."]);
			}
			if (strlen($val["description"])==0) {
				$markerArray['###DESCRIPTION###'] = "";
			} else {
				$markerArray['###DESCRIPTION###'] = $this->cObj->stdWrap($val["description"], $this->conf["marker."]["description_stdWrap."]);
			}
			if (strlen($val["url"])==0) {
				$markerArray['###URL###'] = "";	
			} else {
				$url=t3lib_div::trimExplode(" ", $val["url"], 1);		
				reset($url);
				$markerArray['###URL###'] = $this->cObj->stdWrap("<LINK ".$val["url"].">".$url[0]."</LINK>", $this->conf["marker."]["url_stdWrap."]);			
			}
			$imgs = t3lib_div::trimExplode(",", $val["images"], 1);		
			reset($imgs);
			$theImgCode = "";			
			while (list(, $img) = each($imgs)) {
				$l = $this->conf["marker."]["image_stdWrap."];
				$l["file"] = $this->conf["marker."]["image_stdWrap."]["path"].$img;
				$theImgCode .= $this->cObj->IMAGE($l);	
			}			
			if (strlen($theImgCode)>0) {
				$markerArray['###IMAGES###'] = $this->cObj->stdWrap($theImgCode, $this->conf["marker."]["images_stdWrap."]);
			} else {
				$markerArray['###IMAGES###'] = "";
			}			
			$html = $this->cObj->substituteMarkerArrayCached($this->templateItems["marker"],$markerArray,$subpartArray,$wrappedSubpartArray);
			$html = str_replace("\n","",$html);
			$html = str_replace("\r","",$html);
			$this->gmap->addMarker(floatval($val["latitude"]),floatval($val["longitude"]),$icon["name"],$icon["name"],$html,$ico_n,$ico_s);
		}

		$GLOBALS['TSFE']->additionalHeaderData[$extKey."1"] = $this->gmap->getHeaderScript();
		$GLOBALS['TSFE']->config['config']['doctype'] = "xhtml_strict";
		
		$content = $this->gmap->getContentElement();

		return $content;
	}
	
	function createMarkerPopupHtml($wpt, $image) {
		$markerArray = array();
		$desc = $wpt["DESC"]; 
		$url = $wpt["LINK"];
		$elevation = $wpt["ELE"];
		// look for images whose caption include the name of the waypoint
		$popupImageTags = '';
		$imagesAdded = 0;
		if ($wpt["NAME"] != null) {
			reset($this->imageCaptions);
			while (list($imageKey, $imageCaption) = each($this->imageCaptions)) {
				$pos = stripos($imageCaption, $wpt["NAME"]);
				if ($pos !== false) {
					$l = $this->conf["marker."]["image_stdWrap."];				
					$l["file"] = $this->conf["marker."]["image_stdWrap."]["path"].$this->imageNames[$imageKey];
					$l['titleText'] = $l;
					$popupImageTags .= $this->cObj->IMAGE($l);
					$imagesAdded += 1;
				}
				if ($imagesAdded == $this->conf["marker."]["maxImages"]) {
					break;
				}
			}
		}
		if ($desc == null && $url == null && $elevation == null && strlen($popupImageTags)==0) {
			// do not provide a popup if it does not provide any new information
			// note that the title/name is already showed at mouse hover, so it is not considered here 
			return null;
		}
		$markerArray['###TITLE###'] = $this->cObj->stdWrap($wpt["NAME"], $this->conf["marker."]["title_stdWrap."]);
		
		if (strlen($url) == 0) {
			$markerArray['###URL###'] = "";	
		} else {
			$url = '<a href="'.$url.'" target="_blank">'.$url.'</a>';
			$markerArray['###URL###'] = $this->cObj->stdWrap($url, $this->conf["marker."]["url_stdWrap."]);	
		}

		if (strlen($desc)==0) {
			$markerArray['###DESCRIPTION###'] = "";
		} else {
			$markerArray['###DESCRIPTION###'] = $this->cObj->stdWrap($desc, $this->conf["marker."]["description_stdWrap."]);
		}
		
		if (strlen($popupImageTags) > 0) {
			$markerArray['###IMAGES###'] = $this->cObj->stdWrap($popupImageTags, $this->conf["marker."]["images_stdWrap."]);
		} else {
			$markerArray['###IMAGES###'] = '';			
		}
		
		if ($elevation == null) {
			$markerArray['###ELEVATION###'] = "";
		} else {
			$formattedElevation = number_format($elevation, 0, '', '');
			$markerArray['###ELEVATION###'] = $this->cObj->stdWrap($formattedElevation, $this->conf["marker."]["elevation_stdWrap."]);
		}
		$subpartArray = array();
		$wrappedSubpartArray = array();
		$html = $this->cObj->substituteMarkerArrayCached($this->templateItems["marker"],$markerArray,$subpartArray,$wrappedSubpartArray);
		$html = str_replace("\n","",$html);
		$html = str_replace("\r","",$html);
		return $html;
	}
	

	function getCountries() {
		$pidList = $this->pi_getPidList($this->conf['pidList'],$this->conf['recursive']);
		$selectFields = "uid, countryname";
		$tables = "tx_pmtour_countries";
		$listType = $this->conf["listType"];
		if($listType==1)
			$templateItem="select";
		elseif($listType==2)
			$templateItem="list";
		$where = "pid IN (".$this->cObj->data['pages'].") ".$this->cObj->enableFields("tx_pmtour_countries");
		$order = $this->conf["list."]["country_sort"];
		$countries = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($selectFields, $tables, $where, null, $order);
		foreach ($countries as $country=>$val) {
			$markerArray = array();
			$subpartArray = array();
			$wrappedSubpartArray = array();
			$markerArray['###COUNTRYNAME###'] = $this->cObj->stdWrap($val["countryname"], $this->conf["list."]["country_stdWrap."]);
			$subpartArray['###REGION###'] = $this->getRegions($val["uid"],$templateItem);
			$content .= $this->cObj->substituteMarkerArrayCached($this->templateItems[$templateItem."_country"],$markerArray,$subpartArray,$wrappedSubpartArray);
		}
		return $this->cObj->stdWrap($content, $this->conf["list."]["countryList_stdWrap."]);
	}

	function getRegions($country,$templateItem) {
		$selectFields = "uid, regionname";
		$tables = "tx_pmtour_regions";
		$listLevel=$this->conf["listLevel"];

		$where = "country=".$country." ".$this->cObj->enableFields("tx_pmtour_regions");	
		$order = $this->conf["list."]["region_sort"];    
		$regions = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($selectFields, $tables, $where, null, $order);
		$regionlist=array();
		foreach ($regions as $region=>$val) {
			array_push($regionlist, $val["uid"]);
			if($listLevel==2){
				// deep level => show tours per region
				$markerArray = array();
				$subpartArray = array();
				$wrappedSubpartArray = array();
				$markerArray['###REGIONNAME###'] = $this->cObj->stdWrap($val["regionname"], $this->conf["list."]["region_stdWrap."]);
				$subpartArray['###TOUR###'] = $this->getToursForRegions($regionlist,$templateItem);
				$content .= $this->cObj->substituteMarkerArrayCached($this->templateItems[$templateItem."_region"],$markerArray,$subpartArray,$wrappedSubpartArray);
				$regionlist = array();
			}
		}
		
		if($listLevel==1){
			// shallow level => show tours per country
			$markerArray = array();
			$subpartArray = array();
			$wrappedSubpartArray = array();
			$markerArray['###REGIONNAME###'] = "";
			$subpartArray['###TOUR###'] = $this->getToursForRegions($regionlist,$templateItem);
			$content .= $this->cObj->substituteMarkerArrayCached($this->templateItems[$templateItem."_region"],$markerArray,$subpartArray,$wrappedSubpartArray);
		}

		return $this->cObj->stdWrap($content, $this->conf["list."]["regionList_stdWrap."]);
	}

	function getToursForRegions($regionlist,$templateItem) {
		$subpartArray['###TOUR###'] = $this;
		$selectTourFields = "uid, number, name, length_km, duration_h";
		$tourTables = "tx_pmtour_tour";
		$orderTour = $this->conf["list."]["tour_sort"];
		$prefix=array("select"=>array(" - "," ; "),"list"=>array("",""));

		$regions = implode(",",$regionlist);
		$whereTour = "region IN (".$regions.") ".$this->cObj->enableFields("tx_pmtour_tour");
		$tours = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($selectTourFields, $tourTables, $whereTour, null, $orderTour);

		foreach ($tours as $tour=>$val) {
			$markerArray = array();
			$subpartArray = array();
			$wrappedSubpartArray = array();
			$markerArray['###TOURNAME###'] = $this->cObj->stdWrap($val["name"], $this->conf["list."]["tourname_stdWrap."]);
			$markerArray['###TOURNUMBER###'] = $this->cObj->stdWrap($val["number"], $this->conf["list."]["tournumber_stdWrap."]);
			$markerArray['###TOURLENGTH###'] = $this->cObj->stdWrap(strlen($val["length_km"])?$prefix[$templateItem][0].$val["length_km"]:"", $this->conf["list."]["tourlength_stdWrap."]);
			$markerArray['###TOURDURATION###'] = $this->cObj->stdWrap(strlen($val["duration_h"])?$prefix[$templateItem][1].$val["duration_h"]:"", $this->conf["list."]["tourduration_stdWrap."]);
			$markerArray['###VALUE###'] = $this->pi_linkTP_keepPIvars_url(array('pm_tour' => $val["uid"]), 0, 0, '');
			$wrappedSubpartArray['###LINK_ITEM###'] = explode('|', $this->pi_linkTP_keepPIvars('|', array('pm_tour' => $val["uid"]), $this->allowCaching, 0, null));
			$content .= $this->cObj->stdWrap($this->cObj->substituteMarkerArrayCached($this->templateItems[$templateItem."_tour"],$markerArray,$subpartArray,$wrappedSubpartArray),$this->conf["list."]["tour_stdWrap."]);
		}
		
		return $this->cObj->stdWrap($content,$this->conf["list."]["tourList_stdWrap."]);
	}

	function initTemplate() {
		$listType = $this->conf["listType"];
		$this->templateItems["all"] = $this->cObj->fileResource($this->conf['templateFile']);
		if($listType==2){
			$this->templateItems["countrieslist"] = $this->cObj->getSubpart($this->templateItems["all"],"###COUNTRIESLIST###");		
			$this->templateItems["list_country"] = $this->cObj->getSubpart($this->templateItems["countrieslist"],"###COUNTRY###");
			$this->templateItems["list_region"] = $this->cObj->getSubpart($this->templateItems["countrieslist"],"###REGION###");
			$this->templateItems["list_tour"] = $this->cObj->getSubpart($this->templateItems["countrieslist"],"###TOUR###");
		} elseif ($listType==1){
			$this->templateItems["countriesselect"] = $this->cObj->getSubpart($this->templateItems["all"],"###COUNTRIESSELECT###");		
			$this->templateItems["select_country"] = $this->cObj->getSubpart($this->templateItems["countriesselect"],"###COUNTRY###");
			$this->templateItems["select_region"] = $this->cObj->getSubpart($this->templateItems["countriesselect"],"###REGION###");
			$this->templateItems["select_tour"] = $this->cObj->getSubpart($this->templateItems["countriesselect"],"###TOUR###");
		}
		$this->templateItems["singleview"] = $this->cObj->getSubpart($this->templateItems["all"],"###SINGLEVIEW###"); 
		$this->templateItems["altitudeprofile"] = $this->cObj->getSubpart($this->templateItems["singleview"],"###ALTITUDEPROFILE###"); 
		$this->templateItems["marker"] = $this->cObj->getSubpart($this->templateItems["all"],"###MARKER###");
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pm_tour/pi1/class.tx_pmtour_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pm_tour/pi1/class.tx_pmtour_pi1.php']);
}

