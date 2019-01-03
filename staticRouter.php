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
    require_once(__DIR__.'/container.php');
    //die(var_dump($parameters));
    $controller = Gdn::factory($parameters['_controller']);
    $data = $controller->{$parameters['action']}();
//    die(var_dump($data));
    $response = new \Garden\Web\Data((array)$data);
    $response->render();
    exit;
} catch (ResourceNotFoundException $e) {
    // just do nothing and pass control to Gdn system
    // which is bootstrap.php
} catch (Throwable $e) {
    // we should implement some handler here
    // this is the case when static route was detected,
    // but some exception was fired and not handled by controller itself
    die($e->getMessage());
}

