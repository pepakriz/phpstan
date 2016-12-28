<?php declare(strict_types = 1);

namespace PHPStan\Type;

class NullType implements Type
{

	public function combineWith(Type $otherType): Type
	{
		if ($otherType instanceof UnionType) {
			return $otherType->combineWith($this);
		}

		if ($otherType instanceof MixedType) {
			return $otherType;
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
		return $type instanceof self || $type instanceof MixedType;
	}

	public function describe(): string
	{
		return 'null';
	}

}
