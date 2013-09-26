<?php
namespace Bacon\Http\Url;

class Absolute {

	protected $base;
	
	public function __construct($base = '') {
		$this->base = $base;
	}
	
	public function rewrite($url) {
		return http_build_url($this->base, $url);
	}
	
}