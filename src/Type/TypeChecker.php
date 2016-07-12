<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;

class TypeChecker
{

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	public function __construct(Broker $broker)
	{
		$this->broker = $broker;
	}

	public function accepts(Type $destination, Type $passed, Scope $scope): bool
	{
		// todo check nullability
		if ($passed instanceof MixedType || $destination instanceof MixedType) {
			return true;
		}
		if ($passed instanceof NullType) {
			return $destination->isNullable();
		}
		if (($destination->getClass() === null) !== ($passed->getClass() === null)) {
			return false;
		}

		if ($destination->getClass() !== null && $passed->getClass() !== null) {
			return $this->acceptsObject($destination->getClass(), $passed->getClass());
		}

		return $destination->accepts($passed, $scope);
	}

	private function acceptsObject(string $destinationClass, string $passedClass): bool
	{
		if ($destinationClass === $passedClass) {
			return true;
		}

		if (!$this->broker->hasClass($destinationClass) || !$this->broker->hasClass($passedClass)) {
			return true;
		}

		$destinationClassReflection = $this->broker->getClass($destinationClass);
		$passedClassReflection = $this->broker->getClass($passedClass);

		if (
			$destinationClassReflection->getNativeReflection()->isInterface()
		) {
			if ($passedClassReflection->getNativeReflection()->isInterface()) {
				return $passedClassReflection->isSubclassOf($destinationClass);
			}

			return $passedClassReflection->getNativeReflection()->implementsInterface($destinationClass);
		}

		return $passedClassReflection->getNativeReflection()->isSubclassOf($destinationClass);
	}

}
