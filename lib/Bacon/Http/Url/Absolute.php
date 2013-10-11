<?php
namespace Bacon\Http\Url;

class Absolute {

	protected $base;
	
	public function __construct($base = '') {
		$parsed = parse_url($base);
		unset($parsed['query']);
		$this->base = http_build_query($parsed);
	}
	
	public function rewrite($url) {
		return http_build_url($this->base, $url);
	}
	
}