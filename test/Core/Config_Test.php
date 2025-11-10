<?php
/**
 * Base Class for API Testing
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Chat\Test\Core;

class Config_Test extends \OpenTHC\Chat\Test\Base
{
	/**
	 * ack -o 'getenv\(.+\)'  test | cut -d':' -f2|sort |uniq -c | sort -nr
	 */
	function test_env()
	{
		$x = ini_get('variables_order');
		$this->assertFalse(strpos($x, 'E'));
		// if (strpos($x, 'E') === false) {
		// 	echo "AUTOLOADING ENV into \$_ENV == FALSE\n";
		// } else {
		// 	echo "AUTOLOADING ENV into \$_ENV == TRUE\n";
		// }
		// var_dump(ini_get('variables_order'));

		// ksort($_ENV);
		// var_dump($_ENV);

		// $env = getenv();
		// ksort($env);
		// var_dump($env);

		// ksort($_SERVER);
		// var_dump($_SERVER);

		$env_list = [
			'OPENTHC_TEST_ORIGIN',
			'OPENTHC_TEST_PUBLIC_TEAM_ID',
			// 'OPENTHC_TEST_HTTP_DEBUG',
			// 'OPENTHC_TEST_WEBDRIVER_URL',
			// 'OPENTHC_TEST_CONTACT_A',
			// 'OPENTHC_TEST_CONTACT_B',
			// 'OPENTHC_TEST_CONTACT_C',
			// 'OPENTHC_TEST_CONTACT_PASSWORD',
			// 'OPENTHC_TEST_CONTACT_PHONE',
		];

		foreach ($env_list as $x) {
			$this->assertArrayHasKey($x, $_ENV);
			$this->assertNotEmpty($_ENV[$x], sprintf('$_ENV missing "%s"', $x));
			// $this->assertNotEmpty(getenv($x), sprintf('getenv missing "%s"', $x));
			// $this->assertNotEmpty(constant($x), sprintf('Constant missing "%s"', $x));
		}

	}

	/**
	 *
	 */
	function test_config()
	{
		$key_list = [
			'database/chat/dsn',

			'redis/hostname',

			'openthc/chat/id',
			'openthc/chat/origin',

			'openthc/sso/origin',
			'openthc/sso/public',
			'openthc/sso/client-id',
			'openthc/sso/client-pk',
			'openthc/sso/client-sk',

			'mattermost',
			'mattermost/root-sk',

		];

		foreach ($key_list as $key) {
			$chk = \OpenTHC\Config::get($key);
			$this->assertNotEmpty($chk, sprintf('Key: "%s" is empty', $key));
		}

	}

	function test_mattermost_config()
	{
		$f = sprintf('%s/mattermost/config/config.json', APP_ROOT);
		$this->assertTrue(is_file($f));

		$cfg = file_get_contents($f);
		$cfg = json_decode($cfg);
		$this->assertIsObject($cfg);

		$section = $cfg->ServiceSettings;
		$this->assertObjectHasProperty('SiteURL', $section);
		$this->assertObjectHasProperty('ListenAddress', $section);

		$this->assertObjectHasProperty('EnableOAuthServiceProvider', $section);
		$this->assertFalse($section->EnableOAuthServiceProvider);

		$this->assertObjectHasProperty('EnableCommands', $section);
		$this->assertFalse($section->EnableCommands);

		$this->assertObjectHasProperty('EnableSecurityFixAlert', $section);
		$this->assertFalse($section->EnableSecurityFixAlert);

		$this->assertObjectHasProperty('EnableGifPicker', $section);
		$this->assertFalse($section->EnableGifPicker);

		$this->assertObjectHasProperty('EnableLocalMode', $section);
		$this->assertTrue($section->EnableLocalMode);

		$this->assertObjectHasProperty('TeamSettings', $cfg);

		$this->assertFalse($cfg->TeamSettings->EnableJoinLeaveMessageByDefault);
		$this->assertFalse($cfg->TeamSettings->EnableCustomUserStatuses);
		// cfg->TeamSettings->CustomDescriptionText

		$this->assertFalse($cfg->PasswordSettings->EnableForgotLink);

		$this->assertFalse($cfg->EmailSettings->EnableSignInWithUsername);
		$this->assertFalse($cfg->PrivacySettings->ShowEmailAddress);

		// $this->assertFalse($cfg->SupportSettings->

		$this->assertFalse($cfg->PluginSettings->Enable);
		$this->assertFalse($cfg->PluginSettings->EnableMarketplace);
		$this->assertTrue($cfg->PluginSettings->RequirePluginSignature);
	}

}
