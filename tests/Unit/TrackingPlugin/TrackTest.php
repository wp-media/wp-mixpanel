<?php
declare(strict_types=1);

namespace WPMedia\Mixpanel\Tests\Unit\TrackingPlugin;

use Brain\Monkey\{Filters, Functions};
use WPMedia\Mixpanel\Tests\Unit\TestCase;
use WPMedia\Mixpanel\TrackingPlugin;

/**
 * Test the track method of the TrackingPlugin class.
 *
 * @covers WPMedia\Mixpanel\TrackingPlugin::track
 *
 * @group TrackingPlugin
 */
class TrackTest extends TestCase {
	/**
	 * TrackingPlugin instance
	 *
	 * @var TrackingPlugin
	 */
	private $tracking_plugin;

	/**
	 * Set up the test case
	 *
	 * @return void
	 */
	protected function set_up() {
		parent::set_up();

		$this->tracking_plugin = $this->getMockBuilder( TrackingPlugin::class )
			->setConstructorArgs( [ 'test_token', 'test_plugin', 'test_brand', 'test_app' ] )
			->onlyMethods( [ 'track_event_with_parent', 'hash', 'get_wp_version', 'get_php_version' ] )
			->getMock();
	}

	/**
	 * Test the track method of the TrackingPlugin class.
	 *
	 * @dataProvider configTestData
	 *
	 * @param string[] $config Configuration for the test.
	 * @param string[] $expected Expected values for the test.
	 *
	 * @return void
	 */
	public function testShouldDoExpected( $config, $expected ) {
		if ( $config['bypass_capability'] ) {
			Filters\expectApplied( 'wp_mixpanel_event_capability' )
				->never();

			Functions\expect( 'current_user_can' )
				->never();

			Functions\expect( 'wp_parse_url' )
				->once()
				->andReturn( 'example.org' );

			Functions\expect( 'get_home_url' )
				->once()
				->andReturn( 'example.org' );

			$this->tracking_plugin->expects( $this->once() )
				->method( 'track_event_with_parent' )
				->with(
					'event_name',
					$expected['properties']
				);
		} else {
			if ( $config['filter_value'] ) {
				Filters\expectApplied( 'wp_mixpanel_event_capability' )
					->once()
					->andReturn( $config['filter_value'] );
			}

			Functions\expect( 'current_user_can' )
				->once()
				->with( $expected['capability'] )
				->andReturn( $config['user_can'] );
		}

		$this->tracking_plugin->track( 'event_name', [ 'key' => 'value' ], $config['event_capability'], $config['bypass_capability'] );
	}
}
