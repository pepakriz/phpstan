<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\FunctionCallParametersCheck;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ClassType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

class CallMethodsRule implements \PHPStan\Rules\Rule
{

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	/**
	 * @var \PHPStan\Rules\FunctionCallParametersCheck
	 */
	private $check;

	/**
	 * @var \PHPStan\Rules\RuleLevelHelper
	 */
	private $ruleLevelHelper;

	/**
	 * @var bool
	 */
	private $checkThisOnly;

	public function __construct(
		Broker $broker,
		FunctionCallParametersCheck $check,
		RuleLevelHelper $ruleLevelHelper,
		bool $checkThisOnly
	)
	{
		$this->broker = $broker;
		$this->check = $check;
		$this->ruleLevelHelper = $ruleLevelHelper;
		$this->checkThisOnly = $checkThisOnly;
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
		if (!is_string($node->name)) {
			return [];
		}

		if ($this->checkThisOnly && !$this->ruleLevelHelper->isThis($node->var)) {
			return [];
		}

		$type = $scope->getType($node->var);

		return $this->processType($type, $node, $scope);
	}

	/**
	 * @param \PHPStan\Type\Type $type
	 * @param \PhpParser\Node\Expr\MethodCall $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processType(Type $type, MethodCall $node, Scope $scope): array
	{
		if ($type instanceof UnionType) {
			$errors = [];
			foreach ($type->getNestedTypes() as $nestedType) {
				$errors = $this->processType($nestedType, $node, $scope);
				if (count($errors) === 0) {
					return [];
				}
			}

			if (count($errors) > 0) {
				return [
					sprintf('Cannot call method %s() on %s.', $node->name, $type->describe()),
				];
			}

			return [];
		}

		if ($type instanceof MixedType) {
			return [];
		}

		if (!($type instanceof ClassType)) {
			return [
				sprintf('Cannot call method %s() on %s.', $node->name, $type->describe()),
			];
		}

		if (!$type->canCallMethods()) {
			return [
				sprintf('Cannot call method %s() on %s.', $node->name, $type->describe()),
			];
		}

		if (!($type instanceof ObjectType)) {
			return [];
		}

		$methodClass = $type->getClass();
		$name = $node->name;
		if (!$this->broker->hasClass($methodClass)) {
			return [
				sprintf(
					'Call to method %s() on an unknown class %s.',
					$name,
					$methodClass
				),
			];
		}

		$methodClassReflection = $this->broker->getClass($methodClass);
		if (!$methodClassReflection->hasMethod($name)) {
			$parentClassReflection = $methodClassReflection->getParentClass();
			while ($parentClassReflection !== false) {
				if ($parentClassReflection->hasMethod($name)) {
					return [
						sprintf(
							'Call to private method %s() of parent class %s.',
							$parentClassReflection->getMethod($name)->getName(),
							$parentClassReflection->getName()
						),
					];
				}

				$parentClassReflection = $parentClassReflection->getParentClass();
			}

			return [
				sprintf(
					'Call to an undefined method %s::%s().',
					$methodClassReflection->getName(),
					$name
				),
			];
		}

		$methodReflection = $methodClassReflection->getMethod($name);
		$messagesMethodName = $methodReflection->getDeclaringClass()->getName() . '::' . $methodReflection->getName() . '()';
		if (!$scope->canCallMethod($methodReflection)) {
			return [
				sprintf('Cannot call method %s from current scope.', $messagesMethodName),
			];
		}

		$errors = $this->check->check(
			$methodReflection,
			$scope,
			$node,
			[
				'Method ' . $messagesMethodName . ' invoked with %d parameter, %d required.',
				'Method ' . $messagesMethodName . ' invoked with %d parameters, %d required.',
				'Method ' . $messagesMethodName . ' invoked with %d parameter, at least %d required.',
				'Method ' . $messagesMethodName . ' invoked with %d parameters, at least %d required.',
				'Method ' . $messagesMethodName . ' invoked with %d parameter, %d-%d required.',
				'Method ' . $messagesMethodName . ' invoked with %d parameters, %d-%d required.',
				'Parameter #%d %s of method ' . $messagesMethodName . ' expects %s, %s given.',
				'Result of method ' . $messagesMethodName . ' (void) is used.',
			]
		);

		if ($methodReflection->getName() !== $name) {
			$errors[] = sprintf('Call to method %s with incorrect case: %s', $messagesMethodName, $name);
		}

		return $errors;
	}

}
