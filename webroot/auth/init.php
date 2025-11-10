<?php
/*
 * Actually Connect and Fake the Mattermost Session
 *
 * SPDX-License-Identifier: MIT
 */

require_once('../../boot.php');

if (empty($_GET['_'])) {
	_exit_html('<h1>Invalid Link [CAI-009]</h1>', 400);
}

if ( ! preg_match('/^[\w\-]{43}$/', $_GET['_'])) {
	_exit_html('<h1>Invalid Link [CAI-013]</h1>', 400);
}

$rdb = \OpenTHC\Service\Redis::factory();
$key = sprintf('/chat/auth/session/%s', $_GET['_']);
$SES = $rdb->get($key);
if (empty($SES)) {
	_exit_html('<h1>Invalid Link [CAI-020]</h1>', 400);
}
$SES = json_decode($SES, true);
if (empty($SES)) {
	_exit_html('<h1>Invalid Link [CAI-024]</h1>', 400);
}

if (empty($SES['Contact']['id'])) {
	_exit_html('<h1>Invalid Request [CAI-011]</h1>', 400);
}

// Chat System Contact Data
$Chat_Contact = [];
$Chat_Contact['email'] = $SES['Contact']['username'];
$Chat_Contact['email'] = strtolower($Chat_Contact['email']);
$Chat_Contact['username'] = strtok($SES['Contact']['email'], '@');
$Chat_Contact['username'] = preg_replace('/[^\w\.\-]+/', '', $Chat_Contact['username']);


// Locate Mattermost User
$dbc = _dbc();
$sql = <<<SQL
SELECT id, email, username, password
FROM users
WHERE username = :u0 OR email = :e0
ORDER BY id
SQL;
$res = $dbc->fetchRow($sql, [
	':e0' => $Chat_Contact['email'],
	':u0' => $Chat_Contact['username'],
]);
if (empty($res['id'])) {

	$cfg = \OpenTHC\Config::get('mattermost');
	$key = \OpenTHC\Config::get('mattermost/root-sk');

	// Login as Admin
	if (empty($key)) {

		$mmc = _mm_client();

		$res = $mmc->post('users/login', [ 'json' => [
			'login_id' => $cfg['root-username'],
			'password' => $cfg['root-password'],
		]]);
		if (200 != $res->getStatusCode()) {
			_exit_html('<h1>Failed to Connect [WAI-088]</h1>', 500);
		}

		$key = $res->getHeaderLine('token');

	}

	$mmc = _mm_client($key);

	$Chat_Contact['password'] = _ulid();

	$res = $mmc->post('users', [ 'json' => $Chat_Contact ]);
	if (201 != $res->getStatusCode()) {
		$res = $res->getBody()->getContents();
		_exit_html('<h1>Failed to Connect</h1>' . '<pre>' . $res . '</pre>', 500);
	}
	// $res = $this->assertValidResponse($res, 201);
	$res = json_decode( $res->getBody(), true );
	// var_dump($res);
	// $Chat_Contact['id'] =
	// _exit_html('<h1>Invalid Account [CAI-062]</h1><p>Perhaps you should <a href="https://openthc.com/chat/invite">Request an Invite</a></p>', 401);

	$Chat_Contact['id'] = $res['id'];

} else {
	$Chat_Contact['id'] = $res['id'];
}


// Set Fake Password
$pw1_text = _ulid();
$pw1_hash = password_hash($pw1_text, PASSWORD_BCRYPT);

$dbc->query('UPDATE users SET password = :p1 WHERE id = :c0', [
	':c0' => $Chat_Contact['id'],
	':p1' => $pw1_hash,
]);

$cfg = \OpenTHC\Config::get('mattermost');

// Client
$jar = new \GuzzleHttp\Cookie\CookieJar;
$ghc = new \GuzzleHttp\Client([
	'base_uri' => sprintf('%s/api/v4/', $cfg['origin']),
	'allow_redirects' => false,
	'connect_timeout' => 4,
	'http_errors' => false,
	'cookies' => $jar,
	'headers' => [
		'sec-fetch-dest' => 'empty',
		'sec-fetch-mode' => 'cors',
		'sec-fetch-site' => 'same-origin',
		'x-requested-with' => 'XMLHttpRequest',
	]
]);

$try_idx = 0;
$try_max = 3;
do {

	$try_idx++;
	$SES['try-idx'] = $try_idx;

	// Try to Login as User
	$res = $ghc->post('users/login', [ 'json' => [
		'login_id' => $Chat_Contact['email'],
		'password' => $pw1_text,
	]]);
	$res_code = $res->getStatusCode();
	$res_body = $res->getBody()->getContents();
	switch ($res_code) {
	case 200:
		// Yay!!
		$try_idx = $try_max + 1;
		break;
	case 401:
		break;
	case 502:
		$try_idx = $try_max + 1;
		// _exit_text('Chat Services Offline', 502);
		break;
	// default:
	// 	throw new \Exception('Invalid Response');
	}

} while ($try_idx <= $try_max);

// Set Chat Password to the one from our SSO
$Chat_Contact['password'] = $SES['Contact']['password'];
$dbc->query('UPDATE users SET password = :p1 WHERE id = :c0', [
	':c0' => $Chat_Contact['id'],
	':p1' => $Chat_Contact['password'],
]);

switch ($res_code) {
case 200:
	// OK
	break;
case 401:
	_exit_html('<h1>Authentication Failure [CAI-165]</h1>', $res_code);
	break;
case 502:
	_exit_html('<h1>Chat Services Offline [CAI-168]</h1>', $res_code);
	break;
}

$res = json_decode($res_body, true);
if (empty($res['id'])) {
	_exit_html('<h1>Invalid Connection [CAI-122]</h1>', 401);
}

// Copy Cookies
$cookie_list  = [];
$res = $jar->toArray();
foreach ($res as $c) {
	$c = array_change_key_case($c);
	$cookie_list[ $c['name'] ] = $c;
}
// file_put_contents('/tmp/mattermost-cookies.txt', __json_encode($cookie_list, JSON_PRETTY_PRINT));

// Set Cookies
foreach ($cookie_list as $c) {
	setcookie($c['name'], $c['value'], [
		'domain' => $c['domain'],
		'expires' => $c['expires'],
		'httponly' => $c['httponly'],
		'path' => $c['path'],
		'samesite' => null,
		'secure' => $c['secure'],
	]);
}

// Redirect
// header('HTTP/1.1 302 Found', true, 302);
// header('location: /login');
// header('location: /public/channels/town-square');

doJavaScriptRedirect('/public/channels/town-square');

exit(0);
