<?php
declare(strict_types=1);

namespace WPMedia\Mixpanel\Tests\Integration\Optin;

use WPMedia\Mixpanel\Optin;
use WPMedia\Mixpanel\Tests\Integration\TestCase;

/**
 * @covers WPMedia\Mixpanel\Optin::is_enabled
 */
class IsEnabledTest extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $user_role, $option_value, $expected ) {
		$user = self::factory()->user->create( [ 'role' => $user_role ] );

		wp_set_current_user( $user );

		update_option( 'mixpanel_mixpanel_optin', $option_value );

		$optin = new Optin( 'mixpanel', 'manage_options' );

		$this->assertSame( $expected, $optin->is_enabled() );
	}
}