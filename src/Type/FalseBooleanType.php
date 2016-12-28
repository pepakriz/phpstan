<?php declare(strict_types = 1);

namespace PHPStan\Type;

class FalseBooleanType implements BooleanType
{

	public function describe(): string
	{
		return 'false';
	}

	public function combineWith(Type $otherType): Type
	{
		if ($otherType instanceof UnionType) {
			return $otherType->combineWith($this);
		}

		if ($otherType instanceof self) {
			return $this;
		}

		if ($otherType instanceof BooleanType) {
			return new TrueBooleanType();
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

		if ($type instanceof self) {
			return true;
		}

		return $type instanceof MixedType;
	}

}
