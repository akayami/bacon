Bacon - Simple MVC Router
=========================
It's a simple PHP Router that you can use with pretty much anything to handle basic Model/View/Controller (MVC) design 

## Characteristics

1. Simple
2. Fast
3. Unobstructive
4. Flexible/Extensible
5. You know exactly what it is doing and why

## Requrements: 

Coded in PHP 5.4 but should work in PHP 5.3 (please complain if it does not).

## Condenced boostrap from example project

    <?php
    use Example\Router\Controller;
    use Bacon\Router\Route\Simple;
    use Bacon\Router\Route\Regex;
    use Bacon\Router\Route\Auto;
    use Bacon\Http\Request;
    
    include('../inc/includePath.inc.php'); 
    include('autoload.inc.php');
    
    $cont = Controller::getInstance();
    $cont->addRoute(new Simple('/', 'Example\Front', 'standard'), 0);
    $cont->addRoute(new Regex('#^/error/(?<code>\d+)$#', 'Example\Error', 'handle'), 1000);  
    $cont->addRoute(new Auto('\Bacon\Example'), 1001);										
    try {
    	$cont->route(Request::getInstance()->REQUEST_URI);
    } catch(\Exception $e) {	
    	ob_end_clean(); 						
    	Request::getInstance()->error = $e;		
    	$cont->route('/error/'.$e->getCode());	
    }


# Installing

Normally, all you need is in lib/ folder. Example contains a sample setup. 
You can use to start up your project it you like it, or you can come up with your own organization.