<?php declare(strict_types = 1);

namespace PHPStan\Analyser\ResultCache;

use Nette\Utils\Json;
use Nette\Utils\Strings;
use PHPStan\Analyser\Error;
use PHPStan\Analyser\ResultCache\Strategy\ResultCacheStrategy;
use PHPStan\Dependency\DependencyResolverRule;
use ReflectionClass;
use function phpversion;
use function sha1;

class ResultCache
{

	const CACHE_VERSION = '1';

	/** @var string */
	private $cacheFile;

	/** @var \PHPStan\Analyser\ResultCache\Strategy\ResultCacheStrategy */
	private $resultCacheStrategy;

	/** @var DependencyResolverRule */
	private $dependencyResolverRule;

	/** @var mixed[]|null */
	private $data;

	public function __construct(
		string $cacheFile,
		ResultCacheStrategy $resultCacheStrategy,
		DependencyResolverRule $dependencyResolverRule
	)
	{
		$this->cacheFile = $cacheFile;
		$this->resultCacheStrategy = $resultCacheStrategy;
		$this->dependencyResolverRule = $dependencyResolverRule;
	}

	private function getData(): ?array
	{
		if ($this->data !== null) {
			return $this->data;
		}

		if (!file_exists($this->cacheFile)) {
			return null;
		}

		$cacheData = file_get_contents($this->cacheFile);
		if ($cacheData === false) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$data = Json::decode($cacheData, Json::FORCE_ARRAY);

		if ($data['hash'] !== $this->getCacheHash()) {
			return null;
		}

		return $this->data = $data;
	}

	/**
	 * @param string[] $files
	 */
	public function getRequirements(array $files): ResultCacheRequirements
	{
		$data = $this->getData();
		if ($data === null) {
			return new ResultCacheRequirements($files, []);
		}

		/** @var string[][] $sourceChangedFiles */
		$sourceChangedFiles = [[]];
		foreach ($data['files'] as $file => $values) {
			$fileHash = $this->resultCacheStrategy->getFileHash($file);
			if ($fileHash === null) {
				continue;
			}

			if ($fileHash === $values['hash']) {
				continue;
			}

			$sourceChangedFiles[] = $values['dependencies'];
			if ($values['vendor']) {
				continue;
			}

			$sourceChangedFiles[] = [$file];
		}

		$sourceChangedFiles = array_unique(array_merge(
			...$sourceChangedFiles
		));

		foreach ($files as $file) {
			if (isset($data['files'][$file])) {
				continue;
			}

			$sourceChangedFiles[] = $file;
		}

		$sourceChangedFilesByKeys = array_flip($sourceChangedFiles);

		/** @var Error[] $errors */
		$errors = [];
		foreach ($data['files'] as $file => $value) {
			if (array_key_exists($file, $sourceChangedFilesByKeys)) {
				continue;
			}

			foreach ($value['errors'] as $error) {
				$errors[] = new Error($error['message'], $error['file'], $error['line'], $error['canBeIgnored']);
			}
		}

		return new ResultCacheRequirements($sourceChangedFiles, $errors);
	}

	public function updateCache(ResultCacheRequirements $resultCacheRequirements): void
	{
		$files = $resultCacheRequirements->getFiles();
		$errors = $resultCacheRequirements->getErrors();

		$data = $this->getData();
		if ($data === null) {
			$data = [
				'hash' => $this->getCacheHash(),
				'files' => [],
			];
		}

		// Drop removed files from cache
		$removedFiles = array_keys($this->dependencyResolverRule->getDependencies());
		$removedFiles = array_merge($removedFiles, $files);
		$removedFiles = array_unique($removedFiles);
		foreach ($data['files'] as $file => $values) {
			if (!$values['vendor'] && !file_exists($file)) {
				$removedFiles[] = $file;
				unset($data['files'][$file]);
				continue;
			}
		}

		// Drop removed files from dependencies
		foreach ($data['files'] as $file => $values) {
			$data['files'][$file]['dependencies'] = array_diff($data['files'][$file]['dependencies'], $removedFiles);
		}

		// Cleanup all errors
		foreach ($data['files'] as $file => $values) {
			$data['files'][$file]['errors'] = [];
		}

		// Add or update re-analyzed files
		foreach ($files as $file) {
			$data['files'][$file] = [
				'hash' => $this->resultCacheStrategy->getFileHash($file),
				'vendor' => false,
				'errors' => [],
				'dependencies' => [],
			];
		}

		// Add or update errors
		foreach ($errors as $error) {
			if (!$error instanceof Error) {
				continue;
			}

			$file = $error->getFile();
			$dependency = null;

			$match = Strings::match($file, '#\(in context of class ([a-zA-Z0-9_\\\\]+)\)#');
			if ($match !== null) {
				$dependency = explode(' ', $file, 1)[0]; // remove context info
				$classReflection = new ReflectionClass($match[1]);
				$file = $classReflection->getFileName();
			}

			if (!isset($data['files'][$file])) {
				$data['files'][$file] = [
					'hash' => null,
					'vendor' => false,
					'errors' => [],
					'dependencies' => [],
				];
			}

			if ($dependency !== null) {
				$data['files'][$file]['dependencies'][] = $dependency;
			}

			$data['files'][$file]['errors'][] = [
				'file' => $error->getFile(),
				'line' => $error->getLine(),
				'message' => $error->getMessage(),
				'canBeIgnored' => $error->canBeIgnored(),
			];
		}

		// Add or update dependencies
		$dependencyFiles = [];
		foreach ($this->dependencyResolverRule->getDependencies() as $fileName => $dependencies) {
			foreach ($dependencies as $dependency) {
				if (!isset($data['files'][$dependency])) {
					$data['files'][$dependency] = [
						'hash' => null,
						'vendor' => true,
						'errors' => [],
						'dependencies' => [],
					];
				}

				$dependencyFiles[] = $dependency;
				$data['files'][$dependency]['dependencies'][] = $fileName;
			}
		}

		foreach (array_unique($dependencyFiles) as $file) {
			$data['files'][$file]['hash'] = $this->resultCacheStrategy->getFileHash($file);
		}

		// Remove vendor files without dependencies
		foreach ($data['files'] as $file => $values) {
			if (!$values['vendor']) {
				continue;
			}

			if (count($values['dependencies']) > 0) {
				continue;
			}

			unset($data['files'][$file]);
		}

		$data = $this->sortData($data);

		file_put_contents($this->cacheFile, Json::encode($data, Json::PRETTY));
	}

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	private function sortData(array $data): array
	{
		foreach ($data['files'] as $file => $values) {
			$data['files'][$file]['dependencies'] = array_unique($values['dependencies']);
			sort($data['files'][$file]['dependencies']);
		}

		ksort($data['files']);

		return $data;
	}

	private function getCacheHash(): string
	{
		$hashData = [
			self::CACHE_VERSION,
			ComposerHash::HASH,
			phpversion(),
		];

		return sha1(Json::encode($hashData));
	}

}
