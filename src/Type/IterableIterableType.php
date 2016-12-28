<?php declare(strict_types = 1);

namespace PHPStan\Type;

class IterableIterableType implements IterableType
{

	use IterableTypeTrait;

	public function __construct(
		Type $itemType
	)
	{
		$this->itemType = $itemType;
	}

	public function combineWith(Type $otherType): Type
	{
		if ($otherType instanceof UnionType) {
			return $otherType->combineWith($this);
		}

		if ($otherType instanceof IterableType) {
			return new self(
				$this->getItemType()->combineWith($otherType->getItemType())
			);
		}

		return new UnionType([
			$this,
			$otherType,
		]);
	}

	public function accepts(Type $type): bool
	{
		if ($type instanceof UnionType) {
			return $type->accepts($this);
		}

		if ($type instanceof IterableType) {
			return $this->getItemType()->accepts($type->getItemType());
		}

		return $type instanceof MixedType;
	}

	public function describe(): string
	{
		return sprintf('iterable(%s[])', $this->getItemType()->describe());
	}

}
