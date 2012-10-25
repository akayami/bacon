<?php
namespace Example;
use Bacon\Http\Request;

use Bacon\Router\Action\Controller;

class Error extends Controller {
	
	public function handle() {
		print_r(Request::getInstance());
		$this->disableViewRendering();
	}
	
// 	public function handle() {
// 		$this->view->code = Request::getInstance()->code;
// 		$body = '';
// 		switch(Request::getInstance()->code) {
// 			case 404:
// 				$message = 'Not Found';
// 				break;
// 			case 500:
// 				try {
// 					$body = "Error Occured:".Request::getInstance()->error->getMessage();
// 				} catch (\Exception $e) {
// 					$body = "Error Occured";
// 				}
// 				$message = 'Server Error';
// 				break;
// 			default:
// 				$message = 'Service Unavailable';
// 				$this->view->code = 503;
// 				$body = "Service is temporarly unavilable.";
// 				break;
// 		}
// 		$this->view->body = $body;
// 		$this->view->message = $message;
// 	}	
}