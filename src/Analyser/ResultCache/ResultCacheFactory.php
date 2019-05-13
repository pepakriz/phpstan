<?php declare(strict_types = 1);

namespace PHPStan\Analyser\ResultCache;

use PHPStan\Analyser\ResultCache\Strategy\ResultCacheStrategy;
use PHPStan\Dependency\DependencyResolverRule;
use PHPStan\PhpDoc\TypeNodeResolver;

class ResultCacheFactory
{

	/** @var string */
	private $cacheFile;

	/** @var TypeNodeResolver */
	private $typeNodeResolver;

	/** @var DependencyResolverRule */
	private $dependencyResolverRule;

	public function __construct(
		string $cacheFile,
		TypeNodeResolver $typeNodeResolver,
		DependencyResolverRule $dependencyResolverRule
	)
	{
		$this->cacheFile = $cacheFile;
		$this->typeNodeResolver = $typeNodeResolver;
		$this->dependencyResolverRule = $dependencyResolverRule;
	}

	public function create(ResultCacheStrategy $resultCacheStrategy, string $cacheKey): ResultCache
	{
		return new ResultCache(
			$this->cacheFile . '_' . $cacheKey,
			$this->typeNodeResolver,
			$resultCacheStrategy,
			$this->dependencyResolverRule
		);
	}

}
