<?php
/**
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Chat\Test;

class User_Update_Test extends \OpenTHC\Chat\Test\Base
{
	/**
	 * @depends OpenTHC\Chat\Test\Root_Signin_Test::test_signin
	 * @depends OpenTHC\Chat\Test\User_Create_Test::test_create
	 */
	function test_update($tok, $Contact)
	{
		$cfg = \OpenTHC\Config::get('mattermost');

		$jar = new \GuzzleHttp\Cookie\CookieJar();
		$ghc = new \GuzzleHttp\Client([
			'base_uri' => sprintf('%s/api/v4/', $cfg['origin']),
			'allow_redirects' => false,
			'connect_timeout' => 4.20,
			'http_errors' => false,
			'cookies' => $jar,
			'headers' => [
				'authorization' => sprintf('Bearer %s', $tok)
			]
		]);

		// Search User
		$url = sprintf('users/email/%s', $Contact['email']);
		$res = $ghc->get($url);
		$res = $this->assertValidResponse($res);
		$this->assertEquals($Contact['email'], $res['email']);
		$Contact['id'] = $res['id'];
		$Contact['password'] = str_shuffle('passweed');
		$Contact['password_hash'] = password_hash($Contact['password'], PASSWORD_BCRYPT);

		$dbc = _dbc();

		$db_contact0 = $dbc->fetchRow('SELECT * FROM users WHERE id = :ct0', [ ':ct0' => $Contact['id'] ]);
		// var_dump($db_contact0['password']);

		// Update Password
		// This says OK but then we cannot log in?
		$url = sprintf('users/%s/password', $Contact['id']);
		$res = $ghc->put($url, [ 'json' => [
			'already_hashed' => "true",
			'new_password' => $Contact['password_hash'],
		]]);
		$res = $this->assertValidResponse($res);

		$db_contact1 = $dbc->fetchRow('SELECT * FROM users WHERE id = :ct0', [ ':ct0' => $Contact['id'] ]);
		// var_dump($db_contact1['password']);

		$this->assertEquals($db_contact1['password'], $Contact['password_hash']);

		return $Contact;

	}

	/**
	 * @depends @depends OpenTHC\Chat\Test\Root_Signin_Test::test_signin
	 * @depends test_update
	 */
	function test_signin($tok, $Contact)
	{
		// var_dump($Contact);
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

		return $Contact;

	}

}
