<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

class AppendedArrayItemTypeRule implements \PHPStan\Rules\Rule
{

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
		if (!($node->var instanceof ArrayDimFetch)) {
			return [];
		}

		$assignedToType = $scope->getType($node->var->var);
		$assignedValueType = $scope->getType($node->expr);

		if ($this->isTypeValid($assignedToType, $assignedValueType)) {
			return [];
		}

		return [
			sprintf(
				'Array (%s) does not accept %s.',
				$assignedToType->describe(),
				$assignedValueType->describe()
			),
		];
	}

	private function isTypeValid(Type $assignedToType, Type $assignedValueType): bool
	{
		if ($assignedToType instanceof UnionType) {
			foreach ($assignedToType->getNestedTypes() as $nestedType) {
				if ($this->isTypeValid($nestedType, $assignedValueType)) {
					return true;
				}
			}

			return false;
		}

		if (!($assignedToType instanceof ArrayType)) {
			return true;
		}

		if ($assignedToType->isItemTypeInferredFromLiteralArray()) {
			return true;
		}

		return $assignedToType->getItemType()->accepts($assignedValueType);
	}

}
