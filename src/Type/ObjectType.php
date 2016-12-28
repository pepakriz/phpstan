<?php declare(strict_types = 1);

namespace PHPStan\Type;

class ObjectType implements ClassType
{

	use ClassTypeHelperTrait;

	/** @var string */
	private $class;

	public function __construct(string $class)
	{
		$this->class = $class;
	}

	public function getClass(): string
	{
		return $this->class;
	}

	public function combineWith(Type $otherType): Type
	{
		if ($otherType instanceof UnionType) {
			return $otherType->combineWith($this);
		}

		if ($otherType instanceof self && $this->getClass() == $otherType->getClass()) {
			return new self($this->getClass());
		}

		return new UnionType([
			$this,
			$otherType,
		]);
	}

	public function accepts(Type $type): bool
	{
		if ($type instanceof MixedType) {
			return true;
		}

		if ($type instanceof StaticType) {
			return $this->checkSubclassAcceptability($type->getBaseClass());
		}

		if ($type instanceof ObjectType) {
			return $this->checkSubclassAcceptability($type->getClass());
		}

		return false;
	}

	private function checkSubclassAcceptability(string $thatClass): bool
	{
		if ($this->getClass() === $thatClass) {
			return true;
		}

		if (!$this->exists($this->getClass()) || !$this->exists($thatClass)) {
			return false;
		}

		$thisReflection = new \ReflectionClass($this->getClass());
		$thatReflection = new \ReflectionClass($thatClass);

		if ($thisReflection->getName() === $thatReflection->getName()) {
			// class alias
			return true;
		}

		if ($thisReflection->isInterface() && $thatReflection->isInterface()) {
			return $thatReflection->implementsInterface($this->getClass());
		}

		return $thatReflection->isSubclassOf($this->getClass());
	}

	public function describe(): string
	{
		return $this->class;
	}

	public function canAccessProperties(): bool
	{
		return true;
	}

	public function canCallMethods(): bool
	{
		return strtolower($this->class) !== 'stdclass';
	}

	public function isDocumentableNatively(): bool
	{
		return true;
	}

}
