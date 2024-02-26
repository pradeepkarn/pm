<?php
$home = home;
// Define the routes
include_once 'login_routes.php';
include_once 'public_routes.php';
include_once 'api_routes.php';
include_once 'user_routes.php';
include_once 'admin_routes.php';
include_once 'admin_payment_routes.php';
include_once 'ajax_routes.php';



$routes = array_merge(
    $public_routes,
    $login_routes,
    $user_routes,
    $admin_routes,
    $api_routes,
    $ajax
);
// define('ROUTES',$routes);

// Define middleware for user authentication
function userAuthMiddleware($next)
{
    if (!authenticate()) {
        // Redirect to login page if user is not logged in
        header("Location: /" . home . route('userLogin'));
        exit;
    }
    // Call the next middleware or route handler
    return $next;
}
// function userProfileCompleteMiddleware($next)
// {
//     if (authenticate()) {
//         $completed = profile_completed(USER['id']);
//         if ($completed < 80) {
//             header("Location: /" . home . route('userProfileEdit'));
//             exit;
//         }
//     }
//     // Call the next middleware or route handler
//     return $next;
// }

// Define middleware for admin authentication
function adminAuthMiddleware($next)
{
    if (!authenticate()) {
        // Redirect to login page if admin is not logged in
        header("Location: /" . home . route('adminLogin'));
        exit;
    }
    if (!is_superuser()) {
        // Redirect to login page if admin is not logged in
        header("Location: /" . home . route('adminLogin'));
        exit;
    }
    // Call the next middleware or route handler
    return $next;
}

// $routes = ROUTES;
// Get the current request URI an
// print_r($_SERVER);

$query_string = $_SERVER["QUERY_STRING"]??$_GET;
$request_uri = REQUEST_URI;
$request_method = $_SERVER['REQUEST_METHOD'];
// Iterate through the routes to find a match
$routeObjs = new stdClass;

foreach ($routes as $route => $handler) {
    $contCtrl = count(explode('@', $handler));
    if ($contCtrl > 2) {
        list($controller, $method, $name) = explode('@', $handler);
        list($namekey, $rtname) = explode('.', $name);
        $routeObjs->$rtname = $route;
    }
}
define('ROUTES', $routeObjs);
function route($name, $params = [])
{
    $rt = ROUTES;
    $url = $rt->$name;
    if (count($params) > 0) {
        foreach ($params as $k => $v) {
            $url = str_replace("{{$k}}", $v, $url);
        }
    }
    return $url;
}
foreach ($routes as $route => $handler) {
    // Replace named parameters with regex patterns
    // $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>\w+)', $route);
    $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[\w-]+)', $route);
    //    myprint($pattern);
    // Match the request URI against the pattern
    $req_uri = explode("?", $request_uri);
    if (isset($req_uri[0])) {
        $request_uri = $req_uri[0];
    }
    if (preg_match("#^$pattern$#", $request_uri, $matches)) {
        // Split the handler into controller and method

        list($controller, $method) = explode('@', $handler);

        // Instantiate the controller
        $controller = new $controller();

        // Apply middleware for user authentication to the routes in the $user_routes array
        // if (in_array($route, array_keys($user_routes))) {
        //     $controller = userAuthMiddleware($controller);
        // }
        // Apply middleware for chat message authentication to the routes in the $user_routes array
        if (in_array($route, array_keys($user_routes))) {
            $controller = userAuthMiddleware($controller);
        }

        // Apply middleware for admin authentication to the routes in the $admin_routes array
        if (in_array($route, array_keys($admin_routes))) {
            $controller = adminAuthMiddleware($controller);
        }
        // Apply middleware for profile complete in public routes
        // if (in_array($route, array_keys($public_routes))) {
        //     $controller = userProfileCompleteMiddleware($controller);
        // }

        // Call the method with any named parameters and GET parameters
        $params = array_intersect_key($matches, array_flip(array_filter(array_keys($matches), 'is_string')));
        $params += $_GET;

        // call_user_func_array([$controller, $method], $params);
        $controller->$method($params);

        // Stop processing further routes

        exit;
    }
}

// If no route matched, return a 404 error
http_response_code(404);
echo '404 Not Found';
