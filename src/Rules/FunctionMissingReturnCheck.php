<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PhpParser\Node;

class FunctionMissingReturnCheck
{

	/**
	 * @param \PhpParser\Node\Stmt\ClassMethod $node
	 * @param string $missingReturnStatementMessage
	 * @return string[]
	 */
	public function checkMissingReturn(
		Node $node,
		string $missingReturnStatementMessage
	): array
	{
		if (!$this->leadsToGarantedExit($node->getStmts())) {
			return [
				$missingReturnStatementMessage,
			];
		}

		return [];
	}

	/**
	 * @param Node[] $nodes
	 * @return bool
	 */
	private function leadsToGarantedExit(array $nodes): bool
	{
		if (count($nodes) === 0) {
			return false;
		}

		/** @var Node[] $reversedNodes */
		$reversedNodes = array_reverse($nodes);
		foreach ($reversedNodes as $node) {
			if ($node instanceof Node\Stmt\Return_) {
				return true;
			}

			if ($node instanceof Node\Stmt\Throw_) {
				return true;
			}

			if ($node instanceof Node\Stmt\If_ && $node->else !== null) {
				if (!$this->leadsToGarantedExit($node->else->stmts)) {
					continue;
				}

				if (!$this->leadsToGarantedExit($node->stmts)) {
					continue;
				}

				foreach ($node->elseifs as $elseifNode) {
					if (!$this->leadsToGarantedExit($elseifNode->stmts)) {
						continue 2;
					}
				}

				return true;
			}

			if (
				$node instanceof Node\Stmt\While_
				&& $node->cond instanceof Node\Expr\ConstFetch
				&& $node->cond->name->parts[0] === 'true'
			) {
				if ($this->leadsToPotentialBreak($node->stmts) > 0) {
					continue;
				}

				return true;
			}

			if (
				$node instanceof Node\Stmt\Do_
				&& $node->cond instanceof Node\Expr\ConstFetch
				&& $node->cond->name->parts[0] === 'true'
			) {
				if ($this->leadsToPotentialBreak($node->stmts) > 0) {
					continue;
				}

				return true;
			}

			if ($node instanceof Node\Stmt\Switch_) {
				foreach ($node->cases as $case) {
					if (!$this->leadsToGarantedExit($case->stmts)) {
						continue 2;
					}

					if ($case->cond === null) {
						return true;
					}
				}
			}

			if ($node instanceof Node\Stmt\TryCatch) {
				if ($node->finallyStmts !== null && $this->leadsToGarantedExit($node->finallyStmts)) {
					return true;
				}

				if (!$this->leadsToGarantedExit($node->stmts)) {
					continue;
				}

				foreach ($node->catches as $catch) {
					if (!$this->leadsToGarantedExit($catch->stmts)) {
						continue 2;
					}
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * @param Node[] $nodes
	 * @return int
	 */
	private function leadsToPotentialBreak(array $nodes): int
	{
		if (count($nodes) === 0) {
			return 0;
		}

		/** @var Node[] $reversedNodes */
		$reversedNodes = array_reverse($nodes);
		foreach ($reversedNodes as $node) {
			if ($node instanceof Node\Stmt\Return_) {
				continue;
			}

			if ($node instanceof Node\Stmt\Throw_) {
				continue;
			}

			if ($node instanceof Node\Stmt\Break_) {
				return $node->num !== null ? $node->num->value : 1;
			}

			if ($node instanceof Node\Stmt\If_) {
				$breakNum = $this->leadsToPotentialBreak($node->stmts);
				if ($breakNum > 0) {
					return $breakNum;
				}

				foreach ($node->elseifs as $elseifNode) {
					$breakNum = $this->leadsToPotentialBreak($elseifNode->stmts);
					if ($breakNum > 0) {
						return $breakNum;
					}
				}

				if ($node->else !== null) {
					$breakNum = $this->leadsToPotentialBreak($node->else->stmts);
					if ($breakNum > 0) {
						return $breakNum;
					}
				}

				continue;
			}

			if (
				$node instanceof Node\Stmt\While_
				&& $node->cond instanceof Node\Expr\ConstFetch
				&& $node->cond->name->parts[0] === 'true'
			) {
				$breakNum = $this->leadsToPotentialBreak($node->stmts);
				if ($breakNum === 0) {
					continue;
				}

				return $breakNum - 1;
			}

			if (
				$node instanceof Node\Stmt\Do_
				&& $node->cond instanceof Node\Expr\ConstFetch
				&& $node->cond->name->parts[0] === 'true'
			) {
				$breakNum = $this->leadsToPotentialBreak($node->stmts);
				if ($breakNum === 0) {
					continue;
				}

				return $breakNum - 1;
			}
		}

		return 0;
	}

}
