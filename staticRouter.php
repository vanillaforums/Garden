<?php
/**
 * @author Alexander Kim <alexander.k@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;


$fileLocator = new FileLocator(array(__DIR__.'/conf'));
$loader = new YamlFileLoader($fileLocator);
$routes = $loader->load('routes.yaml');

$context = new RequestContext('/');

$matcher = new UrlMatcher($routes, $context);
//die(print_r($_SERVER));
try {
    $parameters = $matcher->match($_SERVER['REQUEST_URI']);
    $t = new \Vanilla\Knowledge\Controllers\Api\KnowledgeBasesApiController();
    //die(var_dump($t));
    //return call_user_func_array($parameters['_controller'], []);
    //(print_r($parameters));
    $controller = new $parameters['class']();
    $str = $controiller->$parameters['method'];
    //var_dump($controller);
    //echo '!'.$str;
    //return call_user_func_array($parameters['_controller'], []);

} catch (ResourceNotFoundException $e) {
    // just do nothing and pass control to Gdn system
    // which is bootstrap.php
} catch (Throwable $e) {
    //die($e->getMessage());
}

//$routes = new RouteCollection();

//$route = new Route('/foo', array('_controller' => 'MyController'));
