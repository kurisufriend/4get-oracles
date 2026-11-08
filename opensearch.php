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
	'<?xml version="1.0" encoding="UTF-8"?>' .
	'<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/" xmlns:moz="http://www.mozilla.org/2006/browser/search/">' .
		'<ShortName>' . htmlspecialchars(config::SERVER_NAME) . ($ssl === false ? " ({$src})" : "") . '</ShortName>' .
		'<Description>' . htmlspecialchars(config::SERVER_SHORT_DESCRIPTION) . '</Description>' .
		'<InputEncoding>UTF-8</InputEncoding>' .
		'<Image width="16" height="16" type="image/x-icon">' . htmlspecialchars($domain) . '/favicon.ico</Image>' .
		'<Url type="text/html" method="GET" template="' . htmlspecialchars($domain) . '/web?s={searchTerms}"/>';

if(
	isset($_GET["ac"]) &&
	is_string($_GET["ac"]) &&
	$_GET["ac"] != "disabled"
){
	
	echo '<Url type="application/x-suggestions+json" template="' . $domain . '/api/v1/ac?s={searchTerms}&amp;scraper=' . htmlspecialchars($_GET["ac"]) . '"/>';
}

echo '</OpenSearchDescription>';
