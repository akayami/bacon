<?php
namespace Bacon\Misc;

Trait StaticConfigurator {

	protected static $_config;

	public static function setConfig(array $config) {
		static::$_config = $config;
	}

	public function getConfig() {
		return static::$_config;
	}
}