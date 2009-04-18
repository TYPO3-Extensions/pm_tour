<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_pmtour_regions"] = Array (
	"ctrl" => $TCA["tx_pmtour_regions"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,regionname,country"
	),
	"feInterface" => $TCA["tx_pmtour_regions"]["feInterface"],
	"columns" => Array (
		'sys_language_uid' => Array (		
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.language',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => Array(
					Array('LLL:EXT:lang/locallang_general.php:LGL.allLanguages',-1),
					Array('LLL:EXT:lang/locallang_general.php:LGL.default_value',0)
				)
			)
		),
		'l18n_parent' => Array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('', 0),
				),
				'foreign_table' => 'tx_pmtour_regions',
				'foreign_table_where' => 'AND tx_pmtour_regions.pid=###CURRENT_PID### AND tx_pmtour_regions.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => Array (		
			'config' => Array (
				'type' => 'passthrough'
			)
		),
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"regionname" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_regions.regionname",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,trim",
			)
		),
		"country" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_regions.country",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_pmtour_countries",	
				"foreign_table_where" => "AND tx_pmtour_countries.pid=###CURRENT_PID### ORDER BY tx_pmtour_countries.uid",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,	
				"wizards" => Array(
					"_PADDING" => 2,
					"_VERTICAL" => 1,
					"add" => Array(
						"type" => "script",
						"title" => "Create new record",
						"icon" => "add.gif",
						"params" => Array(
							"table"=>"tx_pmtour_countries",
							"pid" => "###CURRENT_PID###",
							"setValue" => "prepend"
						),
						"script" => "wizard_add.php",
					),
					"list" => Array(
						"type" => "script",
						"title" => "List",
						"icon" => "list.gif",
						"params" => Array(
							"table"=>"tx_pmtour_countries",
							"pid" => "###CURRENT_PID###",
						),
						"script" => "wizard_list.php",
					),
					"edit" => Array(
						"type" => "popup",
						"title" => "Edit",
						"script" => "wizard_edit.php",
						"popup_onlyOpenIfSelected" => 1,
						"icon" => "edit2.gif",
						"JSopenParams" => "height=350,width=580,status=0,menubar=0,scrollbars=1",
					),
				),
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, regionname, country")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_pmtour_countries"] = Array (
	"ctrl" => $TCA["tx_pmtour_countries"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,countryname"
	),
	"feInterface" => $TCA["tx_pmtour_countries"]["feInterface"],
	"columns" => Array (
		'sys_language_uid' => Array (		
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.language',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => Array(
					Array('LLL:EXT:lang/locallang_general.php:LGL.allLanguages',-1),
					Array('LLL:EXT:lang/locallang_general.php:LGL.default_value',0)
				)
			)
		),
		'l18n_parent' => Array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('', 0),
				),
				'foreign_table' => 'tx_pmtour_countries',
				'foreign_table_where' => 'AND tx_pmtour_countries.pid=###CURRENT_PID### AND tx_pmtour_countries.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => Array (		
			'config' => Array (
				'type' => 'passthrough'
			)
		),
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"countryname" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_countries.countryname",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,trim",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, countryname")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_pmtour_tour"] = Array (
	"ctrl" => $TCA["tx_pmtour_tour"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,starttime,endtime,fe_group,name,description,number,length_km,duration_h,images,gpxfile,displaytype,region,showfilter,showaltitudeprofile"
	),
	"feInterface" => $TCA["tx_pmtour_tour"]["feInterface"],
	"columns" => Array (
		'sys_language_uid' => Array (		
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.language',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => Array(
					Array('LLL:EXT:lang/locallang_general.php:LGL.allLanguages',-1),
					Array('LLL:EXT:lang/locallang_general.php:LGL.default_value',0)
				)
			)
		),
		'l18n_parent' => Array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('', 0),
				),
				'foreign_table' => 'tx_pmtour_tour',
				'foreign_table_where' => 'AND tx_pmtour_tour.pid=###CURRENT_PID### AND tx_pmtour_tour.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => Array (		
			'config' => Array (
				'type' => 'passthrough'
			)
		),
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"starttime" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.starttime",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"default" => "0",
				"checkbox" => "0"
			)
		),
		"endtime" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.endtime",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"checkbox" => "0",
				"default" => "0",
				"range" => Array (
					"upper" => mktime(0,0,0,12,31,2020),
					"lower" => mktime(0,0,0,date("m")-1,date("d"),date("Y"))
				)
			)
		),
		"fe_group" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.fe_group",
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("", 0),
					Array("LLL:EXT:lang/locallang_general.php:LGL.hide_at_login", -1),
					Array("LLL:EXT:lang/locallang_general.php:LGL.any_login", -2),
					Array("LLL:EXT:lang/locallang_general.php:LGL.usergroups", "--div--")
				),
				"foreign_table" => "fe_groups"
			)
		),
		"name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_tour.name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_tour.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"number" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_tour.number",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"length_km" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_tour.length_km",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"duration_h" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_tour.duration_h",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"images" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_tour.images",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],	
				"max_size" => 500,	
				"uploadfolder" => "uploads/tx_pmtour",
				"show_thumbs" => 1,	
				"size" => 5,	
				"minitems" => 0,
				"maxitems" => 100,
			)
		),
		"gpxfile" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_tour.gpxfile",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "",	
				"disallowed" => "php,php3",	
				"max_size" => 500,	
				"uploadfolder" => "uploads/tx_pmtour",
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"displaytype" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_tour.displaytype",		
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_tour.displaytype.I.0", "0"),
					Array("LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_tour.displaytype.I.1", "1"),
				),
				"size" => 1,	
				"maxitems" => 1,
			)
		),
		"region" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_tour.region",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_pmtour_regions",	
				"foreign_table_where" => "AND tx_pmtour_regions.pid=###CURRENT_PID### ORDER BY tx_pmtour_regions.uid",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,	
				"wizards" => Array(
					"_PADDING" => 2,
					"_VERTICAL" => 1,
					"add" => Array(
						"type" => "script",
						"title" => "Create new record",
						"icon" => "add.gif",
						"params" => Array(
							"table"=>"tx_pmtour_regions",
							"pid" => "###CURRENT_PID###",
							"setValue" => "prepend"
						),
						"script" => "wizard_add.php",
					),
					"list" => Array(
						"type" => "script",
						"title" => "List",
						"icon" => "list.gif",
						"params" => Array(
							"table"=>"tx_pmtour_regions",
							"pid" => "###CURRENT_PID###",
						),
						"script" => "wizard_list.php",
					),
					"edit" => Array(
						"type" => "popup",
						"title" => "Edit",
						"script" => "wizard_edit.php",
						"popup_onlyOpenIfSelected" => 1,
						"icon" => "edit2.gif",
						"JSopenParams" => "height=350,width=580,status=0,menubar=0,scrollbars=1",
					),
				),
			)
		),
		"showfilter" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_tour.showfilter",		
			"config" => Array (
				"type" => "input",	
				"size" => "5",	
				"eval" => "required,int",
				"default" => "1"
			)
		),
		"showaltitudeprofile" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_tour.showaltitudeprofile",		
			"config" => Array (
				"type" => "check",
				"default" => 0,
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, name, description;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], number, length_km, duration_h, images, gpxfile, displaytype, region, showfilter,showaltitudeprofile")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "starttime, endtime, fe_group")
	)
);



$TCA["tx_pmtour_tourpoints"] = Array (
	"ctrl" => $TCA["tx_pmtour_tourpoints"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,name,latitude,longitude,description,images,url,tour,icon"
	),
	"feInterface" => $TCA["tx_pmtour_tourpoints"]["feInterface"],
	"columns" => Array (
		'sys_language_uid' => Array (		
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.language',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => Array(
					Array('LLL:EXT:lang/locallang_general.php:LGL.allLanguages',-1),
					Array('LLL:EXT:lang/locallang_general.php:LGL.default_value',0)
				)
			)
		),
		'l18n_parent' => Array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('', 0),
				),
				'foreign_table' => 'tx_pmtour_tourpoints',
				'foreign_table_where' => 'AND tx_pmtour_tourpoints.pid=###CURRENT_PID### AND tx_pmtour_tourpoints.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => Array (		
			'config' => Array (
				'type' => 'passthrough'
			)
		),
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_tourpoints.name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "trim",
			)
		),
		"latitude" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_tourpoints.latitude",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"longitude" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_tourpoints.longitude",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_tourpoints.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"images" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_tourpoints.images",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],	
				"max_size" => 500,	
				"uploadfolder" => "uploads/tx_pmtour",
				"show_thumbs" => 1,	
				"size" => 4,	
				"minitems" => 0,
				"maxitems" => 100,
			)
		),
		"tour" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_tourpoints.tour",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_pmtour_tour",	
				"foreign_table_where" => "AND tx_pmtour_tour.pid=###CURRENT_PID### ORDER BY tx_pmtour_tour.uid",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,	
				"wizards" => Array(
					"_PADDING" => 2,
					"_VERTICAL" => 1,
					"add" => Array(
						"type" => "script",
						"title" => "Create new record",
						"icon" => "add.gif",
						"params" => Array(
							"table"=>"tx_pmtour_tour",
							"pid" => "###CURRENT_PID###",
							"setValue" => "prepend"
						),
						"script" => "wizard_add.php",
					),
					"list" => Array(
						"type" => "script",
						"title" => "List",
						"icon" => "list.gif",
						"params" => Array(
							"table"=>"tx_pmtour_tour",
							"pid" => "###CURRENT_PID###",
						),
						"script" => "wizard_list.php",
					),
					"edit" => Array(
						"type" => "popup",
						"title" => "Edit",
						"script" => "wizard_edit.php",
						"popup_onlyOpenIfSelected" => 1,
						"icon" => "edit2.gif",
						"JSopenParams" => "height=350,width=580,status=0,menubar=0,scrollbars=1",
					),
				),
			)
		),
		"icon" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_tourpoints.icon",        
            "config" => Array (
                "type" => "select",    
                "items" => Array (
                    Array("",0),
                ),
                "foreign_table" => "tx_pmtour_icons",    
                "foreign_table_where" => "AND tx_pmtour_icons.pid=###CURRENT_PID### ORDER BY tx_pmtour_icons.uid",    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 1,    
                "wizards" => Array(
                    "_PADDING" => 2,
                    "_VERTICAL" => 1,
                    "add" => Array(
                        "type" => "script",
                        "title" => "Create new record",
                        "icon" => "add.gif",
                        "params" => Array(
                            "table"=>"tx_pmtour_icons",
                            "pid" => "###CURRENT_PID###",
                            "setValue" => "prepend"
                        ),
                        "script" => "wizard_add.php",
                    ),
                ),
            )
        ),
		"url" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_tourpoints.url",        
            "config" => Array (
                "type" => "input",
                "size" => "15",
                "max" => "255",
                "checkbox" => "",
                "eval" => "trim",
                "wizards" => Array(
                    "_PADDING" => 2,
                    "link" => Array(
                        "type" => "popup",
                        "title" => "Link",
                        "icon" => "link_popup.gif",
                        "script" => "browse_links.php?mode=wizard",
                        "JSopenParams" => "height=300,width=500,status=0,menubar=0,scrollbars=1"
                    )
                )
            )
        ),        
	),
	"types" => Array (
		"0" => Array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, name, latitude, longitude, description;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], images, url, tour, icon")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);

$TCA["tx_pmtour_icons"] = Array (
    "ctrl" => $TCA["tx_pmtour_icons"]["ctrl"],
    "interface" => Array (
        "showRecordFieldList" => "hidden,name,icon,shadowicon"
    ),
    "feInterface" => $TCA["tx_pmtour_icons"]["feInterface"],
    "columns" => Array (
        "hidden" => Array (        
            "exclude" => 1,
            "label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
            "config" => Array (
                "type" => "check",
                "default" => "0"
            )
        ),
        "name" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_icons.name",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",    
                "eval" => "required,trim",
            )
        ),
        "icon" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_icons.icon",        
            "config" => Array (
                "type" => "group",
                "internal_type" => "file",
                "allowed" => "gif,png,jpeg,jpg",    
                "max_size" => 500,    
                "uploadfolder" => "uploads/tx_pmtour",
                "show_thumbs" => 1,    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 1,
            )
        ),
        "shadowicon" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:pm_tour/locallang_db.php:tx_pmtour_icons.shadowicon",        
            "config" => Array (
                "type" => "group",
                "internal_type" => "file",
                "allowed" => "gif,png,jpeg,jpg",    
                "max_size" => 500,    
                "uploadfolder" => "uploads/tx_pmtour",
                "show_thumbs" => 1,    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 1,
            )
        ),
    ),
    "types" => Array (
        "0" => Array("showitem" => "hidden;;1;;1-1-1, name, icon, shadowicon")
    ),
    "palettes" => Array (
        "1" => Array("showitem" => "")
    )
);
?>