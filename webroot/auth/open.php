<?php
/**
 * Open OpenTHC SSO Authentication Session
 */

require_once('../../boot.php');

session_start();

$ocp = _oauth_provider();
$url = $ocp->getAuthorizationUrl([
	'scope' => 'chat',
]);

// Get the state generated for you and store it to the session.
$_SESSION['oauth2-state'] = $ocp->getState();

header('HTTP/1.1 302 Found', true, 302);
header('location: ' . $url);

exit(0);
