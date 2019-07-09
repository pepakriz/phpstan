<?php declare(strict_types = 1);

namespace PHPStan\Analyser\ResultCache;

use PHPStan\Analyser\ResultCache\Strategy\ResultCacheStrategy;
use PHPStan\Dependency\DependencyResolverRule;

class ResultCacheFactory
{

	/** @var string */
	private $cacheFile;

	/** @var DependencyResolverRule */
	private $dependencyResolverRule;

	public function __construct(
		string $cacheFile,
		DependencyResolverRule $dependencyResolverRule
	)
	{
		$this->cacheFile = $cacheFile;
		$this->dependencyResolverRule = $dependencyResolverRule;
	}

	public function create(ResultCacheStrategy $resultCacheStrategy, string $cacheKey): ResultCache
	{
		return new ResultCache(
			$this->cacheFile . '_' . $cacheKey,
			$resultCacheStrategy,
			$this->dependencyResolverRule
		);
	}

}
