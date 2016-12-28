<?php declare(strict_types = 1);

namespace PHPStan\Type;

class VoidType implements Type
{

	public function combineWith(Type $otherType): Type
	{
		if ($otherType instanceof self) {
			return new self();
		}

		if ($otherType instanceof UnionType) {
			return $otherType->combineWith($this);
		}

		return new UnionType([
			new $this,
			clone $otherType,
		]);
	}

	public function accepts(Type $type): bool
	{
		return $type instanceof self || $type instanceof MixedType;
	}

	public function describe(): string
	{
		return 'void';
	}

}
