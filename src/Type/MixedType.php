<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\Scope;

class MixedType implements Type
{

	/** @var bool */
	private $nullable;

	public function __construct(bool $nullable)
	{
		$this->nullable = $nullable;
	}

	/**
	 * @return string|null
	 */
	public function getClass()
	{
		return null;
	}

	public function isNullable(): bool
	{
		return $this->nullable;
	}

	public function combineWith(Type $otherType): Type
	{
		return new self($this->isNullable() || $otherType->isNullable());
	}

	public function makeNullable(): Type
	{
		return new self(true);
	}

	public function accepts(Type $passed, Scope $scope): bool
	{
		return true;
	}

	public function describe(): string
	{
		return 'mixed';
	}

}
