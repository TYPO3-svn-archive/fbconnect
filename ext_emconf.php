<?php

########################################################################
# Extension Manager/Repository config file for ext "fbconnect".
#
# Auto generated 24-12-2010 13:25
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Facebook Connect',
	'description' => 'Allows linking of Facebook accounts af T3 frontend users.',
	'category' => 'plugin',
	'author' => 'Søren Thing Andersen, Net Image A/S, Nils Blattner, cab service AG',
	'author_email' => 'sta@netimage.dk, nb@cabag.ch',
	'shy' => '',
	'dependencies' => 'cms',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.3.2',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
	'_md5_values_when_last_written' => 'a:30:{s:9:"ChangeLog";s:4:"9484";s:10:"README.txt";s:4:"1817";s:22:"class.tx_fbconnect.php";s:4:"6e2a";s:21:"ext_conf_template.txt";s:4:"3b15";s:12:"ext_icon.gif";s:4:"ab0d";s:17:"ext_localconf.php";s:4:"9c35";s:14:"ext_tables.php";s:4:"a75b";s:14:"ext_tables.sql";s:4:"93fc";s:16:"locallang_db.xml";s:4:"f4e0";s:19:"doc/wizard_form.dat";s:4:"9579";s:20:"doc/wizard_form.html";s:4:"f9f9";s:30:"lib/class.tx_fbconnect_api.php";s:4:"c770";s:16:"lib/facebook.php";s:4:"7868";s:24:"lib/facebook_desktop.php";s:4:"074e";s:22:"lib/facebook_graph.php";s:4:"195e";s:32:"lib/facebookapi_php5_restlib.php";s:4:"ee15";s:31:"lib/jsonwrapper/jsonwrapper.php";s:4:"d3b3";s:37:"lib/jsonwrapper/jsonwrapper_inner.php";s:4:"2bad";s:29:"lib/jsonwrapper/JSON/JSON.php";s:4:"3ce5";s:28:"lib/jsonwrapper/JSON/LICENSE";s:4:"f572";s:30:"pi1/class.tx_fbconnect_pi1.php";s:4:"71fc";s:17:"pi1/locallang.xml";s:4:"484c";s:19:"pi1/res/fb_image.js";s:4:"d899";s:21:"pi1/res/template.html";s:4:"a815";s:24:"pi1/static/constants.txt";s:4:"e0f7";s:20:"pi1/static/setup.txt";s:4:"24b2";s:16:"res/functions.js";s:4:"b3cb";s:21:"res/pi1_template.html";s:4:"38db";s:19:"res/xd_receiver.htm";s:4:"810f";s:30:"sv1/class.tx_fbconnect_sv1.php";s:4:"d5d4";}',
);

?>