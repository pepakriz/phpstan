<?php declare(strict_types = 1);

namespace PHPStan\Command;

use Nette\Utils\Json;
use PHPStan\Analyser\ResultCache\ResultCacheFactory;
use PHPStan\Command\ErrorFormatter\ErrorFormatter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AnalyseCommand extends \Symfony\Component\Console\Command\Command
{

	private const NAME = 'analyse';

	public const OPTION_LEVEL = 'level';

	public const DEFAULT_LEVEL = CommandHelper::DEFAULT_LEVEL;

	protected function configure(): void
	{
		$this->setName(self::NAME)
			->setDescription('Analyses source code')
			->setDefinition([
				new InputArgument('paths', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Paths with source code to run analysis on'),
				new InputOption('paths-file', null, InputOption::VALUE_REQUIRED, 'Path to a file with a list of paths to run analysis on'),
				new InputOption('configuration', 'c', InputOption::VALUE_REQUIRED, 'Path to project configuration file'),
				new InputOption(self::OPTION_LEVEL, 'l', InputOption::VALUE_REQUIRED, 'Level of rule options - the higher the stricter'),
				new InputOption(ErrorsConsoleStyle::OPTION_NO_PROGRESS, null, InputOption::VALUE_NONE, 'Do not show progress bar, only results'),
				new InputOption('debug', null, InputOption::VALUE_NONE, 'Show debug information - which file is analysed, do not catch internal errors'),
				new InputOption('autoload-file', 'a', InputOption::VALUE_REQUIRED, 'Project\'s additional autoload file path'),
				new InputOption('error-format', null, InputOption::VALUE_REQUIRED, 'Format in which to print the result of the analysis', 'table'),
				new InputOption('memory-limit', null, InputOption::VALUE_REQUIRED, 'Memory limit for analysis'),
				new InputOption('use-result-cache', null, InputOption::VALUE_NONE, 'Use result cache'),
				new InputOption('result-cache-strategy', null, InputOption::VALUE_REQUIRED, 'Result cache strategy'),
				new InputOption('result-cache-key', null, InputOption::VALUE_REQUIRED, 'Result cache key'),
			]);
	}

	/**
	 * @return string[]
	 */
	public function getAliases(): array
	{
		return ['analyze'];
	}

	protected function initialize(InputInterface $input, OutputInterface $output): void
	{
		if ((bool) $input->getOption('debug')) {
			$this->getApplication()->setCatchExceptions(false);
			return;
		}
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$paths = $input->getArgument('paths');
		$memoryLimit = $input->getOption('memory-limit');
		$autoloadFile = $input->getOption('autoload-file');
		$configuration = $input->getOption('configuration');
		$level = $input->getOption(self::OPTION_LEVEL);
		$pathsFile = $input->getOption('paths-file');
		$useResultCache = $input->getOption('use-result-cache');
		$resultCacheStrategy = $input->getOption('result-cache-strategy');
		$resultCacheKey = $input->getOption('result-cache-key');

		if (
			!is_array($paths)
			|| (!is_string($memoryLimit) && $memoryLimit !== null)
			|| (!is_string($autoloadFile) && $autoloadFile !== null)
			|| (!is_string($configuration) && $configuration !== null)
			|| (!is_string($level) && $level !== null)
			|| (!is_string($pathsFile) && $pathsFile !== null)
			|| (!is_bool($useResultCache))
			|| (!is_string($resultCacheStrategy) && $resultCacheStrategy !== null)
			|| (!is_string($resultCacheKey) && $resultCacheKey !== null)
		) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		try {
			$inceptionResult = CommandHelper::begin(
				$input,
				$output,
				$paths,
				$pathsFile,
				$memoryLimit,
				$autoloadFile,
				$configuration,
				$level
			);
		} catch (\PHPStan\Command\InceptionNotSuccessfulException $e) {
			return 1;
		}

		$errorOutput = $inceptionResult->getErrorOutput();
		$errorFormat = $input->getOption('error-format');

		if (!is_string($errorFormat) && $errorFormat !== null) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$container = $inceptionResult->getContainer();
		$errorFormatterServiceName = sprintf('errorFormatter.%s', $errorFormat);
		if (!$container->hasService($errorFormatterServiceName)) {
			$errorOutput->writeln(sprintf(
				'Error formatter "%s" not found. Available error formatters are: %s',
				$errorFormat,
				implode(', ', array_map(static function (string $name): string {
					return substr($name, strlen('errorFormatter.'));
				}, $container->findServiceNamesByType(ErrorFormatter::class)))
			));
			return 1;
		}

		/** @var ErrorFormatter $errorFormatter */
		$errorFormatter = $container->getService($errorFormatterServiceName);

		/** @var AnalyseApplication  $application */
		$application = $container->getByType(AnalyseApplication::class);

		$debug = $input->getOption('debug');
		if (!is_bool($debug)) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		/** @var \PHPStan\Analyser\ResultCache\ResultCache|null $analyserResultCache */
		$analyserResultCache = null;

		if ($useResultCache) {
			if ($resultCacheStrategy === null) {
				$resultCacheStrategy = getenv('CI') !== false ? 'md5' : 'mtime';
			}

			if ($resultCacheKey === null) {
				$resultCacheKey = sha1(Json::encode([
					$paths,
					$autoloadFile,
					$configuration,
					$level,
					$pathsFile,
				]));
			}

			$resultCacheStrategyServiceName = sprintf('resultCacheStrategy.%s', $resultCacheStrategy);
			if (!$container->hasService($resultCacheStrategyServiceName)) {
				$errorOutput->writeln(sprintf(
					'Unsupported result cache strategy %s.',
					$resultCacheStrategy
				));
				return 1;
			}

			/** @var \PHPStan\Analyser\ResultCache\Strategy\ResultCacheStrategy $resultCacheStrategy */
			$resultCacheStrategy = $container->getService($resultCacheStrategyServiceName);

			/** @var ResultCacheFactory $analyserResultCacheFactory */
			$analyserResultCacheFactory = $container->getByType(ResultCacheFactory::class);
			$analyserResultCache = $analyserResultCacheFactory->create($resultCacheStrategy, $resultCacheKey);
		}

		return $inceptionResult->handleReturn(
			$application->analyse(
				$inceptionResult->getFiles(),
				$inceptionResult->isOnlyFiles(),
				$inceptionResult->getConsoleStyle(),
				$errorFormatter,
				$inceptionResult->isDefaultLevelUsed(),
				$analyserResultCache,
				$debug,
				$inceptionResult->getProjectConfigFile()
			)
		);
	}

}
