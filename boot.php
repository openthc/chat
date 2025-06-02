<?php
/**
 * OpenTHC Chat Bootstrap
 */

error_reporting(E_ALL & ~E_NOTICE);

define('APP_ROOT', __DIR__);

require_once(__DIR__ . '/vendor/autoload.php');

\OpenTHC\Config::init(__DIR__);

/**
 * Database Conneciton
 */
function _dbc()
{
	static $dbc;
	if (empty($dbc)) {
		$dsn = \OpenTHC\Config::get('database/chat/dsn');
		$dbc = new \Edoceo\Radix\DB\SQL($dsn);
	}
	return $dbc;
}

/**
 * oAuth Provider Factory
 */
function _oauth_provider($s)
{
	$cfg = \OpenTHC\Config::get('openthc/sso');

	$url = sprintf('https://%s/auth/back?_=%s', $_SERVER['SERVER_NAME'], $s);
	$url = trim($url, '?');
	$ocp = new \League\OAuth2\Client\Provider\GenericProvider([
		'clientId' => $cfg['client-id'],
		'clientSecret' => $cfg['client-sk'],
		'redirectUri' => $url,
		'urlAuthorize' => sprintf('%s/oauth2/authorize', $cfg['origin']),
		'urlAccessToken' => sprintf('%s/oauth2/token', $cfg['origin']),
		'urlResourceOwnerDetails' => sprintf('%s/oauth2/profile', $cfg['origin']),
		'verify' => true
	]);

	return $ocp;
}

/**
 * Copied from www-com Project
 * Wrapper to Draw Friendly JavaScript Redirector
 * Have to go this way so that SameSite=Strict works properly
 * @see https://www.nogginbox.co.uk/blog/strict-cookies-not-sent-by-request
 */
function doJavaScriptRedirect($url) : void
{
	header('cache-control', 'no-store');

	$html = <<<HTML
	<!DOCTYPE html>
	<html lang="en">
	<head>
	<meta charset=utf-8>
	<meta http-equiv="cache-control" content="no-store">
	<title>Redirecting...</title>
	</head>
	<body>
	<script>
	window.localStorage.setItem('__landingPageSeen__', true);
	window.localStorage.setItem('debug', undefined);
	window.localStorage.setItem('was_notified_of_login', true)
	window.localStorage.setItem('__announcement__Preview Mode: Email notifications have not been configured.', true);
	</script>
	<script>window.location = "$url";</script>
	</body>
	</html>
	HTML;

	__exit_html($html);

}
