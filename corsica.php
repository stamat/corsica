<?php

// CORS
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
	header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH");
    header('Access-Control-Max-Age: 86400');
}

$curl = curl_init();

// URL of the target (this should be changed to be modular)
$domain = "YOUR_DOMAIN_HERE";
$url = "http://".$domain;
$script_rel_path = preg_replace('/.*public_html/','', __FILE__); //not all servers have public_html
$url_part = str_replace($script_rel_path, '', $_SERVER['REQUEST_URI']);
$url .= $url_part;

$all_headers = array();

$method = strtolower($_SERVER['REQUEST_METHOD']);
$accepted_methods = array('get', 'post', 'delete', 'options', 'put', 'patch');

if (array_search($method, $accepted_methods) === false) {
	exit(0);
}

function post() {
	global $curl;
	
	$size = sizeof($_POST);
	if ($size === 0) {
		$size = 1;
	}
	
	curl_setopt($curl, CURLOPT_POST, $size);
	curl_setopt($curl, CURLOPT_POSTFIELDS, file_get_contents('php://input'));
}

function delete() {
	global $curl;
	
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
}

function put() {
	global $curl;
	
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
	curl_setopt($curl, CURLOPT_POSTFIELDS, file_get_contents('php://input'));
}

function patch() {
	global $curl;
	
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
	curl_setopt($curl, CURLOPT_POSTFIELDS, file_get_contents('php://input'));
}

function applyRequestHeaders() {
	global $all_headers, $method;
	
    $headers = array();
	$accepted_headers = array('Accept', 'Cookie', 'User-Agent', 'Accept-Encoding', 'Accept-Language', 'X-Requested-With', 'Content-Type', 'Connection', 'Keep-Alive');
    foreach($_SERVER as $key => $value) {
        if (substr($key, 0, 5) <> 'HTTP_') {
            continue;
        }
        $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
		$all_headers[$header] = $value;
		
		if (array_search($header, $accepted_headers) === false) {
			continue;
		}
		
		if($method === 'options' && $header === 'X-Requested-With') { //preflight doesnt allow x-requested-with on some setups
			continue;
		}
		
		array_push($headers, $header.': '.$value);
    }
    return $headers;
}

function applyResponseHeaders($header_text) {
    foreach (explode("\r\n", $header_text) as $i => $line) {
        list ($key, $value) = explode(': ', $line);
		if (empty($value)) {
			continue;
		}
		
		if (strpos('Access-Control', $value) !== false) {
			continue;
		}
		
		header($line);
	}
}

$headers = applyRequestHeaders();
//var_dump($all_headers);
$headers[] = 'Origin: '.$domain;

curl_setopt($curl,CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
//curl_setopt($curl, CURLOPT_ENCODING, 'identity');

curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_VERBOSE, 1);
curl_setopt($curl, CURLOPT_HEADER, 1);

if (function_exists($method)) {
	call_user_func($method, $curl);
}

$result = curl_exec($curl);

$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
$header = substr($result, 0, $header_size);

applyResponseHeaders($header);
$body = substr($result, $header_size);

echo $body;

curl_close($curl);