<?php declare(strict_types = 1);

namespace PHPStan\Type;

interface Type
{

	public function combineWith(Type $otherType): Type;

	public function accepts(Type $type): bool;

	public function describe(): string;

}
