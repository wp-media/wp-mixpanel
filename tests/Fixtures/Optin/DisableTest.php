<?php

return [
	'testShouldDoNothingWhenUserDoesNotHaveCapability' => [
		'user_role' => 'subscriber',
		'option_value' => true,
		'expected' => true,
	],
	'testShouldDoNothingWhenOptinAlreadyDisabled' => [
		'user_role' => 'administrator',
		'option_value' => false,
		'expected' => false,
	],
	'testShouldDisableOptin' => [
		'user_role' => 'administrator',
		'option_value' => true,
		'expected' => false,
	],
];