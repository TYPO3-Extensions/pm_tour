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
 * Google Chart API stuff for the 'pm_tour' extension.
 *
 * @author	Markus Barchfeld <Markus.Barchfeld@gmx.de>
 */
class tx_pmtour_googleChart {
	var $yscale; //1, 100, 1000, ..
	var $nodes=50;
	var $ymin;
	var $ymax;
	var $xmax;
	var $xLabelPartitions=4;
    var $simpleEncoding = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789' ;
	var $valuesAtNodes;
    
	function tx_pmtour_googleChart(&$xvalues, &$yvalues) {
		$this->yscale=100; // TODO calculate
		$this->valuesAtNodes=tx_pmtour_googleChart::getValuesAtNodes($xvalues,$yvalues) ;
		$this->ymin=floor(min($this->valuesAtNodes)/$this->yscale)*$this->yscale;
		$this->ymax=ceil(max($this->valuesAtNodes)/$this->yscale)*$this->yscale;
		$this->ypartitions=($this->ymax-$this->ymin)/$this->yscale;
		$this->xmax=$xvalues[count($xvalues)-1];
	}
	
	function simpleEncodeValues(&$values, $min, $max) {
		$chartData= "" ;
		$distance= $max-$min;
		for ($l = 0; $l < count($values); $l++) {
			$value=round(($values[$l]-$min)/$distance*61) ;
			$chartData = $chartData . $this->simpleEncoding[$value] ;
		}		
		return $chartData ;		
	}
	
	
	function getValuesAtNodes(&$xvalues,&$yvalues) {
		$xmax= $xvalues[count($xvalues)-1];
		$xstep= $xmax / ($this->nodes - 1 ) ;
		$nextNode= 0;
		$valuesAtNodes = array();
		$k=0;
		for ($j = 0; $j < count($xvalues); $j++) {
			if ($xvalues[$j]>=$nextNode) {
	    		$valuesAtNodes[$k++] = $yvalues[$j];
				$nextNode += $xstep;
			}
		}
		return $valuesAtNodes;
	}

	function getYLabels() {
		$ylabels=array() ;
		for ($yentry=$this->ymin;$yentry <= $this->ymax;$yentry+=$this->yscale) {
  			$ylabels[]=$yentry;
		}
		return $ylabels;
	}
	
	function getXLabels() {
		$xlabels=array();
		$xstep=$this->xmax/$this->xLabelPartitions;
		for ($xentry=0;$xentry <= $this->xLabelPartitions;$xentry++) {
  			$xlabels[]=round($xentry*$xstep*10) / 10;
		}
		return $xlabels ;
	}
	
	function getUrl() {
		$chart = array() ;
		// Type
		$chart['cht']="lc" ;
		// line style
		$chart['chls']="3,0,0";
		// fill with horizontal stripes (90 degree)
		$stripeHeight=1/$this->ypartitions;
		$chart['chf']="c,ls,90,CCCCCC,".$stripeHeight.",FFFFFF,".$stripeHeight;
		// Labels
		$chart['chxt']="x,y";
		$chart['chxl']="0:|".join("|",$this->getXLabels())."|1:|".join("|",$this->getYLabels()) ;
		// Data
		$chart['chd']="s:".$this->simpleEncodeValues($this->valuesAtNodes, $this->ymin, $this->ymax);
		// Size
		$chart['chs']="600x200";
		$params=array() ;
		foreach ($chart as $key => $value){
	    	$params[] = $key."=".$value;
  		}
		return "http://chart.apis.google.com/chart?".join("&",$params);
	}
	
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pm_tour/pi1/class.tx_pmtour_googleChart.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pm_tour/pi1/class.tx_pmtour_googleChart.php']);
}
?>