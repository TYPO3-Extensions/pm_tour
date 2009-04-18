<?php
/***************************************************************
*  Copyright notice
*
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
 * Altitude calculation for the 'pm_tour' extension.
 *
 * @author	Markus Barchfeld <Markus.Barchfeld@gmx.de>
 */
class tx_pmtour_altitude {
    var $overallDistance = 0;
    var $distances;
    var $elevations;
    
	function tx_pmtour_altitude(&$gpx) {
		$trkpts = $gpx->output["trk0"];
		$this->distances= array() ;
		$this->elevations= array() ;
		$this->distances[0]=0;
		// TODO: check if there is at least one trackpoint
		$this->elevations[0]= floatval($trkpts["trkpt0"]["ELE"]);
		$i = 1;
		while (is_numeric($trkpts["trkpt".$i]["LON"])) {
			$dist = $this->distance($trkpts["trkpt".($i-1)], $trkpts["trkpt".$i]);
			$this->distances[$i] = $this->distances[$i-1] + $dist; 
			$this->elevations[$i] = floatval($trkpts["trkpt".$i]["ELE"]) ; 
			$i++;
		}
		$this->overallDistance=$this->distances[$i-1] ;
	}
	
	function distance($trkpt1, $trkpt2) {
		$lat1=deg2rad(floatval($trkpt1["LAT"]));
		$lon1=deg2rad(floatval($trkpt1["LON"]));
		$lat2=deg2rad(floatval($trkpt2["LAT"]));
		$lon2=deg2rad(floatval($trkpt2["LON"]));
	
		// 6371.0 is earth radius (km)
		return acos( (sin($lat1) * sin($lat2)) + (cos($lat1) * cos($lat2) * cos($lon1 - $lon2))) * 6371.0;
	}
	
	function getElevation() {
		$elevation=0;
		for ($i=1; $i<count($this->elevations);$i++) {
			if ($this->elevations[$i] > $this->elevations[$i-1]) {
    			$elevation += $this->elevations[$i] - $this->elevations[$i-1] ;		
			}
		}
		return round($elevation);
	}

	function getDistances() {
		return $this->distances ;
	}
	
	function getElevations() {
		return $this->elevations ;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pm_tour/pi1/class.tx_pmtour_altitude.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pm_tour/pi1/class.tx_pmtour_altitude.php']);
}
?>