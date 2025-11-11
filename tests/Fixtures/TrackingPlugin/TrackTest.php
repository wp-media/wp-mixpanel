<?php

return [
	'testShouldDoNothingIfNoCapability'                    => [
		'config'   => [
			'filter_value'      => null,
			'capability'        => 'manage_options',
			'user_can'          => false,
			'event_capability'  => '',
			'bypass_capability' => false,
		],
		'expected' => [
			'capability' => 'manage_options',
		],
	],
	'testShouldDoNothingIfNoCapabilityWithFilter'          => [
		'config'   => [
			'filter_value'      => 'edit_posts',
			'capability'        => 'edit_posts',
			'user_can'          => false,
			'event_capability'  => '',
			'bypass_capability' => false,
		],
		'expected' => [
			'capability' => 'edit_posts',
		],
	],
	'testShouldDoNothingIfNoCapabilityWithEventCapability' => [
		'config'   => [
			'filter_value'      => null,
			'capability'        => 'manage_options',
			'user_can'          => false,
			'event_capability'  => 'edit_posts',
			'bypass_capability' => false,
		],
		'expected' => [
			'capability' => 'edit_posts',
		],
	],
	'testShouldBypassCapabilityCheckWithBypassSetToTrue'   => [
		'config'   => [
			'bypass_capability' => true,
		],
		'expected' => [],
	],
];
