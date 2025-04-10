<?php
/**
 * OpenTHC Chat Configuration
 */

$cfg = [];

$cfg['database'] = [
	'chat' => [
		'hostname' => '127.0.0.01',
		'username' => 'openthc_chat',
		'password' => 'openthc_chat',
		'database' => 'openthc_chat',
		'dsn' => 'pgsql:host=127.0.0.01;user=openthc_chat;password=openthc_chat;dbname=openthc_chat',
	],
];

// OpenTHC Services
$cfg['openthc'] = [
	'chat' => [
		'id' => '',
		'origin' => 'https://chat.openthc.example.com',
		'public' => '/* Values from Auth Database */',
		'secret' => '/* Values from Auth Database */',
	],
	'sso' => [
		'origin' => 'https://sso.openthc.example.com',
		'public' => '/* Values from Auth Database */',
		'client-pk' => '/* Values from Auth Database */',
		'client-sk' => '/* Values from Auth Database */',
	]
];

$cfg['mattermost'] = [
	'origin' => 'https://chat.openthc.example.com',
	'root-id' => '7yy7o4p59if4bfkmokfcury7th',
	'root-username' => 'root@chat.openthc.example.com',
	'root-password' => '',
	'root-sk' => '/* The Token */',
	'team_default' => '/* ID */',
	// 'channel'
	// 'company'
	// 'forum'
];

return $cfg;
