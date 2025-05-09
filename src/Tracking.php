<?php
declare(strict_types=1);

namespace WPMedia\Mixpanel;

use Mixpanel;

class Tracking {
	/**
	 * Mixpanel instance
	 *
	 * @var Mixpanel
	 */
	private $mixpanel;

	/**
	 * User ID for Mixpanel
	 *
	 * @var string
	 */
	private $user_id;

	/**
	 * Constructor
	 *
	 * @param string $mixpanel_token Mixpanel token.
	 */
	public function __construct( string $mixpanel_token ) {
		$this->mixpanel = Mixpanel::getInstance(
			$mixpanel_token,
			[
				'host'            => 'api-eu.mixpanel.com',
				'events_endpoint' => '/track/?ip=0',
			]
		);
		$this->user_id  = hash( 'sha3-224', home_url() );

		$this->mixpanel->identify( $this->user_id );
	}

	/**
	 * Track an event in Mixpanel
	 *
	 * @param string  $event Event name.
	 * @param mixed[] $properties Event properties.
	 */
	public function track( string $event, array $properties ): void {
		$this->mixpanel->track( $event, $properties );
	}

	/**
	 * Set a user property in Mixpanel
	 *
	 * @param string $property Property name.
	 * @param mixed  $value Property value.
	 */
	public function set_user_property( string $property, $value ): void {
		$this->mixpanel->people->set(
			$this->user_id,
			[
				$property => $value,
			],
			'0'
		);
	}
}
