<?php

return [
	'testShouldDoNothingWhenUserDoesNotHaveCapability' => [
		'user_role'    => 'subscriber',
		'option_value' => true,
		'expected'     => true,
	],
	'testShouldDoNothingWhenOptinAlreadyEnabled'       => [
		'user_role'    => 'administrator',
		'option_value' => true,
		'expected'     => true,
	],
	'testShouldEnableOptin'                            => [
		'user_role'    => 'administrator',
		'option_value' => false,
		'expected'     => true,
	],
];
