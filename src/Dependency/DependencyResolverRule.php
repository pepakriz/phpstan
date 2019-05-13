<?php declare(strict_types = 1);

namespace PHPStan\Dependency;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\File\FileHelper;
use PHPStan\Rules\Rule;

class DependencyResolverRule implements Rule
{

	/** @var FileHelper */
	private $fileHelper;

	/** @var DependencyResolver */
	private $dependencyResolver;

	/** @var string[][] */
	private $dependencies = [];

	public function __construct(
		FileHelper $fileHelper,
		DependencyResolver $dependencyResolver
	)
	{
		$this->fileHelper = $fileHelper;
		$this->dependencyResolver = $dependencyResolver;
	}

	public function getNodeType(): string
	{
		return Node::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		$reflectionWithFilenames = $this->dependencyResolver->resolveDependencies($node, $scope);

		foreach ($reflectionWithFilenames as $dependencyReflection) {
			$dependencyFile = $dependencyReflection->getFileName();
			if ($dependencyFile === false) {
				continue;
			}

			$dependencyFile = $this->fileHelper->normalizePath($dependencyFile);

			if ($scope->getFile() === $dependencyFile) {
				continue;
			}

			$this->dependencies[$scope->getFile()][$dependencyFile] = $dependencyFile;
		}

		return [];
	}

	/**
	 * @return string[][]
	 */
	public function getDependencies(): array
	{
		return $this->dependencies;
	}

}
