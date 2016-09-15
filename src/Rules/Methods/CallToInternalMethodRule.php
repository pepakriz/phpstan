<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;

class CallToInternalMethodRule implements \PHPStan\Rules\Rule
{

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	public function __construct(Broker $broker)
	{
		$this->broker = $broker;
	}

	public function getNodeType(): string
	{
		return MethodCall::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\MethodCall $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if ($scope->isInClosureBind()) {
			return [];
		}

		if (!is_string($node->name)) {
			return [];
		}

		$type = $scope->getType($node->var);
		$methodClass = $type->getClass();
		if ($methodClass === null || !$this->broker->hasClass($methodClass)) {
			return [];
		}

		$name = (string) $node->name;
		$methodClassReflection = $this->broker->getClass($methodClass);
		if ($scope->getClass() === $methodClassReflection->getName()) {
			return [];
		}

		if (!$methodClassReflection->hasMethod($name)) {
			return [];
		}

		$methodReflection = $methodClassReflection->getNativeReflection()->getMethod($name);
		$phpdoc = $methodReflection->getDocComment();
		if ($phpdoc !== false && preg_match('#@internal\s+#', $phpdoc)) {
			if ($scope->getClass() !== null && $scope->getFunction() !== null) {
				return [sprintf(
					'Method %s::%s() is internal and can not be called from %s::%s().',
					$methodClassReflection->getName(),
					(string) $node->name,
					$scope->getClass(),
					$scope->getFunction()
				)];
			}

			return [sprintf(
				'Method %s::%s() is internal.',
				$methodClassReflection->getName(),
				(string) $node->name
			)];
		}

		return [];
	}

}
