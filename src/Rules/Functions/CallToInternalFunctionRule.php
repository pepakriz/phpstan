<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;

class CallToInternalFunctionRule implements \PHPStan\Rules\Rule
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
		return FuncCall::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\FuncCall $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!($node->name instanceof \PhpParser\Node\Name)) {
			return [];
		}

		if (!$this->broker->hasFunction($node->name, $scope)) {
			return [];
		}

		$function = $this->broker->getFunction($node->name, $scope);
		$functionReflection = $function->getNativeReflection();

		if ($scope->getFile() === $functionReflection->getFileName()) {
			return [];
		}

		$phpdoc = $functionReflection->getDocComment();
		if ($phpdoc !== false && preg_match('#@internal\s+#', $phpdoc)) {
			return [sprintf('Function %s is internal function.', (string) $node->name)];
		}

		return [];
	}

}
