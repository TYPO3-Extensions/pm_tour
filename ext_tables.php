<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::allowTableOnStandardPages("tx_pmtour_regions");


t3lib_extMgm::addToInsertRecords("tx_pmtour_regions");

$TCA["tx_pmtour_regions"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_regions",		
		"label" => "regionname",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"languageField" => "sys_language_uid",	
		"transOrigPointerField" => "l18n_parent",	
		"transOrigDiffSourceField" => "l18n_diffsource",	
		"sortby" => "sorting",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_pmtour_regions.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, regionname, country",
	)
);


t3lib_extMgm::allowTableOnStandardPages("tx_pmtour_countries");


t3lib_extMgm::addToInsertRecords("tx_pmtour_countries");

$TCA["tx_pmtour_countries"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_countries",		
		"label" => "countryname",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"languageField" => "sys_language_uid",	
		"transOrigPointerField" => "l18n_parent",	
		"transOrigDiffSourceField" => "l18n_diffsource",	
		"sortby" => "sorting",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_pmtour_countries.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, countryname",
	)
);


t3lib_extMgm::allowTableOnStandardPages("tx_pmtour_tour");


t3lib_extMgm::addToInsertRecords("tx_pmtour_tour");

$TCA["tx_pmtour_tour"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_tour",		
		"label" => "name",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"languageField" => "sys_language_uid",	
		"transOrigPointerField" => "l18n_parent",	
		"transOrigDiffSourceField" => "l18n_diffsource",	
		"sortby" => "sorting",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",	
			"starttime" => "starttime",	
			"endtime" => "endtime",	
			"fe_group" => "fe_group",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_pmtour_tour.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, starttime, endtime, fe_group, name, description, number, length_km, duration_h, images, imagecaptions, gpxfile, displaytype, region, showfilter, showaltitudeprofile",
	)
);


t3lib_extMgm::allowTableOnStandardPages("tx_pmtour_tourpoints");


t3lib_extMgm::addToInsertRecords("tx_pmtour_tourpoints");

$TCA["tx_pmtour_tourpoints"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_tourpoints",		
		"label" => "name",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"languageField" => "sys_language_uid",	
		"transOrigPointerField" => "l18n_parent",	
		"transOrigDiffSourceField" => "l18n_diffsource",	
		"sortby" => "sorting",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_pmtour_tourpoints.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, name, latitude, longitude, description, images, url, tour, icon",
	)
);

t3lib_extMgm::allowTableOnStandardPages("tx_pmtour_icons");


t3lib_extMgm::addToInsertRecords("tx_pmtour_icons");

$TCA["tx_pmtour_icons"] = Array (
    "ctrl" => Array (
        "title" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_icons",        
        "label" => "name",    
        "tstamp" => "tstamp",
        "crdate" => "crdate",
        "cruser_id" => "cruser_id",
        "default_sortby" => "ORDER BY name",    
        "delete" => "deleted",    
        "enablecolumns" => Array (        
            "disabled" => "hidden",
        ),
        "dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
        "iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_pmtour_icons.gif",
    ),
    "feInterface" => Array (
        "fe_admin_fieldList" => "hidden, name, icon, shadowicon",
    )
);


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';


t3lib_extMgm::addPlugin(Array('LLL:EXT:pm_tour/locallang_db.php:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,'pi1/static/','Tour');


if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_pmtour_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_pmtour_pi1_wizicon.php';
?>