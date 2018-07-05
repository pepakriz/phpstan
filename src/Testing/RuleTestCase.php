<?php declare(strict_types = 1);

namespace PHPStan\Testing;

use PHPStan\Analyser\Analyser;
use PHPStan\Analyser\Error;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Broker\AnonymousClassNameHelper;
use PHPStan\Cache\Cache;
use PHPStan\File\FileHelper;
use PHPStan\PhpDoc\PhpDocStringResolver;
use PHPStan\Rules\Registry;
use PHPStan\Rules\Rule;
use PHPStan\Type\FileTypeMapper;

abstract class RuleTestCase extends \PHPStan\Testing\TestCase
{

	/** @var \PHPStan\Analyser\Analyser */
	private $analyser;

	abstract protected function getRule(): Rule;

	protected function getTypeSpecifier(): TypeSpecifier
	{
		return $this->createTypeSpecifier(
			new \PhpParser\PrettyPrinter\Standard(),
			$this->createBroker(),
			$this->getMethodTypeSpecifyingExtensions(),
			$this->getStaticMethodTypeSpecifyingExtensions()
		);
	}

	private function getAnalyser(): Analyser
	{
		if ($this->analyser === null) {
			$registry = new Registry([
				$this->getRule(),
			]);

			$broker = $this->createBroker();
			$printer = new \PhpParser\PrettyPrinter\Standard();
			$fileHelper = $this->getFileHelper();
			$typeSpecifier = $this->createTypeSpecifier(
				$printer,
				$broker,
				$this->getMethodTypeSpecifyingExtensions(),
				$this->getStaticMethodTypeSpecifyingExtensions()
			);
			$this->analyser = new Analyser(
				$this->createScopeFactory($broker, $typeSpecifier),
				$this->getParser(),
				$registry,
				new NodeScopeResolver(
					$broker,
					$this->getParser(),
					new FileTypeMapper($this->getParser(), self::getContainer()->getByType(PhpDocStringResolver::class), $this->createMock(Cache::class), new AnonymousClassNameHelper(new FileHelper($this->getCurrentWorkingDirectory()))),
					$fileHelper,
					$typeSpecifier,
					$this->shouldPolluteScopeWithLoopInitialAssignments(),
					$this->shouldPolluteCatchScopeWithTryAssignments(),
					[]
				),
				$fileHelper,
				[],
				null,
				true,
				50
			);
		}

		return $this->analyser;
	}

	/**
	 * @return \PHPStan\Type\MethodTypeSpecifyingExtension[]
	 */
	protected function getMethodTypeSpecifyingExtensions(): array
	{
		return [];
	}

	/**
	 * @return \PHPStan\Type\StaticMethodTypeSpecifyingExtension[]
	 */
	protected function getStaticMethodTypeSpecifyingExtensions(): array
	{
		return [];
	}

	/**
	 * @param string[] $files
	 * @param mixed[] $expectedErrors
	 */
	public function analyse(array $files, array $expectedErrors): void
	{
		$files = array_map([$this->getFileHelper(), 'normalizePath'], $files);
		$actualErrors = $this->getAnalyser()->analyse($files, false);

		$strictlyTypedSprintf = function (int $line, string $message): string {
			return sprintf('%02d: %s', $line, $message);
		};

		$expectedErrors = array_map(
			function (array $error): array {
				if (!isset($error[0])) {
					throw new \InvalidArgumentException('Missing expected error message.');
				}
				if (!isset($error[1])) {
					throw new \InvalidArgumentException('Missing expected file line.');
				}
				return $error;
			},
			$expectedErrors
		);

		usort($expectedErrors, function (array $error1, array $error2): int {
			$order = $error1[1] <=> $error2[1];

			if ($order === 0) {
				return $error1[0] <=> $error2[0];
			}

			return $order;
		});

		$expectedErrors = array_map(
			function (array $error) use ($strictlyTypedSprintf): string {
				return $strictlyTypedSprintf($error[1], $error[0]);
			},
			$expectedErrors
		);

		usort($actualErrors, function (Error $error1, Error $error2): int {
			$order = $error1->getLine() <=> $error2->getLine();

			if ($order === 0) {
				return $error1->getMessage() <=> $error2->getMessage();
			}

			return $order;
		});

		$actualErrors = array_map(
			function (Error $error): string {
				return sprintf('%02d: %s', $error->getLine(), $error->getMessage());
			},
			$actualErrors
		);

		$this->assertSame(implode("\n", $expectedErrors), implode("\n", $actualErrors));
	}

	protected function shouldPolluteScopeWithLoopInitialAssignments(): bool
	{
		return false;
	}

	protected function shouldPolluteCatchScopeWithTryAssignments(): bool
	{
		return false;
	}

}
