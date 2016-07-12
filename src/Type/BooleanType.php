<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\Scope;

class BooleanType implements Type
{

	use JustNullableTypeTrait;

	public function accepts(Type $passed, Scope $scope): bool
	{
		if ($scope->isDeclareStrictTypes()) {
			return $passed instanceof self;
		}

		return in_array(get_class($passed), [
			self::class,
			IntegerType::class,
			FloatType::class,
			StringType::class,
		], true);
	}

	public function describe(): string
	{
		return sprintf('bool%s', $this->isNullable() ? '|null' : '');
	}

}
