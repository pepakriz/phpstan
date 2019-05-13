<?php declare(strict_types = 1);

namespace PHPStan\Analyser\ResultCache\Strategy;

class MtimeCacheStrategy implements ResultCacheStrategy
{

	public function getFileHash(string $fileName): ?string
	{
		$hash = filemtime($fileName);

		return $hash !== false ? (string) $hash : null;
	}

}
