<?php declare(strict_types = 1);

namespace PHPStan\Type;

interface StaticResolvableType extends ClassType
{

	public function resolveStatic(string $className): Type;

	public function changeBaseClass(string $className): self;

}
