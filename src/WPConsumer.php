<?php
declare(strict_types=1);

namespace WPMedia\Mixpanel;

use WPMedia_ConsumerStrategies_AbstractConsumer;

/**
 * Consumes messages and sends them to a host/endpoint using WordPress's HTTP API
 */
class WPConsumer extends WPMedia_ConsumerStrategies_AbstractConsumer {

	/**
	 * Number of seconds to allow the request to execute.
	 *
	 * Analytics must never delay a response, so this is deliberately not
	 * configurable. It is also the lowest value WordPress can honour: Requests clamps
	 * the cURL timeout to a minimum of 1 second, because cURL's system DNS resolver
	 * uses alarm(), which only has second resolution. A smaller value would have no
	 * effect on the actual request.
	 *
	 * @var int
	 */
	const REQUEST_TIMEOUT = 1;

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
	 * The protocol to use for the cURL connection
	 *
	 * @var string
	 */
	protected $protocol;

	/**
	 * Creates a new WPConsumer and assigns properties from the $options array
	 *
	 * The timeout and blocking behaviour of the request is fixed and cannot be set
	 * through the options.
	 *
	 * @param array{host: string, endpoint: string, use_ssl?: bool} $options Options for the consumer.
	 */
	public function __construct( $options ) {
		parent::__construct( $options );

		$this->host     = $options['host'];
		$this->endpoint = $options['endpoint'];
		$this->protocol = isset( $options['use_ssl'] ) && ( true === $options['use_ssl'] ) ? 'https' : 'http';
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

		/*
		 * Note that a non-blocking request is not fire-and-forget: WordPress still waits
		 * for the endpoint up to the timeout, it only skips reading and parsing the
		 * response. The timeout is what bounds the cost of a degraded endpoint.
		 */
		$response = wp_remote_post(
			$url,
			[
				'timeout'  => self::REQUEST_TIMEOUT,
				'blocking' => false,
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
