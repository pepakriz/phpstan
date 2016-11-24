<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PHPStan\Rules\FunctionMissingReturnCheck;

class ClosureMissingReturnRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new ClosureMissingReturnRule(new FunctionMissingReturnCheck());
	}

	public function testReturnTypeRule()
	{
		$this->analyse([__DIR__ . '/data/closure-missing-return.php'], [
			[
				'Anonymous function should return bool but empty return statement found.',
				5,
			],
			[
				'Anonymous function should return bool but empty return statement found.',
				8,
			],
			[
				'Anonymous function should return bool but empty return statement found.',
				14,
			],
			[
				'Anonymous function should return bool but empty return statement found.',
				27,
			],
			[
				'Anonymous function should return bool but empty return statement found.',
				33,
			],
			[
				'Anonymous function should return bool but empty return statement found.',
				46,
			],
			[
				'Anonymous function should return bool but empty return statement found.',
				60,
			],
			[
				'Anonymous function should return bool but empty return statement found.',
				72,
			],
			[
				'Anonymous function should return bool but empty return statement found.',
				79,
			],
			[
				'Anonymous function should return bool but empty return statement found.',
				86,
			],
			[
				'Anonymous function should return bool but empty return statement found.',
				94,
			],
		]);
	}

}
