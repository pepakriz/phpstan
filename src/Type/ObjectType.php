<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\Scope;

class ObjectType implements Type
{

	/** @var string */
	private $class;

	/** @var bool */
	private $nullable;

	public function __construct(string $class, bool $nullable)
	{
		$this->class = $class;
		$this->nullable = $nullable;
	}

	/**
	 * @return string|null
	 */
	public function getClass()
	{
		return $this->class;
	}

	public function isNullable(): bool
	{
		return $this->nullable;
	}

	public function combineWith(Type $otherType): Type
	{
		if ($otherType instanceof $this && $this->getClass() == $otherType->getClass()) {
			return new self($this->getClass(), $this->isNullable() || $otherType->isNullable());
		}

		if ($otherType instanceof NullType) {
			return new self($this->getClass(), true);
		}

		return new MixedType($this->isNullable() || $otherType->isNullable());
	}

	public function makeNullable(): Type
	{
		return new self($this->getClass(), true);
	}

	public function accepts(Type $passed, Scope $scope): bool
	{
		return $passed->getClass() !== null; // dummy - real implementation in TypeChecker
	}

	public function describe(): string
	{
		return sprintf('%s%s', $this->getClass(), $this->isNullable() ? '|null' : '');
	}

}
