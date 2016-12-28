<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PhpParser\Node;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\FunctionReturnTypeCheck;

class ClosureReturnTypeRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Rules\FunctionReturnTypeCheck */
	private $returnTypeCheck;

	public function __construct(FunctionReturnTypeCheck $returnTypeCheck)
	{
		$this->returnTypeCheck = $returnTypeCheck;
	}

	public function getNodeType(): string
	{
		return Return_::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\Return_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$scope->isInAnonymousFunction()) {
			return [];
		}

		try {
			$this->returnTypeCheck->checkReturnType(
				$scope,
				$scope->getAnonymousFunctionReturnType(),
				$node->expr
			);
		} catch (\PHPStan\Rules\EmptyReturnStatementException $e) {
			return [
				sprintf('Anonymous function should return %s but empty return statement found.', $e->getType()->describe()),
			];
		} catch (\PHPStan\Rules\VoidReturnStatementException $e) {
			return [
				sprintf('Anonymous function with return type void returns %s but should not return anything.', $e->getReturnType()->describe()),
			];
		} catch (\PHPStan\Rules\TypeMismatchReturnStatementException $e) {
			return [
				sprintf(
					'Anonymous function should return %s but returns %s.',
					$e->getType()->describe(),
					$e->getReturnType()->describe()
				),
			];
		}

		return [];
	}

}
