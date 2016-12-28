<?php declare(strict_types = 1);

namespace PHPStan\Type;

class UnionType implements Type
{

	/** @var \PHPStan\Type\Type[] */
	private $nestedTypes;

	/**
	 * @param \PHPStan\Type\Type[] $nestedTypes
	 */
	public function __construct(
		array $nestedTypes
	)
	{
		$this->nestedTypes = $nestedTypes;
	}

	/**
	 * @return \PHPStan\Type\Type[]
	 */
	public function getNestedTypes(): array
	{
		return $this->nestedTypes;
	}

	public function combineWith(Type $otherType): Type
	{
		if ($otherType instanceof UnionType) {
			$that = $this;
			foreach ($otherType->nestedTypes as $nestedType) {
				$that = $that->combineWith($nestedType);
			}

			return $that;
		}

		if ($otherType instanceof MixedType) {
			return $otherType;
		}

		foreach ($this->nestedTypes as $nestedType) {
			if ($nestedType->accepts($otherType)) {
				return $this;
			}
		}

		return new self(array_merge($this->nestedTypes, [$otherType]));
	}

	public function accepts(Type $type): bool
	{
		if ($type instanceof self) {
			foreach ($type->nestedTypes as $nestedType) {
				if ($this->accepts($nestedType)) {
					return true;
				}
			}

			return false;
		}

		if ($type instanceof MixedType) {
			return true;
		}

		foreach ($this->nestedTypes as $nestedType) {
			if ($nestedType->accepts($type)) {
				return true;
			}
		}

		return false;
	}

	public function describe(): string
	{
		return implode('|', array_map(function (Type $otherType): string {
			return $otherType->describe();
		}, $this->nestedTypes));
	}

}
