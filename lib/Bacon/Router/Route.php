<?php
namespace Bacon\Router;


interface Route {
	
	/**
	 * 
	 * @param string $request 
	 * @return boolean
	 */
	public function isValid($route);
		
	
	/**
	 * @return array
	 */
	public function getParams();
	
	/**
	 * @return string
	 */
	public function getController();
	
	/**
	 * @return string
	 */
	public function getAction();
	
	/**
	 * @return boolean
	 */
	public function validate();
}