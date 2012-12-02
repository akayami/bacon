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

	/**
	 * Generates a predictible storage path.
	 * 1. Allows to store a lot of files in one dir
	 * 2. Is Fast
	 * 3. Is Easily Predictible
	 *
	 * @param int $id
	 * @param int $depth
	 * @return string
	 */
	public static function generatePathFromId($id, $depth = 3) {
		$out = str_split(str_pad($id, 3 * $depth , '0', STR_PAD_LEFT), 1 * $depth);
		return implode(DIRECTORY_SEPARATOR, $out);
	}

	public static function generateHiddenPathFromId() {
		throw new \Exception('Implement me!');
	}
}