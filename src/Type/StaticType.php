<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\Scope;

class StaticType implements Type
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
		return $passed->getClass() !== null; // todo, add special getter for base class and implement check in TypeChecker
	}

	public function describe(): string
	{
		return 'static';
	}

}
