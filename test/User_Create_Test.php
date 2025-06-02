<?php
/**
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Chat\Test;

class User_Create_Test extends \OpenTHC\Chat\Test\Base
{
	/**
	 * @depends OpenTHC\Chat\Test\Root_Signin_Test::test_signin
	 */
	function test_create($tok)
	{
		$ghc = $this->_client($tok);

		$Chat_Contact = [];
		$Chat_Contact['email'] = sprintf('test+%s@openthc.dev', _ulid());
		$Chat_Contact['email'] = strtolower($Chat_Contact['email']);
		$Chat_Contact['username'] = strtok($Chat_Contact['email'], '@');
		$Chat_Contact['username'] = preg_replace('/[^\w\.\-]+/', '', $Chat_Contact['username']);
		$Chat_Contact['password'] = _ulid();
		// var_dump($Chat_Contact);

		// Search User
		$url = sprintf('users/email/%s', $Chat_Contact['email']);
		// var_dump($url);
		$res = $ghc->get($url);
		$res = $this->assertValidResponse($res, 404);
		// var_dump($res);

		// Create the User
		// 	$arg = [
		// 		'json' => [
		// 			'email' => $Chat_Contact['email'],
		// 			'username' => $Chat_Contact['username'],
		// 			'password' => $Chat_Contact['password'],
		// 			// 'auth_data' => $Contact['id'],
		// 			// 'auth_service' => 'email',
		// 			// 'notify_props' => [
		// 			// 	'email' => false,
		// 			// 	'desktop' => 'all',
		// 			// ]
		// 		]
		// 	];
		// 	// var_dump($arg);

		$res = $ghc->post('users', [ 'json' => $Chat_Contact ]);
		$res = $this->assertValidResponse($res, 201);
		// var_dump($res);

		// 	$res_code = $res->getStatusCode();
		// 	$res_body = $res->getBody()->getContents();
		// 	$res_data = json_decode($res_body, true);
		// 	switch ($res_code) {
		// 	case 201:
		// 		$Chat_Contact = $res_data;
		// 		break;
		// 	default:
		// 		echo "FAILED:\n";
		// 		var_dump($res_data);
		// 		exit(0);
		// 	}

		// 	$Chat_Contact = $res_data;
		// }

		return $Chat_Contact;

	}

	/**
	 * @depends test_create
	 */
	function test_signin($Contact)
	{
		$ghc = $this->_client();

		// Sign-In as User
		$res = $ghc->post('users/login', [ 'json' => [
			'login_id' => $Contact['email'],
			'password' => $Contact['password'],
		]]);

		$tmp = $this->assertValidResponse($res);
		$this->assertIsArray($tmp);
		$this->assertArrayHasKey('id', $tmp);
		$this->assertArrayHasKey('username', $tmp);

		$tok = $res->getHeaderLine('token');
		$this->assertNotEmpty($tok);

	}
}
