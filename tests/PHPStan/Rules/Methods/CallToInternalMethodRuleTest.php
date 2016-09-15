<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

class CallToInternalMethodRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new CallToInternalMethodRule($this->createBroker());
	}

	public function testCallInternalMethod()
	{
		require_once __DIR__ . '/data/internal-method-definition.php';
		require_once __DIR__ . '/data/internal-method.php';
		$this->analyse([__DIR__ . '/data/internal-method.php'], [
			[
				'Method InternalMethod\Foo::internalMethod() is internal.',
				6,
			],
			[
				'Method InternalMethod\Foo::internalMethod() is internal and can not be called from InternalMethod\Bar::__construct().',
				14,
			],
		]);
	}

	public function testCallInternalMethodInOneClass()
	{
		require_once __DIR__ . '/data/internal-method-in-one-class.php';
		$this->analyse([__DIR__ . '/data/internal-method-in-one-class.php'], []);
	}

}
