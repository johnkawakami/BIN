<?php

// Not sure what the original purpose of this was, but there was one odd
// bit of code. Use at  your own risk.
//
// mapRoutes is a simple router for requests, if you want to program
// in something resembling the "laravel" style.

# global defines
define( "CONFIG_FILE", "/usr/local/etc/qa.ini" );
define( "CACHE_DIR", "/usr/local/var/spool/qa/cache/" );

# global app configuration
$routes = array(
	'/board/*' => array('GET' => 'getBoard'),
	'/board/([0-9]+)/*' => array('GET' => 'getBoard', 'POST' => 'postToBoard'),
	'/question/([0-9]+)/*' => array('GET' => 'getQuestion', 'POST' => 'postAnswer'),
	'/answer/([0-9]+)/*' => array('GET' => 'getAnswer', 'POST' => 'postComment'),
	'/readme/*' => array('GET' => 'readme')
);
$defaultRoute = 'readme';

try {
	$headers = getallheaders();
	$key = validateApplicationKey( $headers );
	$siteConfig = getSiteConfig( $key );
	processSession();
	$format = processOutputFormat( $headers );
	mapRoutes( $routes, $format );
	echo '<pre>';
	// print_r($_SERVER);
} catch(Exception $e) {
	echo $e->getMessage();
}

function mapRoutes( $routes, $format ) {
	// read in some globals
	global $defaultRoute;
	$pathInfo = $_SERVER['PATH_INFO'];
	$scriptName = $_SERVER['SCRIPT_NAME'];
	$requestURI = $_SERVER['REQUEST_URI'];
	$queryString = $_SERVER['QUERY_STRING'];
	$requestMethod = $_SERVER['REQUEST_METHOD'];

	// validate
	if ($requestMethod != 'POST' and $requestMethod != 'GET') { 
		throw new Exception('unknown method'); 
	}

	foreach( array_keys($routes) as $route ) {
		if (preg_match( '#^'.$route.'$#', $pathInfo, $matches )) {
			// echo "matched $route<br>";
			$dispatch = $routes[$route];
			if (isset($dispatch[$requestMethod])) {
				// turn string into method call
				$func = $dispatch[$requestMethod];
				if (function_exists($func)) {
					call_user_func( $func, $format, $matches );
				}
			} else {
				throw new Exception("handler for $requestMethod is not set");
			}
		} 
	}
	// the route didn't match anything, so perform the default
	$defaultRoute( $format );
}
function processSession() {
	// fixme add error checking
	session_start();
	session_regenerate_id( true );
}
