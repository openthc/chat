<?php
/**
 * OpenTHC Chat Bootstrap
 */

error_reporting(E_ALL & ~E_NOTICE);

define('APP_ROOT', __DIR__);

require_once(__DIR__ . '/vendor/autoload.php');

\OpenTHC\Config::init(__DIR__);

function _oauth_provider()
{
	$cfg = \OpenTHC\Config::get('openthc/sso');

	$r = $_GET['r'];
	switch ($r) {
	case '1':
	case 'r':
		$r = $_SERVER['HTTP_REFERER'];
		break;
	}

	$url = sprintf('https://%s/auth/back?%s', $_SERVER['SERVER_NAME'], http_build_query(array('r' => $r)));
	$url = trim($url, '?');
	$ocp = new \League\OAuth2\Client\Provider\GenericProvider([
		'clientId' => $cfg['client-pk'],
		'clientSecret' => $cfg['client-sk'],
		'redirectUri' => $url,
		'urlAuthorize' => sprintf('%s/oauth2/authorize', $cfg['origin']),
		'urlAccessToken' => sprintf('%s/oauth2/token', $cfg['origin']),
		'urlResourceOwnerDetails' => sprintf('%s/oauth2/profile', $cfg['origin']),
		'verify' => true
	]);

	return $ocp;
}
