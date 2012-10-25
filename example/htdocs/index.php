<?php
use Example\Router\Controller;
use Bacon\Router\Route\Simple;
use Bacon\Router\Route\Regex;
use Bacon\Router\Route\Auto;
use Bacon\Http\Request;

// Metrics for testing
$start = microtime(true);

/*
 * Setting up include paths. This is normally done in vhost file for speed reasons, 
 * but for convinience, can be done this way as well
 */

include('../inc/includePath.inc.php'); 
include('autoload.inc.php');

/*
 * For sake of example, the standard Router Controller is extended by a special Example Router Controller. 
 * It could implement different some different logic
 */
$cont = Controller::getInstance();

/*
 * Adding different types of routes.
 */

// Very simple and fast exact maching
$cont->addRoute(new Simple('/', 'Example\Front', 'standard'), 0);
					
// Regex matching. Ultra flexible
$cont->addRoute(new Regex('#^/error/(?<code>\d+)$#', 'Example\Error', 'handle'), 1000);	

// A catch all routing often used in CMS/Admin sytems where consistent url stucture is used, and SEO does not matter
$cont->addRoute(new Auto('\Bacon\Example'), 1001);										


try {
	// Routing is executed.
	$cont->route(Request::getInstance()->REQUEST_URI);
} catch(\Exception $e) {
	/*
	 * Handling catchable errors
	 */ 
	ob_end_clean(); 						// Emptying whatever might have been written into buffer
	Request::getInstance()->error = $e;		// Attaching execption so that error controller can act on it
	$cont->route('/error/'.$e->getCode());	// Routing to error controller
}

$end = microtime(true);
/*
 * Some metrics for testing
 */
echo $end - $start;
echo "<br>";
echo memory_get_peak_usage(true);