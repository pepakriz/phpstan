<?php declare(strict_types = 1);

namespace PHPStan\Analyser\ResultCache\Strategy;

class Md5CacheStrategy implements ResultCacheStrategy
{

	public function getFileHash(string $fileName): ?string
	{
		$hash = md5_file($fileName);

		return $hash !== false ? $hash : null;
	}

}
