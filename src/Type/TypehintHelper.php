<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\NameScope;

class TypehintHelper
{

	public static function getTypeObjectFromTypehint(
		string $typehintString,
		bool $isNullable,
		string $selfClass = null,
		NameScope $nameScope = null
	): Type
	{
		if (strrpos($typehintString, '[]') === strlen($typehintString) - 2) {
			$hintType = new ArrayType(self::getTypeObjectFromTypehint(
				substr($typehintString, 0, -2),
				false,
				$selfClass,
				$nameScope
			));
		} elseif ($typehintString === 'static' && $selfClass !== null) {
			$hintType = new StaticType($selfClass);
		} elseif ($typehintString === 'self' && $selfClass !== null) {
			$hintType = new ObjectType($selfClass);
		} else {
			$lowercasedTypehintString = strtolower($typehintString);
			switch ($lowercasedTypehintString) {
				case 'int':
				case 'integer':
					$hintType = new IntegerType();
					break;
				case 'bool':
				case 'boolean':
					$hintType = new TrueOrFalseBooleanType();
					break;
				case 'true':
					$hintType = new TrueBooleanType();
					break;
				case 'false':
					$hintType = new FalseBooleanType();
					break;
				case 'string':
					$hintType = new StringType();
					break;
				case 'float':
					$hintType = new FloatType();
					break;
				case 'array':
					$hintType = new ArrayType(new MixedType());
					break;
				case 'iterable':
					$hintType = new IterableIterableType(new MixedType());
					break;
				case 'callable':
					$hintType = new CallableType();
					break;
				case null:
					$hintType = new MixedType();
					break;
				case 'resource':
					$hintType = new ResourceType();
					break;
				case 'object':
				case 'mixed':
					$hintType = new MixedType();
					break;
				case 'null':
					$hintType = new NullType();
					break;
				case 'void':
					$hintType = new VoidType();
					break;
				default:
					$className = $typehintString;
					if ($nameScope !== null) {
						$className = $nameScope->resolveStringName($className);
					}
					$hintType = new ObjectType($className);
			}
		}

		return $isNullable
			? $hintType->combineWith(new NullType())
			: $hintType;
	}

	public static function decideTypeFromReflection(
		\ReflectionType $reflectionType = null,
		Type $phpDocType = null,
		string $selfClass = null,
		bool $isVariadic = false
	): Type
	{
		if ($reflectionType === null) {
			return $phpDocType !== null ? $phpDocType : new MixedType();
		}

		$reflectionTypeString = (string) $reflectionType;
		if ($isVariadic) {
			$reflectionTypeString .= '[]';
		}

		$type = self::getTypeObjectFromTypehint(
			$reflectionTypeString,
			$reflectionType->allowsNull(),
			$selfClass
		);

		return self::decideType($type, $phpDocType);
	}

	public static function decideType(
		Type $type,
		Type $phpDocType = null
	): Type
	{
		if ($phpDocType !== null) {
//			if ($type instanceof IterableType && $phpDocType instanceof ArrayType) {
//				if ($type instanceof IterableIterableType) {
//					$phpDocType = new IterableIterableType($phpDocType->getItemType());
//
//					if ($type->accepts(new NullType()) || $phpDocType->accepts(new NullType())) {
//						$phpDocType = $phpDocType->combineWith(new NullType());
//					}
//
//				} elseif ($type instanceof ArrayType) {
//					$type = new ArrayType($phpDocType->getItemType());
//
//					if ($type->accepts(new NullType()) || $phpDocType->accepts(new NullType())) {
//						$type = $type->combineWith(new NullType());
//					}
//				}
//			} elseif ($phpDocType instanceof UnionType) {
//				if ($phpDocType->accepts($type)) {
//					return $phpDocType;
//				}
//			}
			if ($type->accepts($phpDocType)) {
				return $phpDocType;
			}
		}

		return $type;
	}

	/**
	 * @param \PHPStan\Type\Type[] $typeMap
	 * @param string $docComment
	 * @return \PHPStan\Type\Type|null
	 */
	public static function getReturnTypeFromPhpDoc(array $typeMap, string $docComment)
	{
		$returnTypeString = self::getReturnTypeStringFromMethod($docComment);
		if ($returnTypeString !== null && isset($typeMap[$returnTypeString])) {
			return $typeMap[$returnTypeString];
		}

		return null;
	}

	/**
	 * @param string $docComment
	 * @return string|null
	 */
	private static function getReturnTypeStringFromMethod(string $docComment)
	{
		$count = preg_match_all('#@return\s+' . FileTypeMapper::TYPE_PATTERN . '#', $docComment, $matches);
		if ($count !== 1) {
			return null;
		}

		return $matches[1][0];
	}

	/**
	 * @param \PHPStan\Type\Type[] $typeMap
	 * @param string[] $parameterNames
	 * @param string $docComment
	 * @return \PHPStan\Type\Type[]
	 */
	public static function getParameterTypesFromPhpDoc(
		array $typeMap,
		array $parameterNames,
		string $docComment
	): array
	{
		preg_match_all('#@param\s+' . FileTypeMapper::TYPE_PATTERN . '\s+\$([a-zA-Z0-9_]+)#', $docComment, $matches, PREG_SET_ORDER);
		$phpDocParameterTypeStrings = [];
		foreach ($matches as $match) {
			$typeString = $match[1];
			$parameterName = $match[2];
			if (!isset($phpDocParameterTypeStrings[$parameterName])) {
				$phpDocParameterTypeStrings[$parameterName] = [];
			}

			$phpDocParameterTypeStrings[$parameterName][] = $typeString;
		}

		$phpDocParameterTypes = [];
		foreach ($parameterNames as $parameterName) {
			$typeString = self::getParameterAnnotationTypeString($phpDocParameterTypeStrings, $parameterName);
			if ($typeString !== null && isset($typeMap[$typeString])) {
				$phpDocParameterTypes[$parameterName] = $typeMap[$typeString];
			}
		}

		return $phpDocParameterTypes;
	}

	/**
	 * @param mixed[] $phpDocParameterTypeStrings
	 * @param string $parameterName
	 * @return string|null
	 */
	private static function getParameterAnnotationTypeString(array $phpDocParameterTypeStrings, string $parameterName)
	{
		if (!isset($phpDocParameterTypeStrings[$parameterName])) {
			return null;
		}

		$typeStrings = $phpDocParameterTypeStrings[$parameterName];
		if (count($typeStrings) > 1) {
			return null;
		}

		return $typeStrings[0];
	}

}
