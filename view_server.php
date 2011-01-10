#!/usr/bin/php
<?php
/*
    CouchDB Views with PHP
    This file lets you create CouchDB Views using PHP functions.
    begun by Jan <a href="&#109;&#x61;&#x69;&#108;&#x74;&#x6f;&#58;&#x6a;&#x61;&#110;&#64;&#x70;&#104;&#112;&#x2e;&#110;&#101;&#x74;">&#x6a;&#x61;&#110;&#64;&#x70;&#104;&#112;&#x2e;&#110;&#101;&#x74;</a>
    @link http://jan.prima.de/~jan/plok/archives/93-CouchDb-Views-with-PHP.html
    continued by Benjamin Young
    @link http://github.com/BigBlueHat/php_view_server
    PHP5+

    @license Apache License 2.0
*/
error_reporting(E_ALL);

define("DEBUG", true);
define("DEBUG_LOGFILE", "php.log");

_log("launched");

/*
["reset"]
["add_fun","function foo($doc) { return true;}"]
["map_doc","{id:1}"]

["add_fun","function(doc) { return doc;}"]
*/

$cmd = '';
$functions = array();

while($line = trim(fgets(STDIN))) {

	//silence in case we don't get passed $arg
	@list($cmd, $arg) = json_decode($line);

	if ($arg !== null) {
		$jarg = $arg;
		$arg = json_encode($arg);
	}
	_log($line);
	switch($cmd)
	{
	
		case "reset":
			// pretend to free resources
			unset($functions);
			$functions = array();
			println("true");
		break;
	
		case "add_fun":
			// $arg is a string that will compile to a function
			list($func, $body) = _parseFunc($arg);
			_log("function name: '$func'");
			_log("function body: '$body'");
			$functions[] = array($func, $body);
			if(eval('?>'.$body.'<?php ') === false) {
				_log("eval failed: $func: '$body'");
				println(json_encode(array("error"=>"String must eval to a function (ex: \"function($doc) {return $doc;}\").")));
			} else {
				println("true");
			}
		break;
	
		case "map_doc":
			$results = array();
	
			foreach($functions AS $function) {
				list($name, $body) = $function;
				try {
					_log("mapping against $name");
					_log("doc: $arg");
					_log(var_export($jarg, true));
					$name($jarg);
					_log("emits: ".print_r($emits, true));
					//_log("result: ".print_r($result,true));
	
				} catch(Exception $e) {
					$results[] = 0;
				}
			}
			$emits = array($emits);
			$results = json_encode($emits);
			_log("jres: $results");
			println($results);
		break;
	
		default:
			println("error");
			exit(1);
		break;
	} // end switch
} // end while

function println($msg)
{
    echo "$msg\n";
    flush(); // flush stdout needed for CouchDB
}

function _parseFunc($func)
{
    $matches = array();
    preg_match('/function\s+([A-Za-z_][A-Za-z0-9_]+)/', $func, $matches);
    $name = $matches[1];

	$func = substr($func, 1, strlen($func)-2);
	return array($name, $func);
}

function _log($msg)
{
    if(!DEBUG) {
        return;
    }

	static $fp = false;

	$logfile = DEBUG_LOGFILE;
	if(!$fp) {
    	$fp = fopen($logfile, "a") or die("Unable to open logfile '$logfile'.");
	}
	$time = time();
	fwrite($fp, "($time) ".print_r($msg, true)."\n");
}

function _flush()
{
    flush();
}

function emit($name, $value)
{
	$emits[] = array($name, $value);
}
