<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

class CallToInternalFunctionRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new CallToInternalFunctionRule($this->createBroker());
	}

	public function testCallInternalFunction()
	{
		require_once __DIR__ . '/data/internal-function-definition.php';
		$this->analyse([__DIR__ . '/data/internal-function.php'], [
			[
				'Function internalFunction is internal function.',
				5,
			],
		]);
	}

	public function testCallInternalFunctionInOneFile()
	{
		require_once __DIR__ . '/data/internal-function-in-one-file.php';
		$this->analyse([__DIR__ . '/data/internal-function-in-one-file.php'], []);
	}

}
