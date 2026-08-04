<?php
declare(strict_types=1);

namespace WPMedia\Mixpanel\Tests\Unit\WPConsumer;

use Brain\Monkey\Functions;
use Mockery;
use Mockery\MockInterface;
use WPMedia\Mixpanel\Tests\Unit\TestCase;
use WPMedia\Mixpanel\WPConsumer;

/**
 * Test the persist method of the WPConsumer class.
 *
 * @covers WPMedia\Mixpanel\WPConsumer::persist
 *
 * @group WPConsumer
 */
class PersistTest extends TestCase {
	/**
	 * Host used for the requests under test.
	 *
	 * @var string
	 */
	private const HOST = 'tracking.example.org';

	/**
	 * Endpoint used for the requests under test.
	 *
	 * @var string
	 */
	private const ENDPOINT = '/track';

	/**
	 * Test the persist method of the WPConsumer class.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array{batch: mixed[], is_error: bool} $config Configuration for the test.
	 * @param array{sent: bool}                     $expected Expected values for the test.
	 *
	 * @return void
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$consumer = new WPConsumer(
			[
				'host'     => self::HOST,
				'endpoint' => self::ENDPOINT,
				'use_ssl'  => true,
			]
		);

		if ( ! $expected['sent'] ) {
			Functions\expect( 'wp_remote_post' )->never();

			$this->assertTrue( $consumer->persist( $config['batch'] ) );

			return;
		}

		Functions\expect( 'is_wp_error' )
			->once()
			->andReturn( $config['is_error'] );

		// The timeout and blocking values are fixed, neither filterable nor configurable.
		Functions\expect( 'wp_remote_post' )
			->once()
			->with(
				'https://' . self::HOST . self::ENDPOINT,
				[
					'timeout'  => WPConsumer::REQUEST_TIMEOUT,
					'blocking' => false,
					// Mirrors AbstractConsumer::_encode().
					'body'     => 'data=' . base64_encode( (string) json_encode( $config['batch'] ) ), // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode, WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				]
			)
			->andReturn( $config['is_error'] ? $this->createErrorResponse() : [ 'response' => [ 'code' => false ] ] );

		// A failed request must still report the batch as consumed, or the producer
		// re-queues it and retries the flush up to 10 times at shutdown.
		$this->assertTrue( $consumer->persist( $config['batch'] ) );
	}

	/**
	 * Create a WP_Error test double.
	 *
	 * @return MockInterface
	 */
	private function createErrorResponse(): MockInterface {
		$error = Mockery::mock( 'WP_Error' );

		$error->shouldReceive( 'get_error_code' )
			->once()
			->andReturn( 'http_request_failed' );

		$error->shouldReceive( 'get_error_message' )
			->once()
			->andReturn( 'Operation timed out' );

		return $error;
	}
}
