<?php

namespace InternalMethodInOneClass;

class Foo
{

	public function __construct()
	{
		$this->internalMethod();
	}

	/**
	 * @internal
	 */
	public function internalMethod()
	{

	}

}
