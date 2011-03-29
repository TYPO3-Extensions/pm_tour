<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Daniel Nicke (d.nicke@gmx.de)
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
 * Geotagging for the 'pm_tour' extension.
 *
 * @author	Daniel Nicke <d.nicke@gmx.de>
 */
 
class tx_pmtour_geotagging {

	var $conf = null;
	var $cObj = null;
	var $output = null;
	var $templateItems = null;
	var $image = null;

	function tx_pmtour_geotagging($image, &$cObj, $conf, $templateItems){
		$this->image = $image;
		$this->cObj = $cObj;
		$this->conf = $conf;
		$this->templateItems = $templateItems;
		$exif_data = exif_read_data($image["file"] );

		if(array_key_exists("GPSLatitudeRef",$exif_data)){
			$LatitudeRef = 1;
			if($exif_data[GPSLatitudeRef] == "S")
				$LatitudeRef = (-1);
			$LongitudeRef = 1;
			if($exif_data[GPSLongitudeRef] == "W")
				$LongitudeRef = (-1);
			list($deg,$dec) = explode("/",$exif_data[GPSLongitude][0]);
			$LonDeg = sprintf("%2.9f",$deg/$dec);
			list($min,$dec) = explode("/",$exif_data[GPSLongitude][1]);
			$LonMin = sprintf("%2.9f",$min/$dec);
			list($sec,$dec) = explode("/",$exif_data[GPSLongitude][2]);
			$LonSecs = sprintf("%2.9f",$sec/$dec);
			list($deg,$dec) = explode("/",$exif_data[GPSLatitude][0]);
			$LatDeg = sprintf("%2.9f",$deg/$dec);
			list($min,$dec) = explode("/",$exif_data[GPSLatitude][1]);
			$LatMin = sprintf("%2.9f",$min/$dec);
			list($sec,$dec) = explode("/",$exif_data[GPSLatitude][2]);		
			$LatSecs = sprintf("%2.9f",$sec/$dec);
			list($alt,$dec) = explode("/",$exif_data[GPSAltitude]);
			$Altitude = sprintf("%4.0f",$alt/$dec);
		
			$Longitude = $LongitudeRef*((($LonSecs/60)+$LonMin)/60+$LonDeg);
			$Latitude = $LatitudeRef*((($LatSecs/60)+$LatMin)/60+$LatDeg);

			$img = $this->cObj->IMAGE($image);
			
			$ico_s = "";
			if($conf["singleView."]["useIcon"])
				$ico_n = $GLOBALS['TSFE']->tmpl->getFileName("EXT:pm_tour/pi1/res/image.png");
			else
				$ico_n = $this->imageResize($image["file"],$exif_data[FileName],14);
			
			$markerArray = array();
			$subpartArray = array();
			$wrappedSubpartArray = array();
			$text = explode("|",$image["titleText"]);
			$markerArray['###TITLE###'] = $this->cObj->stdWrap(trim($text[0]), $this->conf["marker."]["title_stdWrap."]);
			$markerArray['###IMAGES###'] = $this->cObj->stdWrap($img, $this->conf["marker."]["images_stdWrap."]);
			$markerArray['###DESCRIPTION###'] = trim($text[1]);
			$markerArray['###URL###'] = "";

			if($Altitude == null)
				$markerArray['###ELEVATION###'] = "keine Angabe";
			else
				$markerArray['###ELEVATION###'] = $Altitude;


			$delete = array("\n","\r");
			$html = $this->cObj->substituteMarkerArrayCached($this->templateItems["marker"],$markerArray,$subpartArray,$wrappedSubpartArray);
			$html = str_replace($delete,"",$html);

			$this->output["Lat"] = $Latitude;
			$this->output["Lon"] = $Longitude;
			$this->output["Title"] = str_replace($delete,"",$image["titleText"]);
			$this->output["Hover"] = str_replace($delete,"",$image["titleText"]);
			$this->output["Html"] = $html;
			$this->output["Ico_n"] = $ico_n;
			$this->output["Ico_s"] = $ico_s;
		}
	}
	
	function imageResize($img, $filename, $target) {
		list($width, $height, $type, $attr) = getimagesize($img);

		if ($width > $height)
			$percentage = ($target / $width);
		else
			$percentage = ($target / $height);
			
		$width_ico = round($width * $percentage);
		$height_ico = round($height * $percentage);

		if($type == 1)
			$org_img = imagecreatefromgif($img);

		if($type == 2)
			$org_img = ImageCreateFromJPEG($img);

		if($type == 3)
			$org_img = ImageCreateFromPNG($img);

		$ico = imagecreate($width_ico+2,$height_ico+2);
		ImageColorAllocate ($ico, 0, 0, 0);
		$ico_file = $this->image['path'].substr($filename,0,strrpos($filename,"."))."-ico.jpg";
		imagecopyresampled($ico,$org_img,1,1,0,0,$width_ico,$height_ico,$width,$height);
		ImageJPEg($ico,$ico_file);

		return $ico_file;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pm_tour/pi1/class.tx_pmtour_geotagging.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pm_tour/pi1/class.tx_pmtour_geotagging.php']);
}
?>
