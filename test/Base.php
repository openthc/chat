<?php
/**
 * Base Class for API Testing
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Chat\Test;

class Base extends \OpenTHC\Test\Base
{
	protected $_pid = null;

	/**
	 *
	 */
	function __construct($name = null, array $data = [], $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->_pid = getmypid();
	}

	/**
	 *
	 */
	function _client(string $tok = '')
	{
		$cfg = \OpenTHC\Config::get('mattermost');

		$jar = new \GuzzleHttp\Cookie\CookieJar();
		$arg = [
			'base_uri' => sprintf('%s/api/v4/', $cfg['origin']),
			'allow_redirects' => false,
			'connect_timeout' => 4.20,
			'http_errors' => false,
			'cookies' => $jar,
		];
		if ( ! empty($tok)) {
			$arg['headers'] = [
				'authorization' => sprintf('Bearer %s', $tok)
			];
		}

		$ghc = new \GuzzleHttp\Client($arg);

		return $ghc;

	}

}
