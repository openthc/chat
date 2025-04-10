<?php
/**
 * Authentication from the OpenTHC SSO Comes Back Here
 */

require_once('../../boot.php');

if (empty($_GET['_'])) {
	_exit_html('<p>Invalid Link [CAB-009]</p>', 400);
}

if (empty($_GET['code'])) {
	_exit_html('<p>Invalid Link [CAB-013]</p>', 400);
}

if ( ! preg_match('/^[\w\-]{43}$/', $_GET['_'])) {
	_exit_html('<p>Invalid Link [CAB-017]</p>', 400);
}

$rdb = \OpenTHC\Service\Redis::factory();
$key = sprintf('/chat/auth/session/%s', $_GET['_']);
$SES = $rdb->get($key);
if (empty($SES)) {
	_exit_html('<p>Invalid Link [CAB-024]</p>', 400);
}
$SES = json_decode($SES, true);
if (empty($SES)) {
	_exit_html('<p>Invalid Link [CAB-028]</p>', 400);
}

$ocp = _oauth_provider($SES['id']);

// __exit_text($SES);


// Check State
// $this->checkState();

// Try to get an access token using the authorization code grant.
try {
	$tok0 = $ocp->getAccessToken('authorization_code', [
		'code' => $_GET['code']
	]);
} catch (\Exception $e) {
	_exit_html('<p>Invalid Access Token [CAB-040]</p>', 400);
}

if (empty($tok0)) {
	_exit_html('<p>Invalid Access Token [CAB-044]</p>', 400);
}

// Token OK?
$tok0a = json_decode(json_encode($tok0), true);
if (empty($tok0a['access_token'])) {
	_exit_html('<p>Invalid Access Token [CAB-054]</p>', 400);
}

if (empty($tok0a['token_type'])) {
	_exit_html('<p>Invalid Access Token [CAB-058]</p>', 400);
}

// Using the access token, we may look up details about the
// resource owner.
try {

	$tok1 = $ocp->getResourceOwner($tok0);
	$tok1 = $tok1->toArray();

	$pass = false;
	if (is_array($tok1['scope'])) {
		if (in_array('chat', $tok1['scope'])) {
			$pass = true;
		}
	}

	if (!$pass) {
		_exit_text('Access Denied [CAB-057]', 403);
	}

	$SES['Contact'] = $tok1['Contact'];
	$SES['Company'] = $tok1['Company'];

	$rdb->set($SES['key'], json_encode($SES));

	header('HTTP/1.1 302 Found', true, 302);
	header(sprintf('location: /auth/init?_=%s', $SES['id']));

} catch (\Exception $e) {
	_exit_html(sprintf('<p>Failure: %s</p>', $e->getMessage()), 500);
}

exit(0);
