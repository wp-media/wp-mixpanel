<?php

return [
	'testShouldReturnFalseWhenUserDoesNotHaveCapability' => [
		'user_role' => 'subscriber',
		'option_value' => true,
		'expected' => false,
	],
	'testShouldReturnFalseWhenOptionIsNotSet' => [
		'user_role' => 'administrator',
		'option_value' => false,
		'expected' => false,
	],
	'testShouldReturnTrueWhenUserHasCapabilityAndOptionIsSet' => [
		'user_role' => 'administrator',
		'option_value' => true,
		'expected' => true,
	],
	'testShouldReturnFalseWhenUserHasCapabilityAndOptionIsNotSet' => [
		'user_role' => 'administrator',
		'option_value' => false,
		'expected' => false,
	],
];
