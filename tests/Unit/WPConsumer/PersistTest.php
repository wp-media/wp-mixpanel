<?php
declare(strict_types=1);

namespace WPMedia\Mixpanel\Tests\Unit\WPConsumer;

use Brain\Monkey\{Filters, Functions};
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
	 * @param array{timeout_option: int|float|null, batch: mixed[], timeout_filter: mixed, blocking_filter: bool|null, is_error: bool} $config Configuration for the test.
	 * @param array{sent: bool, timeout: int|float, blocking: bool}                                                                    $expected Expected values for the test.
	 *
	 * @return void
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$options = [
			'host'     => self::HOST,
			'endpoint' => self::ENDPOINT,
			'use_ssl'  => true,
		];

		if ( null !== $config['timeout_option'] ) {
			$options['timeout'] = $config['timeout_option'];
		}

		$consumer = new WPConsumer( $options );

		if ( ! $expected['sent'] ) {
			Filters\expectApplied( 'wp_media_mixpanel_request_timeout' )->never();
			Filters\expectApplied( 'wp_media_mixpanel_request_blocking' )->never();
			Functions\expect( 'wp_remote_post' )->never();

			$this->assertTrue( $consumer->persist( $config['batch'] ) );

			return;
		}

		$default_timeout = null !== $config['timeout_option'] ? $config['timeout_option'] : WPConsumer::DEFAULT_TIMEOUT;

		Filters\expectApplied( 'wp_media_mixpanel_request_timeout' )
			->once()
			->with( $default_timeout, self::HOST )
			->andReturn( null !== $config['timeout_filter'] ? $config['timeout_filter'] : $default_timeout );

		Filters\expectApplied( 'wp_media_mixpanel_request_blocking' )
			->once()
			->with( false, self::HOST )
			->andReturn( null !== $config['blocking_filter'] ? $config['blocking_filter'] : false );

		Functions\expect( 'is_wp_error' )
			->once()
			->andReturn( $config['is_error'] );

		Functions\expect( 'wp_remote_post' )
			->once()
			->with(
				'https://' . self::HOST . self::ENDPOINT,
				[
					'timeout'  => $expected['timeout'],
					'blocking' => $expected['blocking'],
					// Mirrors AbstractConsumer::_encode(), which base64-encodes the JSON payload.
					'body'     => 'data=' . base64_encode( (string) json_encode( $config['batch'] ) ), // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode, WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				]
			)
			->andReturn( $config['is_error'] ? $this->createErrorResponse() : [ 'response' => [ 'code' => false ] ] );

		// A failed request must still report the batch as consumed, otherwise the producer
		// re-queues it and retries the flush up to 10 times at shutdown, once per producer.
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
