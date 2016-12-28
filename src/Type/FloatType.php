<?php declare(strict_types = 1);

namespace PHPStan\Type;

class FloatType implements Type
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
			return $type->accepts($this);
		}

		if ($type instanceof self || $type instanceof IntegerType) {
			return true;
		}

		return $type instanceof MixedType;
	}

	public function describe(): string
	{
		return 'float';
	}

}
