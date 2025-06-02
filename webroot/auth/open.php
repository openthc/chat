<?php
/**
 * OpenTHC Chat Authentication Integration
 *
 * SPDX-License-Identifier: MIT
 */

require_once('../../boot.php');

$SES = [];

$kp = sodium_crypto_box_keypair();
$pk = sodium_bin2base64(sodium_crypto_box_publickey($kp), SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
$sk = sodium_bin2base64(sodium_crypto_box_secretkey($kp), SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
$SES['id'] = $pk;
$SES['sk'] = $sk;
$SES['key'] = sprintf('/chat/auth/session/%s', $SES['id']);

// OAuth Provider
$ocp = _oauth_provider($SES['id']);
$url = $ocp->getAuthorizationUrl([
	'scope' => 'chat',
]);

$SES['auth-state'] = $ocp->getState();

$rdb = \OpenTHC\Service\Redis::factory();
$rdb->set($SES['key'], json_encode($SES), [ 'ex' => 240 ]);

header('HTTP/1.1 302 Found', true, 302);
header(sprintf('location: %s', $url));

exit(0);
