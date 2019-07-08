<?php declare(strict_types = 1);

namespace PHPStan\Analyser\ResultCache;

use PHPStan\Analyser\Error;

class ResultCacheRequirements
{

	/** @var string[] */
	private $files;

	/** @var Error[] */
	private $errors;

	/**
	 * @param string[] $files
	 * @param Error[] $errors
	 */
	public function __construct(
		array $files,
		array $errors
	)
	{
		$this->files = $files;
		$this->errors = $errors;
	}

	/**
	 * @return string[]
	 */
	public function getFiles(): array
	{
		return $this->files;
	}

	/**
	 * @return Error[]
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}

	public function appendError(Error $error): void
	{
		$this->errors[] = $error;
	}

}
