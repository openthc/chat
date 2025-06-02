<?php
/**
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Chat\Test;

class User_Join_Team_Test extends \OpenTHC\Chat\Test\Base
{
	/**
	 * @depends OpenTHC\Chat\Test\Root_Signin_Test::test_signin
	 * @depends OpenTHC\Chat\Test\User_Update_Test::test_signin
	 * ---depends OpenTHC\Chat\Test\Team_Create_Test::test_create
	 */
	function test_user_join_team($tok, $Contact)
	{
		// $this->markTestSkipped('Not Implemented');
		$team = [];
		$team['id'] = $_ENV['OPENTHC_TEST_PUBLIC_TEAM_ID'];
		$team_name = 'public';

		$ghc = $this->_client($tok);

		// $res = $ghc->get('teams');
		// $res = $this->assertValidResponse($res);
		// // var_dump($res);
		// // $res = $res[0];
		// foreach ($res as $rec) {
		// 	echo $rec['id'];
		// 	echo ' ';
		// 	echo $rec['name'];
		// 	echo "\n";
		// }

		$res = $ghc->get(sprintf('teams/%s', $team['id']));
		$res = $this->assertValidResponse($res);

		// Add Member
		$url = sprintf('teams/%s/members', $team['id']);
		$arg = [
			'json' => [
				'team_id' => $team['id'],
				'user_id' => $Contact['id'],
			]

		];
		$res = $ghc->post($url, $arg);
		$res = $this->assertValidResponse($res, 201);
		// var_dump($res);

	}

}
