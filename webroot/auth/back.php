<?php
/**
 * Authentication from the OpenTHC SSO Comes Back Here
 */

require_once('../../boot.php');

session_start();

if (empty($_GET['code'])) {
	_exit_html('<p>Invalid Link [CAB-009]</p>', 400);
}

$ocp = _oauth_provider();

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

$tok0 = json_decode(json_encode($tok0), true);

if (empty($tok0['access_token'])) {
	_exit_html('<p>Invalid Access Token [CAB-054]</p>', 400);
}

if (empty($tok0['token_type'])) {
	_exit_html('<p>Invalid Access Token [CAB-058]</p>', 400);
}

// Using the access token, we may look up details about the
// resource owner.
try {

	$tok1 = $ocp->getResourceOwner($tok);
	$tok1 = $tok1->toArray();

	$tok1['scope'] = explode(' ', $tok1['scope']);

	$_SESSION['Contact'] = $tok1['Contact'];
	$_SESSION['Company'] = $tok1['Company'];

	header('HTTP/1.1 302 Found', true, 302);
	header('location: /auth/init?' . http_build_query(['r' => $_GET['r'] ]));

} catch (\Exception $e) {
	_exit_html('<p>Failure: ' . $e->getMessage() . '</p>', 500);
}

exit(0);
