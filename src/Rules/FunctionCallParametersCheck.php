<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Type\TypeChecker;

class FunctionCallParametersCheck
{

	/**
	 * @var \PHPStan\Type\TypeChecker
	 */
	private $typeChecker;

	public function __construct(TypeChecker $typeChecker)
	{
		$this->typeChecker = $typeChecker;
	}

	/**
	 * @param \PHPStan\Reflection\ParametersAcceptor $function
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $funcCall
	 * @param string[] $messages Seven message templates
	 * @return string[]
	 */
	public function check(ParametersAcceptor $function, Scope $scope, $funcCall, array $messages): array
	{
		$functionParametersMinCount = 0;
		$functionParametersMaxCount = 0;
		foreach ($function->getParameters() as $parameter) {
			if (!$parameter->isOptional()) {
				$functionParametersMinCount++;
			}

			$functionParametersMaxCount++;
		}

		$checkTypes = true;

		if ($function->isVariadic()) {
			$functionParametersMaxCount = -1;
			$checkTypes = false;
		}

		$invokedParametersCount = count($funcCall->args);
		foreach ($funcCall->args as $arg) {
			if ($arg->unpack) {
				$invokedParametersCount = max($functionParametersMinCount, $functionParametersMaxCount);
				$checkTypes = false;
				break;
			}
		}

		if ($invokedParametersCount < $functionParametersMinCount || $invokedParametersCount > $functionParametersMaxCount) {
			if ($functionParametersMinCount === $functionParametersMaxCount) {
				return [sprintf(
					ngettext(
						$messages[0],
						$messages[1],
						$invokedParametersCount
					),
					$invokedParametersCount,
					$functionParametersMinCount
				)];
			} elseif ($functionParametersMaxCount === -1 && $invokedParametersCount < $functionParametersMinCount) {
				return [sprintf(
					ngettext(
						$messages[2],
						$messages[3],
						$invokedParametersCount
					),
					$invokedParametersCount,
					$functionParametersMinCount
				)];
			} elseif ($functionParametersMaxCount !== -1) {
				return [sprintf(
					ngettext(
						$messages[4],
						$messages[5],
						$invokedParametersCount
					),
					$invokedParametersCount,
					$functionParametersMinCount,
					$functionParametersMaxCount
				)];
			}
		} elseif ($checkTypes) {
			$args = $funcCall->args;
			$parameters = $function->getParameters();

			$errors = [];
			foreach ($parameters as $i => $parameter) {
				if (!isset($args[$i])) {
					break;
				}
				$argumentType = $scope->getType($args[$i]->value);
				if (!$this->typeChecker->accepts($parameter->getType(), $argumentType, $scope)) {

					$errors[] = sprintf(
						$messages[6],
						$i + 1,
						$parameter->getName(),
						$parameter->getType()->describe(),
						$argumentType->describe()
					);
				}
			}

			return $errors;
		}

		return [];
	}

}
