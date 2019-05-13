<?php declare(strict_types = 1);

namespace PHPStan\Analyser\ResultCache\Strategy;

interface ResultCacheStrategy
{

	public function getFileHash(string $fileName): ?string;

}
