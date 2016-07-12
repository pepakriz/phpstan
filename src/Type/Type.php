<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\Scope;

interface Type
{

	/**
	 * @return string|null
	 */
	public function getClass();

	public function isNullable(): bool;

	public function combineWith(Type $otherType): Type;

	public function makeNullable(): Type;

	public function accepts(Type $passed, Scope $scope): bool;

	public function describe(): string;

}
