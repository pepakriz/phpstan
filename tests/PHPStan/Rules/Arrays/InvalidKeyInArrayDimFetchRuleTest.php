<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

class InvalidKeyInArrayDimFetchRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new InvalidKeyInArrayDimFetchRule();
	}

	public function testInvalidKey()
	{
		require_once __DIR__ . '/data/invalid-key-array-dim-fetch.php';
		$this->analyse([__DIR__ . '/data/invalid-key-array-dim-fetch.php'], [
			[
				'Invalid array key type DateTimeImmutable.',
				22,
			],
			[
				'Invalid array key type array.',
				23,
			],
			[
				'Invalid array access key type string.',
				36,
			],
			[
				'Invalid array access key type string.',
				43,
			],
			[
				'Invalid array access key type string.',
				47,
			],
			[
				'Invalid array access on type int.',
				50,
			],
			[
				'Invalid array access on type DateTimeImmutable.',
				53,
			],
		]);
	}

}
