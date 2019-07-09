<?php declare(strict_types = 1);

namespace PHPStan\Composer;

use Composer\Composer;
use Composer\Config;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Package\AliasPackage;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use function chmod;
use function dirname;
use function file_exists;
use function file_put_contents;
use function rename;
use function sprintf;
use function uniqid;

class Installer implements PluginInterface, EventSubscriberInterface
{

	/** @var string */
	private static $generatedClassTemplate = <<<'PHP'
<?php declare(strict_types = 1);

namespace PHPStan\Analyser\ResultCache;

class ComposerHash
{

	const HASH = '%s';

}

PHP;

	public function activate(Composer $composer, IOInterface $io): void
	{
		// Nothing to do here, as all features are provided through event listeners
	}

	public static function getSubscribedEvents(): array
	{
		return [
			ScriptEvents::POST_AUTOLOAD_DUMP => 'dumpComposerHash',
		];
	}

	public static function dumpComposerHash(Event $composerEvent): void
	{
		$io = $composerEvent->getIO();
		$composer = $composerEvent->getComposer();

		$composerContentHash = $composer->getLocker()->getLockData()['content-hash'];

		$composerHashSource = self::generateComposerHash($composerContentHash);
		self::writeComposerHashToFile($composerHashSource, $composer, $io);
	}

	private static function generateComposerHash(string $composerContentHash): string
	{
		return sprintf(
			self::$generatedClassTemplate,
			$composerContentHash
		);
	}

	private static function writeComposerHashToFile(string $composerHashSource, Composer $composer, IOInterface $io): void
	{
		$installPath = self::locateRootPackageInstallPath($composer->getConfig(), $composer->getPackage())
			. '/src/Analyser/ResultCache/ComposerHash.php';

		if (!file_exists(dirname($installPath))) {
			$io->write('<info>phpstan/phpstan:</info> Package not found (probably scheduled for removal); generation of result cache hash skipped.');
			return;
		}

		$io->write('<info>phpstan/phpstan:</info> Dumping composer content-hash...');

		$installPathTmp = $installPath . '_' . uniqid('tmp', true);
		file_put_contents($installPathTmp, $composerHashSource);
		chmod($installPathTmp, 0664);
		rename($installPathTmp, $installPath);

		$io->write('<info>phpstan/phpstan:</info> ...done dumping content-hash');
	}

	private static function locateRootPackageInstallPath(
		Config $composerConfig,
		RootPackageInterface $rootPackage
	): string
	{
		if (self::getRootPackageAlias($rootPackage)->getName() === 'phpstan/phpstan') {
			return dirname($composerConfig->get('vendor-dir'));
		}

		return $composerConfig->get('vendor-dir') . '/phpstan/phpstan';
	}

	private static function getRootPackageAlias(RootPackageInterface $rootPackage): PackageInterface
	{
		$package = $rootPackage;
		while ($package instanceof AliasPackage) {
			$package = $package->getAliasOf();
		}

		return $package;
	}

}
