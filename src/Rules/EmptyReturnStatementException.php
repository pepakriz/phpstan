<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PHPStan\Type\Type;

class EmptyReturnStatementException extends \PHPStan\AnalysedCodeException
{

	/** @var Type */
	private $type;

	public function __construct(Type $type)
	{
		parent::__construct('Empty return statement');
		$this->type = $type;
	}

	public function getType(): Type
	{
		return $this->type;
	}

}
