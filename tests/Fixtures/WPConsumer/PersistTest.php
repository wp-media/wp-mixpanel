<?php

return [
	'testShouldNotSendRequestWhenBatchIsEmpty'           => [
		'config'   => [
			'timeout_option'  => null,
			'batch'           => [],
			'timeout_filter'  => null,
			'blocking_filter' => null,
			'is_error'        => false,
		],
		'expected' => [
			'sent'     => false,
			'timeout'  => 1,
			'blocking' => false,
		],
	],
	'testShouldSendNonBlockingRequestWithDefaultTimeout' => [
		'config'   => [
			'timeout_option'  => null,
			'batch'           => [ [ 'event' => 'Test' ] ],
			'timeout_filter'  => null,
			'blocking_filter' => null,
			'is_error'        => false,
		],
		'expected' => [
			'sent'     => true,
			'timeout'  => 1,
			'blocking' => false,
		],
	],
	'testShouldUseTimeoutFromOptions'                    => [
		'config'   => [
			'timeout_option'  => 3,
			'batch'           => [ [ 'event' => 'Test' ] ],
			'timeout_filter'  => null,
			'blocking_filter' => null,
			'is_error'        => false,
		],
		'expected' => [
			'sent'     => true,
			'timeout'  => 3,
			'blocking' => false,
		],
	],
	'testShouldUseTimeoutFromFilter'                     => [
		'config'   => [
			'timeout_option'  => null,
			'batch'           => [ [ 'event' => 'Test' ] ],
			'timeout_filter'  => 2,
			'blocking_filter' => null,
			'is_error'        => false,
		],
		'expected' => [
			'sent'     => true,
			'timeout'  => 2,
			'blocking' => false,
		],
	],
	'testShouldCastNumericStringTimeoutFromFilter'       => [
		'config'   => [
			'timeout_option'  => null,
			'batch'           => [ [ 'event' => 'Test' ] ],
			'timeout_filter'  => '2.5',
			'blocking_filter' => null,
			'is_error'        => false,
		],
		'expected' => [
			'sent'     => true,
			'timeout'  => 2.5,
			'blocking' => false,
		],
	],
	'testShouldFallBackToDefaultTimeoutWhenFilterValueIsInvalid' => [
		'config'   => [
			'timeout_option'  => null,
			'batch'           => [ [ 'event' => 'Test' ] ],
			'timeout_filter'  => 'not a number',
			'blocking_filter' => null,
			'is_error'        => false,
		],
		'expected' => [
			'sent'     => true,
			'timeout'  => 1,
			'blocking' => false,
		],
	],
	'testShouldUseBlockingFromFilter'                    => [
		'config'   => [
			'timeout_option'  => null,
			'batch'           => [ [ 'event' => 'Test' ] ],
			'timeout_filter'  => null,
			'blocking_filter' => true,
			'is_error'        => false,
		],
		'expected' => [
			'sent'     => true,
			'timeout'  => 1,
			'blocking' => true,
		],
	],
	'testShouldReturnTrueWhenRequestFails'               => [
		'config'   => [
			'timeout_option'  => null,
			'batch'           => [ [ 'event' => 'Test' ] ],
			'timeout_filter'  => null,
			'blocking_filter' => null,
			'is_error'        => true,
		],
		'expected' => [
			'sent'     => true,
			'timeout'  => 1,
			'blocking' => false,
		],
	],
];
