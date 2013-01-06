<?php
/**
 * Example of a prep statement
 */
Config::setConfig($config);

$db = Manager::singleton()->get()->master();
$stmt = $db->prepare('SELECT * FROM template WHERE width = ? and height = ?');
$stmt->bindParam($width, 'i');
$stmt->bindParam($height, 'i');

$sizes = [1200, 300];
$heights = [900, 200];

foreach($sizes as $width) {
	foreach($heights as $height) {
		if($stmt->execute()) {
			print_r($stmt->getResult()->fetchAll());
		}
	}
}