<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use PHPStan\Type\VoidType;

class FunctionReturnTypeCheck
{

	public function checkReturnType(
		Scope $scope,
		Type $returnType,
		Expr $returnValue = null
	)
	{
		if ($returnType instanceof UnionType) {
			foreach ($returnType->getNestedTypes() as $nestedType) {
				try {
					$this->checkReturnType(
						$scope,
						$nestedType,
						$returnValue
					);
					return;
				} catch (\PHPStan\Rules\EmptyReturnStatementException $e) {
					// ignore
				} catch (\PHPStan\Rules\VoidReturnStatementException $e) {
					// ignore
				} catch (\PHPStan\Rules\TypeMismatchReturnStatementException $e) {
					// ignore
				}
			}

			if ($e instanceof \PHPStan\Rules\EmptyReturnStatementException) {
				throw new \PHPStan\Rules\EmptyReturnStatementException($returnType);
			}

			if ($e instanceof \PHPStan\Rules\VoidReturnStatementException) {
				throw new \PHPStan\Rules\VoidReturnStatementException($returnType, $e->getReturnType());
			}

			if ($e instanceof \PHPStan\Rules\TypeMismatchReturnStatementException) {
				throw new \PHPStan\Rules\TypeMismatchReturnStatementException($returnType, $e->getReturnType());
			}
		}

		if ($returnValue === null) {
			if ($returnType instanceof VoidType || $returnType instanceof MixedType) {
				return;
			}

			throw new \PHPStan\Rules\EmptyReturnStatementException($returnType);
		}

		$returnValueType = $scope->getType($returnValue);
		if ($returnType instanceof VoidType) {
			throw new \PHPStan\Rules\VoidReturnStatementException($returnType, $returnValueType);
		}

		if ($returnValueType instanceof UnionType) {
			foreach ($returnValueType->getNestedTypes() as $nestedType) {
				if ($returnType->accepts($nestedType)) {
					return;
				}
			}

			throw new \PHPStan\Rules\TypeMismatchReturnStatementException($returnType, $returnValueType);
		}

		if (!$returnType->accepts($returnValueType)) {
			throw new \PHPStan\Rules\TypeMismatchReturnStatementException($returnType, $returnValueType);
		}
	}

}
