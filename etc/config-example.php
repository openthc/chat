<?php
/**
 * OpenTHC Chat Configuration
 */

$cfg = [];

$cfg['database'] = [
	'chat' => [
		'hostname' => '10.4.20.69',
		'username' => 'openthc_chat',
		'password' => 'openthc_chat',
		'database' => 'openthc_chat',
		'dsn' => 'pgsql:host=10.4.20.69;user=openthc_chat;password=openthc_chat;dbname=openthc_chat',
	],
];

// OpenTHC Services
$cfg['openthc'] = [
	'chat' => [
		'id' => '/* Value from Auth Database */',
		'origin' => 'https://chat.openthc.example.com',
		'public' => '/* Value from Auth Database */',
		'secret' => '/* Value from Auth Database */',
	],
	'sso' => [
		'origin' => 'https://sso.openthc.example.com',
		'public' => '/* Value from Auth Database */',
		'client-id' => '/* Value from Auth Database */',
		'client-pk' => '/* Value from Auth Database */',
		'client-sk' => '/* Value from Auth Database */',
	]
];

$cfg['mattermost'] = [
	'origin' => 'https://chat.openthc.example.com',
	'root-id' => '/* Value from Chat Database */',
	'root-username' => '/* Value from Chat Database */',
	'root-password' => '',
	'root-sk' => '/* The Token */',
	'team_default' => '/* ID */',
	// 'channel'
	// 'company'
	// 'forum'
];

return $cfg;
