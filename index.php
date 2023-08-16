<?php
/**
 * Entry point for all site calls
 *
 * @author Vanessa Richie Alia-Trapero <vrat.engr@gmail.com>
 */

require_once('config/config.php');

$uri = trim($_SERVER['REQUEST_URI'], '/');

// our pages will be accessed following 'host/controller/method/param?query' format
// in order to achieve that, we will have to do some routing
// however, there may be cases when we only need to access some assets, so on those, no routing is needed
if (!file_exists($uri)) {

    // parse uri to get the path and exclude query string
    // if none was requested, let's default to our main/index function
    @list($path, $query) = ($uri) ? explode('?', $uri) : ['main/index', ''] ;

    // parse the path to get the appropriate controller, method and the optional param
    @list($controller, $method, $param) = explode('/', $path);
    $controllerName = $controller . 'Controller';

    // check if the controller file exists, else let's not proceed
    if (!file_exists("controller/$controller.php")) {
        die('Requested page was not found!');
    }
    require_once("controller/$controller.php");
    $controllerClass = new $controllerName;

    // then let's check as well if the method was valid
    if (!$method || !method_exists($controllerClass, $method)) {
        die('Requested page was not found!');
    }

    // if all else is beautiful, then let's load the page
    $controllerClass->$method($param);
}

// ps: if you're accessing this using 'localhost/folder/subfolder/etc' format,
// then the routing will not work since obviously we rely on the slashes to determine the controller and method :O
// try checking out how to make Virtual Hosts :)