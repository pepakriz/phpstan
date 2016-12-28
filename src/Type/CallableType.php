<?php declare(strict_types = 1);

namespace PHPStan\Type;

class CallableType implements Type
{

	public function combineWith(Type $otherType): Type
	{
		if ($otherType instanceof UnionType) {
			return $otherType->combineWith($this);
		}

		if ($otherType instanceof self) {
			return $this;
		}

		if ($otherType instanceof ArrayType && $otherType->isPossiblyCallable()) {
			return $this;
		}

		return new UnionType([
			$this,
			$otherType,
		]);
	}

	public function accepts(Type $type): bool
	{
		if ($type instanceof self) {
			return true;
		}

		if ($type instanceof UnionType) {
			return $type->accepts($this);
		}

		if ($type instanceof ArrayType && $type->isPossiblyCallable()) {
			return true;
		}

		if ($type instanceof StringType) {
			return true;
		}

		if ($type instanceof ObjectType && $type->getClass() === 'Closure') {
			return true;
		}

		return $type instanceof MixedType;
	}

	public function describe(): string
	{
		return 'callable';
	}

}
