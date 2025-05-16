<?php
declare(strict_types=1);

namespace WPMedia\Mixpanel\Tests\Integration\Optin;

use WPMedia\Mixpanel\Optin;
use WPMedia\Mixpanel\Tests\Integration\TestCase;

/**
 * @covers WPMedia\Mixpanel\Optin::disable
 */
class DisableTest extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldEnableOptin( $user_role, $option_value, $expected ) {
		$user = self::factory()->user->create( [ 'role' => $user_role ] );

		wp_set_current_user( $user );

		update_option( 'mixpanel_mixpanel_optin', $option_value );

		$optin = new Optin( 'mixpanel', 'manage_options' );

		$optin->disable();

		$this->assertSame( $expected, get_option( 'mixpanel_mixpanel_optin' ) );
	}
}
