<?php
/**
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Chat\Test;

class Root_Signin_Test extends \OpenTHC\Chat\Test\Base
{
	/**
	 *
	 */
	function test_signin() : string
	{
		$cfg = \OpenTHC\Config::get('mattermost');
		$this->assertNotEmpty($cfg['root-username']);
		$this->assertNotEmpty($cfg['root-password']);

		// Client
		$ghc = $this->_client();

		// Login as Admin
		$res = $ghc->post('users/login', [ 'json' => [
			'login_id' => $cfg['root-username'],
			'password' => $cfg['root-password'],
		]]);

		$tmp = $this->assertValidResponse($res);
		$this->assertIsArray($tmp);
		$this->assertArrayHasKey('id', $tmp);
		$this->assertArrayHasKey('username', $tmp);

		$tok = $res->getHeaderLine('token');
		$this->assertNotEmpty($tok);

		return $tok;

	}

}
