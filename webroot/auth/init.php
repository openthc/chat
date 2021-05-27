<?php
/*
 * Actually Connect and Fake the Mattermost Session
 */

require_once('../../boot.php');

session_start();

if (empty($_SESSION['Contact']['id'])) {
	_exit_text('Invalid Request [CAI-011]', 400);
}

// var_dump($_SESSION);
$_SESSION['Chat_Contact'] = [];
$_SESSION['Chat_Contact']['email'] = $_SESSION['Contact']['username'];
$_SESSION['Chat_Contact']['username'] = preg_replace('/[^\w\-\_\.]+/', '-', $_SESSION['Contact']['username']);
$x = sprintf('%s-%s', \OpenTHC\Config::get('salt'), $_SESSION['Contact']['username']);
$_SESSION['Chat_Contact']['password'] = base64_encode_url( hash('sha256', $x, true) );

$cfg = \OpenTHC\Config::get('mattermost');

// Client
$jar = new \GuzzleHttp\Cookie\CookieJar;
$ghc = new \GuzzleHttp\Client([
	'base_url' => sprintf('https://%s/api/v4/', $cfg['hostname']),
	'allow_redirects' => false,
	'connect_timeout' => 4.20,
	'http_errors' => false,
	// 'synchronous' => true,
	// 'timeout' => 4.20,
	'cookies' => $jar,
	'headers' => [
		'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.70 Safari/537.36',
	]
]);

// Try to Login as User
$res = $ghc->post('users/login', [
	'json' => [
		'login_id' => $_SESSION['Chat_Contact']['username'],
		'password' => $_SESSION['Chat_Contact']['password'],
	],
]);
$res_code = $res->getStatusCode();
// var_dump($res_code);
switch ($res_code) {
	case 200:
		// Yay!!
		break;
	case 401:

		$chat_contact = _mattermost_create_user();
		// var_dump($chat_contact);

		$res = $ghc->post('users/login', [
			'json' => [
				'login_id' => $_SESSION['Chat_Contact']['email'],
				'password' => $_SESSION['Chat_Contact']['password'],
			],
		]);

		break;

}
$res = $res->getBody()->getContents();
$res = json_decode($res, true);
var_dump($res);

$tok = [];
$tok['contact_id'] = $res['id'];

$cookie_list = $jar->toArray();
foreach ($cookie_list as $c) {
	$tok[ $c['Name'] ] = $c['Value'];
}
var_dump($tok);


// Set Cookie Options
$opt = [
	'expires' => $_SERVER['REQUEST_TIME'] + (86400 * 30),
	'path' => '/',
	'domain' => $_SERVER['SERVER_NAME'],
	'secure' => true,
	'httponly' => true, // MMAUTHTOKEN ONLY
	'samesite' => null,
];

// Set Cookies
setcookie('MMAUTHTOKEN', $tok['MMAUTHTOKEN'], $opt);
unset($opt['httponly']);
setcookie('MMCSRF', $tok['MMCSRF'], $opt); // 0, '/', 'meta.weedtraqr.com', true, false);
setcookie('MMUSERID', $tok['MMUSERID'], $opt);


// Redirect
header('HTTP/1.1 302 Found', true, 302);
header(sprintf('location: https://%s/openthc-public', $_SERVER['SERVER_NAME']));

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
		'base_url' => sprintf('https://%s/api/v4/', $cfg['hostname']),
		'allow_redirects' => false,
		'connect_timeout' => 4.20,
		'http_errors' => false,
		'cookies' => $jar,
	]);

	// Login as Admin
	$res = $ghc->post('users/login', [
		'json' => [
			'login_id' => $cfg['username'],
			'password' => $cfg['password'],
		]
	]);
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
		'base_url' => sprintf('https://%s/api/v4/', $cfg['hostname']),
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
	$url = sprintf('users/email/%s', $_SESSION['Chat_Contact']['email']);
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
					'email' => $_SESSION['Chat_Contact']['email'],
					'username' => $_SESSION['Chat_Contact']['username'],
					'password' => $_SESSION['Chat_Contact']['password'],
					'auth_data' => $_SESSION['Contact']['id'],
					'auth_service' => 'email',
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

	// var_dump($chat_contact);


	// Update the Password
	$url = sprintf('users/%s/password', $chat_contact['id']);
	// var_dump($url);
	$res = $ghc->put($url, [
		'json' => [
			'new_password' => $_SESSION['Chat_Contact']['password']
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
		'username' => $_SESSION['Chat_Contact']['username'],
		'password' => $_SESSION['Chat_Contact']['password'],
	];

	exit;

}
