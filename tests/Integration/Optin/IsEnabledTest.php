<?php
declare(strict_types=1);

namespace WPMedia\Mixpanel\Tests\Integration\Optin;

use WPMedia\Mixpanel\Optin;
use WPMedia\Mixpanel\Tests\Integration\TestCase;

/**
 * Test the Optin::is_enabled method.
 *
 * @covers WPMedia\Mixpanel\Optin::is_enabled
 */
class IsEnabledTest extends TestCase {
	/**
	 * Test method
	 *
	 * @dataProvider configTestData
	 *
	 * @param string $user_role    The user role to test.
	 * @param bool   $option_value The option value to test.
	 * @param bool   $expected     The expected result.
	 *
	 * @return void
	 */
	public function testShouldReturnExpected( $user_role, $option_value, $expected ): void {
		$user = self::factory()->user->create( [ 'role' => $user_role ] );

		if ( $user instanceof \WP_Error ) {
			$this->fail( 'Failed to create user' );
		}

		wp_set_current_user( $user );

		update_option( 'mixpanel_mixpanel_optin', $option_value );

		$optin = new Optin( 'mixpanel', 'manage_options' );

		$this->assertSame( $expected, $optin->is_enabled() );
	}
}
