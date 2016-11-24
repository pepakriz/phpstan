<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PHPStan\Rules\FunctionMissingReturnCheck;

class MissingReturnRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new MissingReturnRule(new FunctionMissingReturnCheck());
	}

	public function testReturnTypeRule()
	{
		$this->analyse([__DIR__ . '/data/missing-return.php'], [
			[
				'Method ReturnTypes\NullFoo::returnMissing() should return bool but empty return statement found.',
				8,
			],
			[
				'Method ReturnTypes\NullFoo::returnMissingWithIfWithoutElse() should return bool but empty return statement found.',
				12,
			],
			[
				'Method ReturnTypes\NullFoo::returnMissingWithWhileTrue() should return bool but empty return statement found.',
				19,
			],
			[
				'Method ReturnTypes\NullFoo::returnMissingWithWhile() should return bool but empty return statement found.',
				33,
			],
			[
				'Method ReturnTypes\NullFoo::returnMissingWithDoWhile() should return bool but empty return statement found.',
				40,
			],
			[
				'Method ReturnTypes\NullFoo::returnMissingWithSwitch() should return bool but empty return statement found.',
				54,
			],
			[
				'Method ReturnTypes\NullFoo::returnMissingWithSwitchWithoutDefault() should return bool but empty return statement found.',
				69,
			],
			[
				'Method ReturnTypes\NullFoo::returnMissingInTry() should return bool but empty return statement found.',
				82,
			],
			[
				'Method ReturnTypes\NullFoo::returnMissingInCatch() should return bool but empty return statement found.',
				90,
			],
			[
				'Method ReturnTypes\NullFoo::returnMissingInInnerWhile() should return bool but empty return statement found.',
				98,
			],
			[
				'Method ReturnTypes\NullFoo::returnMissingInInnerDoWhile() should return bool but empty return statement found.',
				107,
			],
		]);
	}

}
