<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\Scope;

class FloatType implements Type
{

	use JustNullableTypeTrait;

	public function accepts(Type $passed, Scope $scope): bool
	{
		if ($scope->isDeclareStrictTypes()) {
			return $passed instanceof self || $passed instanceof IntegerType;
		}

		return in_array(get_class($passed), [
			self::class,
			IntegerType::class,
			BooleanType::class,
		], true);
	}

	public function describe(): string
	{
		return sprintf('float%s', $this->isNullable() ? '|null' : '');
	}

}
