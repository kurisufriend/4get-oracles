<?php

header("Content-Type: application/xml");
include "data/config.php";

$host = strtolower(explode(":", $_SERVER["HTTP_HOST"], 2)[0]);

// detect if 4get should serve the HTTPS endpoint
if(
	preg_match(
		'/\.(onion|i2p)$/',
		strtolower($host),
		$match
	)
){
	
	$ssl = false;
	$src = $match[1];
}elseif(
	is_string(
		filter_var(
			$host,
			FILTER_VALIDATE_IP
		)
	) ||
	$host == "localhost"
){
	
	$ssl = false;
	$src = "localhost";
}else{
	
	$ssl = true;
}

$domain = ($ssl ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"];

echo
	'<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">' .
		'<ShortName>' . htmlspecialchars(config::SERVER_NAME) . ($ssl === false ? " ({$src})" : "") . '</ShortName>' .
		'<Description>' . htmlspecialchars(config::SERVER_SHORT_DESCRIPTION) . '</Description>' .
		'<InputEncoding>UTF-8</InputEncoding>' .
		'<Image width="32" height="32" type="image/x-icon">' . htmlspecialchars($domain) . '/favicon.ico</Image>' .
		'<Url rel="results" type="text/html" method="GET" template="' . htmlspecialchars($domain) . '/web?s={searchTerms}"/>';

if(
	isset($_GET["ac"]) &&
	is_string($_GET["ac"]) &&
	$_GET["ac"] != "disabled"
){
	
	echo '<Url rel="suggestions" type="application/x-suggestions+json" template="' . $domain . '/api/v1/ac?s={searchTerms}&amp;scraper=' . htmlspecialchars($_GET["ac"]) . '"/>';
}

echo '</OpenSearchDescription>';
