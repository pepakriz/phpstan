<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node\Stmt\PropertyProperty;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Type\TypeChecker;

class AssignPropertyDefaultValueTypeRule implements \PHPStan\Rules\Rule
{

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	/**
	 * @var \PHPStan\Type\TypeChecker
	 */
	private $typeChecker;

	public function __construct(
		Broker $broker,
		TypeChecker $typeChecker
	)
	{
		$this->broker = $broker;
		$this->typeChecker = $typeChecker;
	}

	public function getNodeType(): string
	{
		return PropertyProperty::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\PropertyProperty $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(\PhpParser\Node $node, Scope $scope): array
	{
		if ($scope->getClass() === null) {
			return [];
		}

		if ($node->default === null) {
			return [];
		}

		$classReflection = $this->broker->getClass($scope->getClass());
		$propertyType = $classReflection->getProperty($node->name)->getType();
		$assignedType = $scope->getType($node->default);

		if (!$this->typeChecker->accepts($propertyType, $assignedType, $scope->enterDeclareStrictTypes())) {
			return [sprintf(
				'Property %s::$%s (%s) cannot have %s as its default value.',
				$scope->getClass(),
				$node->name,
				$propertyType->describe(),
				$assignedType->describe()
			)];
		}

		return [];
	}

}
