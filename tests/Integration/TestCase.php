<?php
declare(strict_types=1);

namespace WPMedia\Mixpanel\Tests\Integration;

use ReflectionObject;
use WPMedia\PHPUnit\Integration\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
	/**
	 * Configuration for the test data.
	 *
	 * @var array<string, mixed>
	 */
	protected $config;

	/**
	 * Setup method for the test case.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		if ( empty( $this->config ) ) {
			$this->loadTestDataConfig();
		}
	}

	/**
	 * Get the test data configuration.
	 *
	 * @return array<string, mixed>
	 */
	public function configTestData(): array {
		if ( empty( $this->config ) ) {
			$this->loadTestDataConfig();
		}

		$test_data = $this->config['test_data'] ?? $this->config;

		return is_array( $test_data ) ? $test_data : [];
	}

	/**
	 * Load test data configuration.
	 *
	 * @return void
	 */
	protected function loadTestDataConfig(): void {
		$obj      = new ReflectionObject( $this );
		$filename = $obj->getFileName();

		if ( false === $filename ) {
			return;
		}

		$this->config = $this->getTestData( dirname( $filename ), basename( $filename, '.php' ) );
	}
}
