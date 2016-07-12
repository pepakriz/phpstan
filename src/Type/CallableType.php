<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\Scope;

class CallableType implements Type
{

	use JustNullableTypeTrait;

	public function accepts(Type $passed, Scope $scope): bool
	{
		return $passed instanceof self;
	}

	public function describe(): string
	{
		return sprintf('callable%s', $this->isNullable() ? '|null' : '');
	}

}
