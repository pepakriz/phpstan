<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\Scope;

class CallableType implements Type
{

	use JustNullableTypeTrait;

	public function accepts(Type $passed, Scope $scope): bool
	{
		if ($passed instanceof ObjectType) {
			return $passed->getClass() === \Closure::class;
		}

		return in_array(get_class($passed), [
			self::class,
			StringType::class,
			ArrayType::class,
		], true);
	}

	public function describe(): string
	{
		return sprintf('callable%s', $this->isNullable() ? '|null' : '');
	}

}
