<?php declare(strict_types = 1);

namespace PHPStan\Type;

trait JustNullableTypeTrait
{

	public function combineWith(Type $otherType): Type
	{
		if ($otherType instanceof UnionType) {
			return $otherType->combineWith($this);
		}

		if ($otherType instanceof self) {
			return $this;
		}

		return new UnionType([
			$this,
			$otherType,
		]);
	}

	public function accepts(Type $type): bool
	{
		if ($type instanceof UnionType) {
			$type->accepts($this);
		}

		if ($type instanceof self) {
			return true;
		}

		return $type instanceof MixedType;
	}

}
