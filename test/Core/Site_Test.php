<?php
/**
 * Test the Pages
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Chat\Test\Core;

class Site_Test extends \OpenTHC\Chat\Test\Base
{
	protected $type_expect = 'text/html';

	function test_page_all()
	{
		$this->assertIsString($_ENV['OPENTHC_TEST_ORIGIN']);
		$this->assertMatchesRegularExpression('/\w+\.\w{2,256}\.\w{2,16}$/', $_ENV['OPENTHC_TEST_ORIGIN']);

		$ghc = new \GuzzleHttp\Client([
			'base_uri' => $_ENV['OPENTHC_TEST_ORIGIN'],
			'allow_redirects' => false,
			'connect_timeout' => 4.20,
			'http_errors' => false,
			'cookies' => true,
		]);

		$res = $ghc->get('/');
		$this->assertValidResponse($res);

		// $res = $ghc->get('/auth/open');
		// $res = $this->assertValidResponse($res);

		// $res = $ghc->get('/auth/open');
		// $res = $this->assertValidResponse($res);

		// $res = $ghc->get('/auth/open');
		// $res = $this->assertValidResponse($res);

		// $res = $ghc->get('/.well-known/change-password');

		// // workaround because of empty mime type and
		// // cannot pass empty to assertValidResponse
		// // @todo change assertValidResponse to allow an empty mime-type?
		// // Perhaps a special case of '*' ?
		// $tmp = $this->type_expect;
		// $this->type_expect = '';
		// $this->assertValidResponse($res, 302);
		// $this->type_expect = $tmp;

		// $res = $ghc->get('/auth/open?a=password-reset');
		// $this->assertValidResponse($res);

		// $res = $ghc->get('/account/create');
		// $this->assertValidResponse($res);

		// $res = $ghc->get('/auth/shut');
		// $this->assertValidResponse($res);

		// $res = $ghc->get('/done');
		// $this->assertValidResponse($res);

	}

}
