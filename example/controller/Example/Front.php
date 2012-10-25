<?php
namespace Example;
use Bacon\Http\Request;
use Bacon\Router\Action\Controller;

class Front extends Controller {
	
	public function standard() {
// 		echo "OK";
// 		$this->disableViewRendering();
	}
	
	private function hidden() {
		echo "OK!!";
		$this->disableViewRendering();
	}
	
}