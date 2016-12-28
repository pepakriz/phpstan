<?php declare(strict_types = 1);

namespace PHPStan\Type;

class TrueOrFalseBooleanType implements BooleanType
{

	public function describe(): string
	{
		return 'bool';
	}

	public function combineWith(Type $otherType): Type
	{
		if ($otherType instanceof UnionType) {
			return $otherType->combineWith($this);
		}

		if ($otherType instanceof BooleanType) {
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

		if ($type instanceof BooleanType) {
			return true;
		}

		return $type instanceof MixedType;
	}

}
