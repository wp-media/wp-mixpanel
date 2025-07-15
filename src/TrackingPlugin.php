<?php
declare(strict_types=1);

namespace WPMedia\Mixpanel;

class TrackingPlugin extends Tracking {
	/**
	 * Mixpanel token
	 *
	 * @var string
	 */
	private $mixpanel_token;

	/**
	 * Plugin name & version
	 *
	 * @var string
	 */
	private $plugin;

	/**
	 * Plugin slug
	 *
	 * @var string
	 */
	private $plugin_slug;

	/**
	 * Brand name
	 *
	 * @var string
	 */
	private $brand;

	/**
	 * Product name
	 *
	 * @var string
	 */
	private $product;

	/**
	 * Constructor
	 *
	 * @param string $mixpanel_token Mixpanel token.
	 * @param string $plugin         Plugin name.
	 * @param string $plugin_slug    Plugin slug.
	 * @param string $brand          Brand name.
	 * @param string $product        Product name.
	 */
	public function __construct( string $mixpanel_token, string $plugin, string $plugin_slug, string $brand, string $product ) {
		$options = [
			'consumer'  => 'wp',
			'consumers' => [
				'wp' => 'WPMedia\\Mixpanel\\WPConsumer',
			],
		];

		parent::__construct( $mixpanel_token, $brand, $product, $options );

		$this->plugin         = $plugin;
		$this->mixpanel_token = $mixpanel_token;
		$this->plugin_slug    = $plugin_slug;
		$this->brand          = $brand;
		$this->product        = $product;
	}

	/**
	 * Register hooks for tracking global events
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( $this->plugin_slug . '_mixpanel_optin_changed', [ $this, 'track_optin' ] );
	}

	/**
	 * Track an event in Mixpanel with plugin context
	 *
	 * @param string  $event      Event name.
	 * @param mixed[] $properties Event properties.
	 */
	public function track( string $event, array $properties ): void {
		$host = wp_parse_url( get_home_url(), PHP_URL_HOST );

		if ( ! $host ) {
			$host = '';
		}

		$defaults = [
			'domain'      => $this->hash( $host ),
			'wp_version'  => $this->get_wp_version(),
			'php_version' => $this->get_php_version(),
			'plugin'      => $this->plugin,
			'brand'       => $this->brand,
			'product'     => $this->product,
		];

		$properties = array_merge( $properties, $defaults );

		parent::track( $event, $properties );
	}

	/**
	 * Get the Mixpanel token
	 *
	 * @return string
	 */
	public function get_token(): string {
		return $this->mixpanel_token;
	}

	/**
	 * Track opt-in status change in Mixpanel
	 *
	 * @param bool $value Opt-in status.
	 *
	 * @return void
	 */
	public function track_optin( $value ): void {
		$this->track(
			'WordPress Plugin Data Consent Changed',
			[
				'opt_in_status' => $value,
			]
		);
	}
}
