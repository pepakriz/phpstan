<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\Scope;

class StringType implements Type
{

	use JustNullableTypeTrait;

	public function accepts(Type $passed, Scope $scope): bool
	{
		if ($scope->isDeclareStrictTypes()) {
			return $passed instanceof self;
		}

		// todo object s __toString, asi budu muset v TypeCheckeru

		return in_array(get_class($passed), [
			self::class,
			IntegerType::class,
			FloatType::class,
			BooleanType::class,
		], true);
	}

	public function describe(): string
	{
		return sprintf('string%s', $this->isNullable() ? '|null' : '');
	}

}
