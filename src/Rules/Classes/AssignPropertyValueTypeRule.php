<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Type\TypeChecker;

class AssignPropertyValueTypeRule implements \PHPStan\Rules\Rule
{

	/**
	 * @var \PHPStan\Type\TypeChecker
	 */
	private $typeChecker;

	public function __construct(TypeChecker $typeChecker)
	{
		$this->typeChecker = $typeChecker;
	}

	public function getNodeType(): string
	{
		return Assign::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\Assign $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(\PhpParser\Node $node, Scope $scope): array
	{
		if (!($node->var instanceof PropertyFetch)) {
			return [];
		}

		$propertyHolderType = $scope->getType($node->var->var);
		if ($propertyHolderType->getClass() === null || !is_string($node->var->name)) {
			return [];
		}

		$propertyType = $scope->getType($node->var);
		$assignedType = $scope->getType($node->expr);

		if (!$this->typeChecker->accepts($propertyType, $assignedType, $scope->enterDeclareStrictTypes())) {
			return [sprintf(
				'Property %s::$%s (%s) does not accept %s.',
				$propertyHolderType->getClass(),
				$node->var->name,
				$propertyType->describe(),
				$assignedType->describe()
			)];
		}

		return [];
	}

}
