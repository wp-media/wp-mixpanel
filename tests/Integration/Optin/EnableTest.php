<?php
declare(strict_types=1);

namespace WPMedia\Mixpanel\Tests\Integration\Optin;

use WPMedia\Mixpanel\Optin;
use WPMedia\Mixpanel\Tests\Integration\TestCase;

/**
 * @covers WPMedia\Mixpanel\Optin::enable
 */
class EnableTest extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $user_role, $option_value, $expected ) {
		$user = self::factory()->user->create( [ 'role' => $user_role ] );

		wp_set_current_user( $user );

		update_option( 'mixpanel_mixpanel_optin', $option_value );

		$this->assertSame( $option_value, get_option( 'mixpanel_mixpanel_optin' ) );

		$optin = new Optin( 'mixpanel', 'manage_options' );

		$optin->enable();

		$this->assertSame( $expected, get_option( 'mixpanel_mixpanel_optin' ) );
	}
}
