<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\Scope;

class NullType implements Type
{

	/**
	 * @return string|null
	 */
	public function getClass()
	{
		return null;
	}

	public function isNullable(): bool
	{
		return true;
	}

	public function combineWith(Type $otherType): Type
	{
		return $otherType->makeNullable();
	}

	public function makeNullable(): Type
	{
		return $this;
	}

	public function accepts(Type $passed, Scope $scope): bool
	{
		return $passed instanceof self;
	}

	public function describe(): string
	{
		return 'null';
	}

}
