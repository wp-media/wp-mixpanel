<?php

return [
	'testShouldNotSendRequestWhenBatchIsEmpty'    => [
		'config'   => [
			'batch'    => [],
			'is_error' => false,
		],
		'expected' => [
			'sent' => false,
		],
	],
	'testShouldSendNonBlockingRequestWithTimeout' => [
		'config'   => [
			'batch'    => [ [ 'event' => 'Test' ] ],
			'is_error' => false,
		],
		'expected' => [
			'sent' => true,
		],
	],
	'testShouldReturnTrueWhenRequestFails'        => [
		'config'   => [
			'batch'    => [ [ 'event' => 'Test' ] ],
			'is_error' => true,
		],
		'expected' => [
			'sent' => true,
		],
	],
];
