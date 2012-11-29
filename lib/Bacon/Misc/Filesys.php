<?php
namespace Bacon\Misc;

class Filesys {

	/**
	 *
	 * @param string $dir
	 * @return boolean
	 */
	public static function delTree($dir) {
		$files = array_diff(scandir($dir), array('.','..'));
		foreach ($files as $file) {
			$f = $dir.DIRECTORY_SEPARATOR.$file;
			(is_dir($f)) ? static::delTree($f) : unlink($f);
		}
		return rmdir($dir);
	}
}