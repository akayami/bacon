<?php
namespace Bacon\Router;


interface Route {
	
	/**
	 * @param $request route
	 * 
	 * @return boolean
	 */
	public function isvalidate($route);
	
	
	
	/**
	 * @return array
	 */
	public function getParams();
	
	public function getController();
	
	public function getAction();
	
	public function validate();
}