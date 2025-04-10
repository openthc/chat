<?php
/*
 * Actually Connect and Fake the Mattermost Session
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
WHERE 1=0 AND username = :u0 OR email = :e0
ORDER BY id
SQL;
$res = $dbc->fetchRow($sql, [
	':e0' => $Chat_Contact['email'],
	':u0' => $Chat_Contact['username'],
]);
if (empty($res['id'])) {
	_exit_text('<h1>Invalid Account [CAI-062]</h1><p>Perhaps you should <a href="https://openthc.com/chat/invite">Request an Invite</a></p>', 401);
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

// Try to Login as User
$res = $ghc->post('users/login', [
	'json' => [
		'login_id' => $Chat_Contact['email'],
		'password' => $pw1_text,
	]
]);
$res_code = $res->getStatusCode();
$res_body = $res->getBody()->getContents();
$SES['try-one'] = $res_code;
switch ($res_code) {
	case 200:
		// Yay!!
		break;
	case 401:

		// $chat_contact = _mattermost_create_user();
		// var_dump($chat_contact);

		$res = $ghc->post('users/login', [
			'json' => [
				'login_id' => $Chat_Contact['email'],
				'password' => $pw1_text,
			],
		]);

		$res_code = $res->getStatusCode();
		$res_body = $res->getBody()->getContents();
		$SES['try-two'] = $res_code;

		break;

}

$dbc->query('UPDATE users SET password = :p1 WHERE id = :c0', [
	':c0' => $Chat_Contact['id'],
	':p1' => $pw0_hash,
]);

$res = json_decode($res_body, true);
if (empty($res['id'])) {
	__exit_text('Invalid [CAI-122]', 401);
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


/**
 *
 */
function _mattermost_create_user()
{
	$cfg = \OpenTHC\Config::get('mattermost');

	// Client
	$jar = new \GuzzleHttp\Cookie\CookieJar;
	$ghc = new \GuzzleHttp\Client([
		'base_uri' => sprintf('%s/api/v4/', $cfg['origin']),
		'allow_redirects' => false,
		'connect_timeout' => 4.20,
		'http_errors' => false,
		'cookies' => $jar,
	]);

	// Login as Admin
	$res = $ghc->post('users/login', [
		'json' => [
			'login_id' => $cfg['root-username'],
			'password' => $cfg['root-password'],
		]
	]);
	$res_body = $res->getBody()->getContents();
	__exit_text($res_body);
	$res_code = $res->getStatusCode();
	if (200 != $res_code) {
		_exit_html('Invalid Connexion to Chat Server', 502);
	}

	// $head = $res->getHeaders();
	// var_dump($head);
	// $res = $res->getBody()->getContents();
	// $res = json_decode($res, true);
	// var_dump($res);

	$tok = $res->getHeaderLine('token');
	// var_dump($tok);
	$ghc = new \GuzzleHttp\Client([
		'base_uri' => sprintf('%s/api/v4/', $cfg['origin']),
		'allow_redirects' => false,
		'connect_timeout' => 4.20,
		'http_errors' => false,
		// 'synchronous' => true,
		// 'timeout' => 4.20,
		'cookies' => $jar,
		'headers' => [
			'authorization' => sprintf('Bearer %s', $tok)
		]
	]);


	// Get the User
	$url = sprintf('users/email/%s', $SES['Chat_Contact']['email']);
	// var_dump($url);
	$res = $ghc->get($url);
	$res_code = $res->getStatusCode();
	$res = $res->getBody()->getContents();
	$res = json_decode($res, true);
	// var_dump($res);

	switch ($res_code) {
		case 200:
			$chat_contact = $res;
			break;
		case 404:

			// Create the User
			$arg = [
				'json' => [
					'email' => $SES['Chat_Contact']['email'],
					'username' => $SES['Chat_Contact']['username'],
					'password' => $SES['Chat_Contact']['password'],
					// 'auth_data' => $SES['Contact']['id'],
					// 'auth_service' => 'email',
					'notify_props' => [
						'email' => false,
						'desktop' => 'all',
					]
				]
			];
			var_dump($arg);

			$res = $ghc->post('users', $arg);
			$res = $res->getBody()->getContents();
			$res = json_decode($res, true);
			var_dump($res);

			$chat_contact = $res;

	}


	// Update the Password
	$url = sprintf('users/%s/password', $chat_contact['id']);
	// var_dump($url);
	$res = $ghc->put($url, [
		'json' => [
			'new_password' => $SES['Chat_Contact']['password']
		]
	]);
	$res = $res->getBody()->getContents();
	$res = json_decode($res, true);
	// var_dump($res);


	// Verify the Email
	$url = sprintf('users/%s/email/verify/member', $chat_contact['id']);
	// var_dump($url);
	$res = $ghc->post($url, [
		'json' => [],
	]);
	$res = $res->getBody()->getContents();
	$res = json_decode($res, true);
	// var_dump($res);

	// Add User to Team
	// $res = $ghc->get('teams', [
	// ]);
	// $res = $res->getBody()->getContents();
	// $res = json_decode($res, true);
	// var_dump($res);

	// openthc-public
	$team = [
		'id' => $cfg['team_default'],
	];

	// Add Member
	$url = sprintf('teams/%s/members', $team['id']);
	$arg = [
		'json' => [
			'team_id' => $team['id'],
			'user_id' => $chat_contact['id'],
		]

	];
	$res = $ghc->post($url, $arg);
	$res = $res->getBody()->getContents();
	$res = json_decode($res, true);
	// var_dump($res);


	return [
		'username' => $SES['Chat_Contact']['username'],
		'password' => $SES['Chat_Contact']['password'],
	];

	exit;

}
