# WP Mixpanel

A library for integrating Mixpanel analytics into WordPress projects of Group.One.

## Overview

WP Mixpanel provides seamless integration between WordPress and Mixpanel, allowing you to track events in your WordPress projects.

## Installation

### Via Composer

Add the package to your project using Composer:

```bash
composer require wp-media/wp-mixpanel
```

## Configuration

The library is composed of 3 main classes:
- `Optin` handles the status of the opt-in for analytics
- `Tracking` is the base class for interaction with Mixpanel
- `TrackingPlugin` extends the `Tracking` class with some specific configuration for usage in WordPress plugins

### Initialize the `Tracking` class alone

```php
new WPMedia\Mixpanel\Tracking( $mixpanel_token, $options = [] );

```

The `$mixpanel_token` is the token provided by Mixpanel corresponding to the project you want to send data to.

For Group.One, we have a sandbox project and a production project.

The `$options` parameter is an optional array which can be used to configure further the Mixpanel PHP library configuration.

### Initialize the `TrackingPlugin` in a WordPress plugin

```php
new WPMedia\Mixpanel\TrackingPlugin( $mixpanel_token, $plugin, $brand = '', $product = '' );
```

- The `$plugin` is the plugin name + the version
- The `$brand` and `$product` are optional, but should be specified based on the analytics requirements of the plugin you implement the library in.

### Initialize the Optin class

```php
new WPMedia\Mixpanel\Optin( $plugin_slug, $capability );
```

- The `$plugin_slug` is the slug used in your plugin as the prefix for options
- The `$capability` is the capability required to be able to modify the value of the optin

## Usage Examples

### Track a simple event without optin

```php
$tracking->identify( $user_id );
$tracking->track( 'Event Name', $properties );
```

Calling `identify()` is required to associate sent events with a user ID. The `$user_id` provided is automatically hashed with the appropriate algorithm.

The `track()` method takes 2 required arguments:
- The `$event_name` corresponding to the event name which should be displayed in Mixpanel. Events use Start Case formatting.
- The `$properties` is an array of properties to associate with the event in the format `property_name => value`. Properties use small caps formatting and underscores.

### Track an event with optin check in a plugin

```php
if ( ! $optin->is_enabled() ) {
    return;
}

$tracking_plugin->identify( $user_id );
$tracking_plugin->track( 'Event Name', $properties );
```

The `track()` method of the `TrackingPlugin` class will automatically associated the following properties to the event:
- `domain`: hashed value of the current hostname
- `wp_version`: current WP version
- `php_version`: current PHP version
- `plugin`: Plugin name and version (set in constructor)
- `brand`: Brand name (set in constructor)
- `product`: Product name (set in constructor)
