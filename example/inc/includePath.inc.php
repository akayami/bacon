<?php
/*
 * This file is normally not nessesary. You should set your include paths in vhost in prod enviroments using: 
 * php_value include_path ".:path1/sds:path2/sds"
 * 
 * It is much faster
 */

$pieces = explode(DIRECTORY_SEPARATOR, __FILE__);
array_pop($pieces);
array_pop($pieces);
$_incRoot = implode(DIRECTORY_SEPARATOR, $pieces);
$_incPaths = array(
	'conf',
	'inc',
	'lib',
	'controller',
	'view',
	'template'
);
foreach($_incPaths as $key => $path) {
	$_incPaths[$key] = $_incRoot.'/'.$path;
}
$_incPaths[] = '/home/t_rakowski/dev/git/bacon/lib';

set_include_path(implode(PATH_SEPARATOR, array_merge(explode(PATH_SEPARATOR, get_include_path()), $_incPaths)));