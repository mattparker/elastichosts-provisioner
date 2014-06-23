<?php
/**
 * User: matt
 * Date: 23/06/14
 * Time: 11:10
 */

/**
 * Mock ElasticHosts endpoint for testing purposes
 *
 * Run it as the built-in server for php and point build process
 * at it; it should return reasonable values...
 *
 */



// Get the path:
$request = $_SERVER["REQUEST_URI"];
$parts = explode('/', $request);

$controller = ucfirst(strtolower($parts[1]));
$method = ucfirst(strtolower($parts[2])) . 'Action';
// pass args to method
$args = [];
for ($i = 3; $i < count($parts); $i = $i + 2) {
    $args[$parts[$i]] = $parts[$i+1];
}

//echo ' going to ' . $controller . '::' . $method . ' with '; print_r($args);


// What we do:
$response = call_user_func_array(array($controller, $method), $args);
echo $response;


class Drives {

    public function createAction ($args) {
        return '1232asdf-asdf99-asdfsdf';
    }

    public function listAction ($args) {
        return <<<DRIVELIST
019238098-192380928
945vkjfd=-dfsdfsdfk
DRIVELIST;
    }
}