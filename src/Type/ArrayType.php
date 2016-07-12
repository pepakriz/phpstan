<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\Scope;

class ArrayType implements Type
{

	use JustNullableTypeTrait;

	public function accepts(Type $passed, Scope $scope): bool
	{
		return $passed instanceof self;
	}

	public function describe(): string
	{
		return sprintf('array%s', $this->isNullable() ? '|null' : '');
	}

}
