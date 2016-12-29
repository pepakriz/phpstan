<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\BrokerAwareClassReflectionExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\MixedType;
use PHPStan\Type\TypehintHelper;

class PhpClassReflectionExtension
	implements PropertiesClassReflectionExtension, MethodsClassReflectionExtension, BrokerAwareClassReflectionExtension
{

	/** @var \PHPStan\Reflection\Php\PhpMethodReflectionFactory */
	private $methodReflectionFactory;

	/** @var \PHPStan\Type\FileTypeMapper */
	private $fileTypeMapper;

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	private $properties = [];

	private $methods = [];

	public function __construct(
		PhpMethodReflectionFactory $methodReflectionFactory,
		FileTypeMapper $fileTypeMapper
	)
	{
		$this->methodReflectionFactory = $methodReflectionFactory;
		$this->fileTypeMapper = $fileTypeMapper;
	}

	public function setBroker(Broker $broker)
	{
		$this->broker = $broker;
	}

	public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
	{
		return $classReflection->getNativeReflection()->hasProperty($propertyName);
	}

	public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
	{
		if (!isset($this->properties[$classReflection->getName()])) {
			$this->properties[$classReflection->getName()] = $this->createProperties($classReflection);
		}

		return $this->properties[$classReflection->getName()][$propertyName];
	}

	/**
	 * @param \PHPStan\Reflection\ClassReflection $classReflection
	 * @return \PHPStan\Reflection\PropertyReflection[]
	 */
	private function createProperties(ClassReflection $classReflection): array
	{
		$properties = [];
		foreach ($classReflection->getNativeReflection()->getProperties() as $propertyReflection) {
			$propertyName = $propertyReflection->getName();
			$declaringClassReflection = $this->broker->getClass($propertyReflection->getDeclaringClass()->getName());
			$typeString = $this->getPropertyAnnotationTypeString($propertyReflection);
			if ($typeString === null) {
				$type = new MixedType();
			} elseif (!$declaringClassReflection->getNativeReflection()->isAnonymous() && $declaringClassReflection->getNativeReflection()->getFileName() !== false) {
				$typeMap = $this->fileTypeMapper->getTypeMap($declaringClassReflection->getNativeReflection()->getFileName());
				if (isset($typeMap[$typeString])) {
					$type = $typeMap[$typeString];
				} else {
					$type = new MixedType();
				}
			} else {
				$type = new MixedType();
			}

			$properties[$propertyName] = new PhpPropertyReflection(
				$declaringClassReflection,
				$type,
				$propertyReflection
			);
		}

		return $properties;
	}

	/**
	 * @param \ReflectionProperty $propertyReflection
	 * @return string|null
	 */
	private function getPropertyAnnotationTypeString(\ReflectionProperty $propertyReflection)
	{
		$phpDoc = $propertyReflection->getDocComment();
		if ($phpDoc === false) {
			return null;
		}

		$count = preg_match_all('#@var\s+' . FileTypeMapper::TYPE_PATTERN . '#', $phpDoc, $matches);
		if ($count !== 1) {
			return null;
		}

		return $matches[1][0];
	}

	public function hasMethod(ClassReflection $classReflection, string $methodName): bool
	{
		return $classReflection->getNativeReflection()->hasMethod($methodName);
	}

	public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
	{
		if (!isset($this->methods[$classReflection->getName()])) {
			$this->methods[$classReflection->getName()] = $this->createMethods($classReflection);
		}

		$methodName = strtolower($methodName);

		return $this->methods[$classReflection->getName()][$methodName];
	}

	/**
	 * @param \PHPStan\Reflection\ClassReflection $classReflection
	 * @return \PHPStan\Reflection\MethodReflection[]
	 */
	private function createMethods(ClassReflection $classReflection): array
	{
		$methods = [];
		foreach ($classReflection->getNativeReflection()->getMethods() as $methodReflection) {
			$declaringClass = $this->broker->getClass($methodReflection->getDeclaringClass()->getName());

			$phpDocParameterTypes = [];
			$phpDocReturnType = null;
			if (!$declaringClass->getNativeReflection()->isAnonymous() && $declaringClass->getNativeReflection()->getFileName() !== false) {
				$typeMap = $this->fileTypeMapper->getTypeMap($declaringClass->getNativeReflection()->getFileName());
				if ($methodReflection->getDocComment() !== false) {
					$phpDocParameterTypes = TypehintHelper::getParameterTypesFromPhpDoc(
						$typeMap,
						array_map(function (\ReflectionParameter $parameterReflection): string {
							return $parameterReflection->getName();
						}, $methodReflection->getParameters()),
						$methodReflection->getDocComment()
					);
				}

				if ($methodReflection->getDocComment() !== false) {
					$phpDocReturnType = TypehintHelper::getReturnTypeFromPhpDoc($typeMap, $methodReflection->getDocComment());
				}
			}

			$methods[strtolower($methodReflection->getName())] = $this->methodReflectionFactory->create(
				$declaringClass,
				$methodReflection,
				$phpDocParameterTypes,
				$phpDocReturnType
			);
		}

		return $methods;
	}

}
