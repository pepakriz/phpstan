<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PHPStan\Type\Type;

class VoidReturnStatementException extends \PHPStan\AnalysedCodeException
{

	/** @var Type */
	private $type;

	/** @var Type */
	private $returnType;

	public function __construct(Type $type, Type $returnType)
	{
		parent::__construct('Void return statement');
		$this->type = $type;
		$this->returnType = $returnType;
	}

	public function getType(): Type
	{
		return $this->type;
	}

	public function getReturnType(): Type
	{
		return $this->returnType;
	}

}
