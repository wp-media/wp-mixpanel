<?php
declare(strict_types=1);

namespace WPMedia\Mixpanel;

use WPMedia_ConsumerStrategies_AbstractConsumer;

/**
 * Consumes messages and sends them to a host/endpoint using WordPress's HTTP API
 */
class WPConsumer extends WPMedia_ConsumerStrategies_AbstractConsumer {

	/**
	 * Default number of seconds to allow the request to execute.
	 *
	 * This is also the lowest value WordPress can honour: Requests clamps the cURL
	 * timeout to a minimum of 1 second, because cURL's system DNS resolver uses
	 * alarm(), which only has second resolution. Passing a smaller value has no
	 * effect on the actual request.
	 *
	 * @var int
	 */
	const DEFAULT_TIMEOUT = 1;

	/**
	 * The host to connect to (e.g. api.mixpanel.com)
	 *
	 * @var string
	 */
	protected $host;

	/**
	 * The host-relative endpoint to write to (e.g. /engage)
	 *
	 * @var string
	 */
	protected $endpoint;

	/**
	 * The maximum number of seconds to allow the call to execute.
	 *
	 * @var int|float
	 */
	protected $timeout;

	/**
	 * Whether the request waits for a response.
	 *
	 * @var bool
	 */
	protected $blocking;

	/**
	 * The protocol to use for the cURL connection
	 *
	 * @var string
	 */
	protected $protocol;

	/**
	 * Creates a new WPConsumer and assigns properties from the $options array
	 *
	 * @param array{host:string, endpoint:string, timeout?: int|float, blocking?: bool, use_ssl?: bool} $options Options for the consumer.
	 */
	public function __construct( $options ) {
		parent::__construct( $options );

		$this->host     = $options['host'];
		$this->endpoint = $options['endpoint'];
		$this->timeout  = isset( $options['timeout'] ) ? $options['timeout'] : self::DEFAULT_TIMEOUT;
		$this->blocking = isset( $options['blocking'] ) ? (bool) $options['blocking'] : false;
		$this->protocol = isset( $options['use_ssl'] ) && ( true === $options['use_ssl'] ) ? 'https' : 'http';
	}

	/**
	 * Get the timeout, in seconds, to use for the request
	 *
	 * @return int|float
	 */
	protected function get_timeout() {
		/**
		 * Filters the timeout, in seconds, for analytics requests.
		 *
		 * Values below 1 have no effect: WordPress clamps the cURL timeout to a minimum
		 * of 1 second. Keep this value low, analytics must never delay a response.
		 *
		 * @param mixed  $timeout Request timeout in seconds, as an int or a float.
		 *                        Non-numeric values fall back to self::DEFAULT_TIMEOUT.
		 * @param string $host    Host the request is sent to.
		 */
		$timeout = apply_filters( 'wp_media_mixpanel_request_timeout', $this->timeout, $this->host );

		if ( is_int( $timeout ) || is_float( $timeout ) ) {
			return $timeout;
		}

		if ( is_numeric( $timeout ) ) {
			return (float) $timeout;
		}

		return self::DEFAULT_TIMEOUT;
	}

	/**
	 * Check whether the request should wait for a response
	 *
	 * @return bool
	 */
	protected function is_blocking(): bool {
		/**
		 * Filters whether analytics requests wait for a response.
		 *
		 * Note that a non-blocking request is not fire-and-forget: WordPress still waits
		 * for the endpoint up to the timeout, it only discards the response.
		 *
		 * @param bool   $blocking Whether the request waits for a response.
		 * @param string $host     Host the request is sent to.
		 */
		return (bool) apply_filters( 'wp_media_mixpanel_request_blocking', $this->blocking, $this->host );
	}

	/**
	 * Send post request to the given host/endpoint using WordPress's HTTP API
	 *
	 * @param mixed[] $batch Batch of data to send to mixpanel.
	 *
	 * @return bool
	 */
	public function persist( $batch ) {
		if ( count( $batch ) <= 0 ) {
			return true;
		}

		$url  = $this->protocol . '://' . $this->host . $this->endpoint;
		$data = 'data=' . $this->_encode( $batch );

		$response = wp_remote_post(
			$url,
			[
				'timeout'  => $this->get_timeout(),
				'blocking' => $this->is_blocking(),
				'body'     => $data,
			]
		);

		if ( is_wp_error( $response ) ) {
			$this->_handleError( $response->get_error_code(), $response->get_error_message() );
		}

		/*
		 * Always report the batch as consumed, even when the request failed.
		 *
		 * Analytics is non-essential, and returning false here is expensive: the producer
		 * puts the batch back on the queue and retries the flush up to 10 times from its
		 * destructor, once per producer. An unreachable endpoint would then cost up to
		 * 10 timeouts for each of the events, people and groups producers, at PHP
		 * shutdown, while the visitor is still waiting for the response.
		 *
		 * Dropping the batch keeps a degraded endpoint bounded to a single timeout.
		 * Failures are still reported through _handleError().
		 *
		 * @see \WPMedia_Producers_MixpanelBaseProducer::__destruct()
		 */
		return true;
	}
}
