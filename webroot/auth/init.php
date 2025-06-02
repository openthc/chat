<?php
/*
 * Actually Connect and Fake the Mattermost Session
 *
 * SPDX-License-Identifier: MIT
 */

require_once('../../boot.php');

if (empty($_GET['_'])) {
	_exit_html('<p>Invalid Link [CAI-009]</p>', 400);
}

if ( ! preg_match('/^[\w\-]{43}$/', $_GET['_'])) {
	_exit_html('<p>Invalid Link [CAI-013]</p>', 400);
}

$rdb = \OpenTHC\Service\Redis::factory();
$key = sprintf('/chat/auth/session/%s', $_GET['_']);
$SES = $rdb->get($key);
if (empty($SES)) {
	_exit_html('<p>Invalid Link [CAI-020]</p>', 400);
}
$SES = json_decode($SES, true);
if (empty($SES)) {
	_exit_html('<p>Invalid Link [CAI-024]</p>', 400);
}

if (empty($SES['Contact']['id'])) {
	_exit_text('Invalid Request [CAI-011]', 400);
}

// Chat System Contact Data
$Chat_Contact = [];
$Chat_Contact['email'] = $SES['Contact']['username'];
$Chat_Contact['email'] = strtolower($Chat_Contact['email']);
// $Chat_Contact['username'] = preg_replace('/[^\w\-]+/', '-', $SES['Contact']['username']);
$Chat_Contact['username'] = strtok($SES['Contact']['username'], '@');

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
	_exit_html('<h1>Invalid Account [CAI-062]</h1><p>Perhaps you should <a href="https://openthc.com/chat/invite">Request an Invite</a></p>', 401);
}
$Chat_Contact = $res;

$pw0_hash = $res['password'];
// $pw0_hash = password_hash('passweed', PASSWORD_BCRYPT);
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

$dbc->query('UPDATE users SET password = :p1 WHERE id = :c0', [
	':c0' => $Chat_Contact['id'],
	':p1' => $pw0_hash,
]);

switch ($res_code) {
case 200:
	// OK
	break;
case 401:
	_exit_text('Authentication Failure', 401);
	break;
case 502:
	_exit_text('Chat Services Offline', 502);
	break;
}

$res = json_decode($res_body, true);
if (empty($res['id'])) {
	_exit_text('Invalid [CAI-122]', 401);
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
