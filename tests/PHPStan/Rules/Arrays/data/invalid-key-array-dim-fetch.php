<?php

namespace InvalidKeyArrayDimFetch;

class StringArrayObject extends \ArrayObject
{

	/**
	 * @param int $index
	 * @param mixed $value
	 */
	public function offsetSet($index, $value)
	{
		parent::offsetSet($index, $value);
	}

}

function (): void {
	$a = [];
	$foo = $a[null];
	$foo = $a[new \DateTimeImmutable()];
	$a[[]] = $foo;
	$a[1];
	$a[1.0];
	$a['1'];
	$a[true];
	$a[false];

	/** @var string|null $stringOrNull */
	$stringOrNull = doFoo();
	$a[$stringOrNull];

	$numbers = [0, 1];
	$numbers[0];
	$numbers['abc'];

	$obj = new \SplObjectStorage();
	$obj[new \stdClass()] = 1;

	$stringArrayObject = new StringArrayObject([null]);
	$stringArrayObject[0];
	$stringArrayObject['abc'];

	$text = 'foo';
	$text[0];
	$text['abc'];

	$scalar = 123;
	$scalar[0];

	$date = new \DateTimeImmutable();
	$date[0];
};
