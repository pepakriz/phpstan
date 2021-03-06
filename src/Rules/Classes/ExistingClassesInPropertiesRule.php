<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PhpParser\Node\Stmt\PropertyProperty;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;

class ExistingClassesInPropertiesRule implements \PHPStan\Rules\Rule
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
		return PropertyProperty::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\PropertyProperty $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$className = $scope->getClass();
		if ($className === null) {
			return [];
		}

		$classReflection = $this->broker->getClass($className);
		$propertyType = $classReflection->getProperty($node->name)->getType();

		if ($propertyType->getClass() === null) {
			return [];
		}

		if (!$this->broker->hasClass($propertyType->getClass())) {
			return [
				sprintf(
					'Property %s::$%s has unknown class %s as its type.',
					$className,
					$node->name,
					$propertyType->getClass()
				),
			];
		}

		return [];
	}

}
