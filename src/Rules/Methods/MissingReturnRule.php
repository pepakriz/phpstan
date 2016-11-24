<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\FunctionMissingReturnCheck;

class MissingReturnRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Rules\FunctionMissingReturnCheck */
	private $missingReturnCheck;

	public function __construct(FunctionMissingReturnCheck $missingReturnCheck)
	{
		$this->missingReturnCheck = $missingReturnCheck;
	}

	public function getNodeType(): string
	{
		return Node\Stmt\ClassMethod::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\ClassMethod $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (
			$node->getReturnType() === null
			|| $node->isAbstract()
			|| $node->getStmts() === null
		) {
			return [];
		}

		return $this->missingReturnCheck->checkMissingReturn(
			$node,
			sprintf(
				'Method %s::%s() should return %s but empty return statement found.',
				$scope->getClass(),
				$node->name,
				$node->getReturnType()
			)
		);
	}

}
