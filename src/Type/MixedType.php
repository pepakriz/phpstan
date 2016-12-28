<?php declare(strict_types = 1);

namespace PHPStan\Type;

class MixedType implements Type
{

	public function combineWith(Type $otherType): Type
	{
		return $this;
	}

	public function accepts(Type $type): bool
	{
		return true;
	}

	public function describe(): string
	{
		return 'mixed';
	}

}
