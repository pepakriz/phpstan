<?php declare(strict_types = 1);

namespace PHPStan\Type;

class StaticType implements StaticResolvableType
{

	/** @var string */
	private $baseClass;

	public function __construct(string $baseClass)
	{
		$this->baseClass = $baseClass;
	}

	public function getBaseClass(): string
	{
		return $this->baseClass;
	}

	public function combineWith(Type $otherType): Type
	{
		if ($otherType instanceof UnionType) {
			return $otherType->combineWith($this);
		}

		if ($otherType instanceof self && $this->accepts($otherType)) {
			return $otherType;
		}

		if ($otherType instanceof ObjectType && $this->accepts($otherType)) {
			return $otherType;
		}

		return new UnionType([
			$this,
			$otherType,
		]);
	}

	public function accepts(Type $type): bool
	{
		return (new ObjectType($this->baseClass))->accepts($type);
	}

	public function describe(): string
	{
		return sprintf('static(%s)', $this->baseClass);
	}

	public function canAccessProperties(): bool
	{
		return true;
	}

	public function canCallMethods(): bool
	{
		return true;
	}

	public function isDocumentableNatively(): bool
	{
		return true;
	}

	public function resolveStatic(string $className): Type
	{
		return new ObjectType($className);
	}

	public function changeBaseClass(string $className): StaticResolvableType
	{
		return new self($className);
	}

}
