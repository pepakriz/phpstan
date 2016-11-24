<?php

namespace ReturnTypes;

class NullFoo extends FooParent implements FooInterface
{

	public function returnMissing(): bool
	{
	}

	public function returnMissingWithIfWithoutElse(): bool
	{
		if (rand(0, 10) === 10) {
			throw new \Exception('Foo');
		}
	}

	public function returnMissingWithWhileTrue(): bool
	{
		while (true) {
			$rand = rand(0, 10);
			if ($rand === 10) {
				return true;
			} elseif($rand === 0) {
				break;
			} else {
				return false;
			}
		}
	}

	public function returnMissingWithWhile(): bool
	{
		while (rand(0, 10) === 5) {
			throw new \Exception('Foo');
		}
	}

	public function returnMissingWithDoWhile(): bool
	{
		do {
			$rand = rand(0, 10);
			if ($rand === 10) {
				return true;
			} elseif($rand === 0) {
				break;
			} else {
				return false;
			}
		} while (true);
	}

	public function returnMissingWithSwitch(): bool
	{
		$rand = rand(0, 10);
		switch ($rand) {
			case 1:
				return true;
			case 2:
				echo "i equals 1";
				break;
			case 3:
				echo "i equals 2";
				break;
		}
	}

	public function returnMissingWithSwitchWithoutDefault(): bool
	{
		$rand = rand(0, 10);
		switch ($rand) {
			case 1:
				return true;
			case 2:
				return true;
			case 3:
				return false;
		}
	}

	public function returnMissingInTry(): bool
	{
		try {
		} catch (\Throwable $e) {
			throw $e;
		}
	}

	public function returnMissingInCatch(): bool
	{
		try {
			return true;
		} catch (\Throwable $e) {
		}
	}

	public function returnMissingInInnerWhile(): bool
	{
		while (true) {
			while (true) {
				break 2;
			}
		}
	}

	public function returnMissingInInnerDoWhile(): bool
	{
		do {
			do {
				break 2;
			} while (true);
		} while (true);
	}

	public function returnAbc()
	{
	}

	public function exitByReturn(): bool
	{
		return true;
	}

	public function exitByException(): bool
	{
		throw new \Exception('Foo');
	}

	public function exitInIfElse(): bool
	{
		$rand = rand(0, 10);

		if ($rand === 10) {
			throw new \Exception('Foo');
		} elseif($rand === 0) {
			throw new \Exception('Bar');
		} else {
			throw new \Exception('Err');
		}
	}

	public function exitInWhile(): bool
	{
		while (true) {
			$rand = rand(0, 10);
			if ($rand === 10) {
				return true;
			} elseif($rand === 0) {
				return true;
			} else {
				return false;
			}
		}
	}

	public function exitInWhileInCondition(): bool
	{
		while (true) {
			$rand = rand(0, 10);
			if ($rand === 10) {
				return true;
			}
		}
	}

	public function breakInInnerWhile(): bool
	{
		while (true) {
			while (true) {
				break;
			}
		}
	}

	public function exitInDoWhile(): bool
	{
		do {
			$rand = rand(0, 10);
			if ($rand === 10) {
				return true;
			} elseif($rand === 0) {
				return true;
			} else {
				return false;
			}
		} while (true);
	}

	public function exitInDoWhileInCondition(): bool
	{
		do {
			$rand = rand(0, 10);
			if ($rand === 10) {
				return true;
			}
		} while (true);
	}

	public function breakInInnerDoWhile(): bool
	{
		do {
			do {
				break;
			} while (true);
		} while (true);
	}

	public function exitInSwitch(): bool
	{
		$rand = rand(0, 10);
		switch ($rand) {
			case 1:
				return true;
			case 2:
				return true;
			case 3:
				return true;
			default:
				return false;
			case 4:
				break;
		}
	}

	public function exitInTry(): bool
	{
		try {
			return true;
		} catch (\Throwable $e) {
			throw $e;
		}
	}

	public function exitInTryWithFinally(): bool
	{
		try {
			return true;
		} finally {
		}
	}

	public function exitInCatch(): bool
	{
		try {
			return true;
		} catch (\Throwable $e) {
			return true;
		} finally {

		}
	}

	public function exitInFinnaly(): bool
	{
		try {

		} finally {
			return true;
		}
	}

}
