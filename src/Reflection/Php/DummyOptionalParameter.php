<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PHPStan\Reflection\ParameterReflection;
use PHPStan\Type\Type;

class DummyOptionalParameter implements ParameterReflection
{

	/** @var string */
	private $name;

	/** @var \PHPStan\Type\Type  */
	private $type;

	public function __construct(string $name, Type $type)
	{
		$this->name = $name;
		$this->type = $type;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function isOptional(): bool
	{
		return true;
	}

	public function getType(): Type
	{
		return $this->type;
	}

}
