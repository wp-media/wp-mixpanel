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
new WPMedia\Mixpanel\TrackingPlugin( $mixpanel_token, $plugin, $brand = '', $app = '' );
```

- The `$plugin` is the plugin name + the version
- The `$brand` and `$app` are optional, but should be specified based on the analytics requirements of the plugin you implement the library in.

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
if ( ! $optin->can_track() ) {
    return;
}

$tracking_plugin->identify( $user_id );
$tracking_plugin->track( 'Event Name', $properties, $event_capability = '' );
```

The `track()` method of the `TrackingPlugin` class is a bit different than its parent:

First, it takes an additional optional parameter `$event_capability`. By default, the capability required for all events is `manage_options`. This can be changed in two different ways:
- Using the filter `wp_mixpanel_event_capability` to modify the value for all events
- Passing the capability as the parameter `$event_capability`, to set it on a specific event

Second, the method will automatically associate the following properties to the event:
- `domain`: hashed value of the current hostname
- `wp_version`: current WP version
- `php_version`: current PHP version
- `plugin`: Plugin name and version (set in constructor)
- `brand`: Brand name (set in constructor)
- `application`: Application name (set in constructor)

### `wp_mixpanel_event_capability` Filter usage

The filter can takes 3 arguments:
- `$capability` the capability for all events
- `$event` the current event name
- `$app` the current app name

## Request behaviour

Events are sent with `wp_remote_post()` when the in-memory queue is flushed. Because analytics must never delay a response, requests are **non-blocking** and use a **1 second timeout** by default.

Two filters control this, both receiving the target host as their second argument:

- `wp_media_mixpanel_request_timeout` — the timeout in seconds. Non-numeric values fall back to the default.
- `wp_media_mixpanel_request_blocking` — whether the request waits for a response.

```php
add_filter( 'wp_media_mixpanel_request_timeout', function( $timeout, $host ) {
	return 2;
}, 10, 2 );
```

Two things are worth knowing before changing these:

- **1 second is a floor, not a default.** WordPress clamps the cURL timeout to a minimum of 1 second, because cURL's system DNS resolver uses `alarm()`, which only has second resolution. Passing a smaller value has no effect on the request.
- **Non-blocking is not fire-and-forget.** WordPress still waits for the endpoint up to the timeout; it only skips reading and parsing the response. The timeout is what bounds the cost of a degraded endpoint.

A failed request is reported through the consumer's error callback and then **dropped**. It is not retried, because the producer's retry-on-flush behaviour would multiply the cost of an unreachable endpoint across every request.

# Read more about MixPanel at group.one

More information about how MixPanel is used at group.one is available in [our internal documentation](https://group-one.atlassian.net/wiki/spaces/PA1/folder/33940931155?atlOrigin=eyJpIjoiZGNhYmI5MDMyZmZiNGY4MmIzOWZkNDNmZmY3ZjcyNDAiLCJwIjoiYyJ9). 
