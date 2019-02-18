<?php

/*
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
*/

ini_set('max_execution_time', 2100);

// parser
require_once "config.php";
require_once "functions.php";

global $url_site;

$action = $_POST['action'];
if ($action == 'start') { // start

    $result = getPageCurl($url_site);

    $list_cat = getListCategory($result);
    $GLOBALS['max_page'] = count($list_cat);
    @unlink('cache/cache.dat');

	echo json_encode(array('list_cat' => $list_cat));
	exit;
	
} elseif ($action == 'parse') {

	sleep(1);
	$id_cat = $_POST['id_cat'];
	$page = $_POST['offset'];

	$i = 0;

	while ($i < 1) {
		$list_object = getObjectsPage($id_cat, $page, $GLOBALS['max_page']);
		saveCache(json_encode($list_object));
		$i++;
      $page++;
	}

	echo json_encode(array('offset' => $page, 'max_offset' => $GLOBALS['max_page'] - 1));
	exit;
}

?>