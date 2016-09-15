<?php

namespace InternalMethod;

$foo = new Foo();
$foo->internalMethod();

class Bar
{

	public function __construct()
	{
		$foo = new Foo();
		$foo->internalMethod();
	}

}
