<?php
class tx_pmtour_gpxParser{
   var $xml_obj = null;
   var $output = array();
   var $attrs;
   var $rtept=0;
   var $trkpt=0;
   var $wpt=0;
   var $rte=-1;
   var $trkseg=-1;
   var $currentwpt=-1;
   var $currentdata=null;
   var $readingdata=0;
   
   function tx_pmtour_gpxParser($encoding=null){
   	   $this->encoding = $encoding;
       $this->xml_obj = xml_parser_create();
       xml_set_object($this->xml_obj,$this);
       xml_set_character_data_handler($this->xml_obj, 'dataHandler');
       xml_set_element_handler($this->xml_obj, "startHandler", "endHandler");
   }

   function parse($path){
   	   $this->rte=-1;	
   	   $this->trkseg=-1;
   	   $this->wpt=0;
       if (!($fp = fopen($path, "r"))) {
           die("Cannot open XML data file: $path");
           return false;
       }
	   $filecontent = "";
       while ($data = fread($fp, 4096)) {
       		$filecontent .= $data;
       }
       if (!xml_parse($this->xml_obj, $filecontent, feof($fp))) {
           die(sprintf("XML error: %s at line %d",
           xml_error_string(xml_get_error_code($this->xml_obj)),
           xml_get_current_line_number($this->xml_obj)));
           xml_parser_free($this->xml_obj);
       }

       $this->output["wpt_count"] = $this->wpt;
       $this->output["rte_count"] = $this->rte+1;
       $this->output["trk_count"] = $this->trkseg+1;
       return true;
   }

   function startHandler($parser, $name, $attribs){
   		switch ($name) {
   			case "RTE":
   				$this->rte++;
   				$this->rtept = 0;
   				break;
   			case "RTEPT":
   				$this->output["rte".$this->rte]["rtept".$this->rtept] = $attribs;
   				$this->rtept++;
   				$this->output["rte".$this->rte]["rtept_count"] = $this->rtept;
   				break;
   			case "TRKSEG":
   				$this->trkseg++;
   				$this->trkpt = 0;
   				break;
   			case "TRKPT":
   				$this->output["trk".$this->trkseg]["trkpt".$this->trkpt] = $attribs;   				
   				$this->trkpt++;
   				$this->output["trk".$this->trkseg]["trkpt_count"] = $this->trkpt;
   				break;
   			case "ELE":
      			if ($this->trkseg >= 0 || $this->currentwpt >= 0) {	
					$this->currentdata=null;
					$this->readingdata=1;
      			}
   				break;
   			case "WPT":
				$this->output["wpt".$this->wpt] = $attribs;
				$this->currentwpt = $this->wpt;
				$this->wpt++;
				break;
			case "SYM":
				if ($this->currentwpt>=0) {
					$this->currentdata=null;
					$this->readingdata=1;
				}
				break;
			case "NAME":
				if ($this->currentwpt>=0) {
					$this->currentdata=null;
					$this->readingdata=1;
				}
				break;			
			case "DESC":
				if ($this->currentwpt>=0) {
					$this->currentdata=null;
					$this->readingdata=1;
				}
				break;				
			case "LINK":
				if ($this->currentwpt>=0) {
					$this->output["wpt".$this->currentwpt]["LINK"]=$attribs["HREF"];
				}
				break;						
			case "BOUNDS":
				$this->output["bounds"] = $attribs;
				break;				
			default:
				//echo $name."<br>";
				break;
   		}
   }

   function dataHandler($parser, $data){
   	// if encoding is utf-8 and data contains Umlaute, the data is split into several chunks
   	if ($this->readingdata) {
   		$this->currentdata.=$data;   		
   	}
   }

   function endHandler($parser, $name){
   	  $this->readingdata=0;
      switch ($name) {
      	case "WPT":
      		$this->currentwpt = -1;
      	   break;
      	case "SYM":
			if ($this->currentwpt>=0) {
				$this->output["wpt".$this->currentwpt]["SYM"] = $this->convertedData();
      		}
      		break;
      	case "NAME":
			if ($this->currentwpt>=0) {
				$this->output["wpt".$this->currentwpt]["NAME"] = $this->convertedData();
      		}
      		break;
      	case "DESC":
			if ($this->currentwpt>=0) {
				$this->output["wpt".$this->currentwpt]["DESC"] = $this->convertedData();
      		}
      		break;      	
      	case "ELE":
      		if ($this->currentwpt>=0) {
      			$this->output["wpt".$this->currentwpt]["ELE"] = $this->currentdata;
      		} else if ($this->trkpt >= 0) {
      			$currenttrkptidx = $this->trkpt - 1 ;
      			$currenttrkpt= &$this->output["trk".$this->trkseg]["trkpt".$currenttrkptidx] ;
      			$currenttrkpt["ELE"]= $this->currentdata ;
      		} 
      		break;      		
      	default:
      	   break;
      }
   }
   
   function convertedData() {
		if ($this->encoding == null) {
   			return $this->currentdata;
   		} else {
   			return mb_convert_encoding($this->currentdata, "auto", $this->encoding);
   		}
   }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pm_tour/pi1/class.tx_pmtour_gpxParser.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pm_tour/pi1/class.tx_pmtour_gpxParser.php']);
}

?>