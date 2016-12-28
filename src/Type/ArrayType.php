<?php declare(strict_types = 1);

namespace PHPStan\Type;

class ArrayType implements IterableType
{

	use IterableTypeTrait;

	/** @var bool */
	private $itemTypeInferredFromLiteralArray;

	/** @var bool */
	private $possiblyCallable;

	public function __construct(
		Type $itemType,
		bool $itemTypeInferredFromLiteralArray = false,
		bool $possiblyCallable = false
	)
	{
		$this->itemType = $itemType;
		$this->itemTypeInferredFromLiteralArray = $itemTypeInferredFromLiteralArray;
		$this->possiblyCallable = $possiblyCallable;
	}

	public static function createDeepArrayType(NestedArrayItemType $nestedItemType): self
	{
		$itemType = $nestedItemType->getItemType();
		for ($i = 0; $i < $nestedItemType->getDepth() - 1; $i++) {
			$itemType = new self($itemType);
		}

		return new self($itemType);
	}

	public function isItemTypeInferredFromLiteralArray(): bool
	{
		return $this->itemTypeInferredFromLiteralArray;
	}

	public function isPossiblyCallable(): bool
	{
		return $this->possiblyCallable;
	}

	public function combineWith(Type $otherType): Type
	{
		if ($otherType instanceof UnionType) {
			return $otherType->combineWith($this);
		}

		if ($otherType instanceof IterableType) {
			$isItemInferredFromLiteralArray = $this->isItemTypeInferredFromLiteralArray();
			$isPossiblyCallable = $this->isPossiblyCallable();
			if ($otherType instanceof self) {
				$isItemInferredFromLiteralArray = $isItemInferredFromLiteralArray || $otherType->isItemTypeInferredFromLiteralArray();
				$isPossiblyCallable = $isPossiblyCallable || $otherType->isPossiblyCallable();
			}
			return new self(
				$this->getItemType()->combineWith($otherType->getItemType()),
				$isItemInferredFromLiteralArray,
				$isPossiblyCallable
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

		if ($type instanceof self) {
			return $this->getItemType()->accepts($type->getItemType());
		}

		return $type instanceof MixedType;
	}

	public function describe(): string
	{
		return sprintf('%s[]', $this->getItemType()->describe());
	}

}
