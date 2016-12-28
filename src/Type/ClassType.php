<?php declare(strict_types = 1);

namespace PHPStan\Type;

interface ClassType extends Type
{

	public function canAccessProperties(): bool;

	public function canCallMethods(): bool;

}
