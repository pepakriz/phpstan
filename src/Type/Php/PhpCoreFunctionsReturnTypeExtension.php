<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Type;

class PhpCoreFunctionsReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	/** @var int[] */
	private $arrayFunctionsThatCreateArrayBasedOnClosureReturnType = [
		'array_map' => 0,
	];

	/** @var int[] */
	private $arrayFunctionsThatDependOnClosureReturnType = [
		'array_reduce' => 1,
	];

	/** @var int[] */
	private $arrayFunctionsThatDependOnArgumentType = [
		'array_filter' => 0,
		'array_unique' => 0,
		'array_reverse' => 0,
	];

	/** @var int[] */
	private $arrayFunctionsThatCreateArrayBasedOnArgumentType = [
		'array_fill' => 2,
		'array_fill_keys' => 1,
	];

	/** @var string[] */
	private $functionsThatCombineAllArgumentTypes = [
		'min' => '',
		'max' => '',
	];

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		$functionName = strtolower($functionReflection->getName());

		return isset($this->arrayFunctionsThatCreateArrayBasedOnClosureReturnType[$functionName])
			|| isset($this->arrayFunctionsThatDependOnClosureReturnType[$functionName])
			|| isset($this->arrayFunctionsThatDependOnArgumentType[$functionName])
			|| isset($this->arrayFunctionsThatCreateArrayBasedOnArgumentType[$functionName])
			|| isset($this->functionsThatCombineAllArgumentTypes[$functionName]);
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		$functionName = strtolower($functionReflection->getName());

		if (isset($this->arrayFunctionsThatCreateArrayBasedOnClosureReturnType[$functionName])) {
			if (!isset($functionCall->args[$this->arrayFunctionsThatCreateArrayBasedOnClosureReturnType[$functionName]])) {
				return $functionReflection->getReturnType();
			}

			$argumentValue = $functionCall->args[$this->arrayFunctionsThatCreateArrayBasedOnClosureReturnType[$functionName]]->value;
			if (!$argumentValue instanceof Closure) {
				return $functionReflection->getReturnType();
			}

			$anonymousFunctionType = $scope->getFunctionType($argumentValue->returnType, $argumentValue->returnType === null, false);

			return new ArrayType($anonymousFunctionType, true);
		}

		if (isset($this->arrayFunctionsThatDependOnClosureReturnType[$functionName])) {
			if (!isset($functionCall->args[$this->arrayFunctionsThatDependOnClosureReturnType[$functionName]])) {
				return $functionReflection->getReturnType();
			}

			$argumentValue = $functionCall->args[$this->arrayFunctionsThatDependOnClosureReturnType[$functionName]]->value;
			if (!$argumentValue instanceof Closure) {
				return $functionReflection->getReturnType();
			}

			return $scope->getFunctionType($argumentValue->returnType, $argumentValue->returnType === null, false);
		}

		if (isset($this->arrayFunctionsThatDependOnArgumentType[$functionName])) {
			if (!isset($functionCall->args[$this->arrayFunctionsThatDependOnArgumentType[$functionName]])) {
				return $functionReflection->getReturnType();
			}

			$argumentValue = $functionCall->args[$this->arrayFunctionsThatDependOnArgumentType[$functionName]]->value;
			return $scope->getType($argumentValue);
		}

		if (isset($this->arrayFunctionsThatCreateArrayBasedOnArgumentType[$functionName])) {
			if (!isset($functionCall->args[$this->arrayFunctionsThatCreateArrayBasedOnArgumentType[$functionName]])) {
				return $functionReflection->getReturnType();
			}

			$argumentValue = $functionCall->args[$this->arrayFunctionsThatCreateArrayBasedOnArgumentType[$functionName]]->value;
			return new ArrayType($scope->getType($argumentValue), true, true);
		}

		if (isset($this->functionsThatCombineAllArgumentTypes[$functionName])) {
			if (!isset($functionCall->args[0])) {
				return $functionReflection->getReturnType();
			}

			if ($functionCall->args[0]->unpack) {
				$argumentType = $scope->getType($functionCall->args[0]->value);
				if ($argumentType instanceof ArrayType) {
					return $argumentType->getItemType();
				}
			}

			if (count($functionCall->args) === 1) {
				$argumentType = $scope->getType($functionCall->args[0]->value);
				if ($argumentType instanceof ArrayType) {
					return $argumentType->getItemType();
				}
			}

			$argumentType = null;
			foreach ($functionCall->args as $arg) {
				$argType = $scope->getType($arg->value);
				if ($argumentType === null) {
					$argumentType = $argType;
				} else {
					$argumentType = $argumentType->combineWith($argType);
				}
			}

			/** @var \PHPStan\Type\Type $argumentType */
			$argumentType = $argumentType;

			return $argumentType;
		}

		throw new \PHPStan\ShouldNotHappenException();
	}

}
