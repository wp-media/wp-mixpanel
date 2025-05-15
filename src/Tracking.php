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
	 * @param string $user_id User ID.
	 */
	public function __construct( string $mixpanel_token, string $user_id ) {
		$this->mixpanel = Mixpanel::getInstance(
			$mixpanel_token,
			[
				'host'            => 'api-eu.mixpanel.com',
				'events_endpoint' => '/track/?ip=0',
			]
		);
		$this->user_id  = hash( 'sha3-224', $user_id );

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

	/**
	 * Get the WordPress version
	 *
	 * @return string
	 */
	public function get_wp_version(): string {
		$version = preg_replace( '@^(\d\.\d+).*@', '\1', get_bloginfo( 'version' ) );

		if ( null === $version ) {
			$version = '0.0';
		}

		return $version;
	}

	/**
	 * Get the PHP version
	 *
	 * @return string
	 */
	public function get_php_version(): string {
		$version = preg_replace( '@^(\d\.\d+).*@', '\1', phpversion() );

		if ( null === $version ) {
			$version = '0.0';
		}

		return $version;
	}

	/**
	 * Get the active theme
	 *
	 * @return string
	 */
	public function get_current_theme(): string {
		$theme = wp_get_theme();

		return $theme->get( 'Name' );
	}

	/**
	 * Get list of active plugins names
	 *
	 * @return string[]
	 */
	public function get_active_plugins(): array {
		$plugins        = [];
		$active_plugins = get_option( 'active_plugins' );
		$all_plugins    = get_plugins();

		foreach ( $active_plugins as $plugin_path ) {
			if ( ! isset( $all_plugins[ $plugin_path ] ) ) {
				continue;
			}

			$plugins[] = $all_plugins[ $plugin_path ]['Name'];
		}

		return $plugins;
	}
}
