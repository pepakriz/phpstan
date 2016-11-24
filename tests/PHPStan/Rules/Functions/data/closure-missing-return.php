<?php

namespace ReturnTypes;

$returnMissing = function (): bool {
};

$returnMissingWithIfWithoutElse = function (): bool {
	if (rand(0, 10) === 10) {
		throw new \Exception('Foo');
	}
};

$returnMissingWithWhileTrue = function (): bool {
	while (true) {
		$rand = rand(0, 10);
		if ($rand === 10) {
			return true;
		} elseif ($rand === 0) {
			break;
		} else {
			return false;
		}
	}
};

$returnMissingWithWhile = function (): bool {
	while (rand(0, 10) === 5) {
		throw new \Exception('Foo');
	}
};

$returnMissingWithDoWhile = function (): bool {
	do {
		$rand = rand(0, 10);
		if ($rand === 10) {
			return true;
		} elseif ($rand === 0) {
			break;
		} else {
			return false;
		}
	} while (true);
};

$returnMissingWithSwitch = function (): bool {
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
};

$returnMissingWithSwitchWithoutDefault = function (): bool {
	$rand = rand(0, 10);
	switch ($rand) {
		case 1:
			return true;
		case 2:
			return true;
		case 3:
			return false;
	}
};

$returnMissingInTry = function (): bool {
	try {
	} catch (\Throwable $e) {
		throw $e;
	}
};

$returnMissingInCatch = function (): bool {
	try {
		return true;
	} catch (\Throwable $e) {
	}
};

$returnMissingInInnerWhile = function (): bool {
	while (true) {
		while (true) {
			break 2;
		}
	}
};

$returnMissingInInnerDoWhile = function (): bool {
	do {
		do {
			break 2;
		} while (true);
	} while (true);
};

$returnAbc = function () {
};

$exitByReturn = function (): bool {
	return true;
};

$exitByException = function (): bool {
	throw new \Exception('Foo');
};

$exitInIfElse = function (): bool {
	$rand = rand(0, 10);

	if ($rand === 10) {
		throw new \Exception('Foo');
	} elseif ($rand === 0) {
		throw new \Exception('Bar');
	} else {
		throw new \Exception('Err');
	}
};

$exitInWhile = function (): bool {
	while (true) {
		$rand = rand(0, 10);
		if ($rand === 10) {
			return true;
		} elseif ($rand === 0) {
			return true;
		} else {
			return false;
		}
	}
};

$exitInWhileInCondition = function (): bool {
	while (true) {
		$rand = rand(0, 10);
		if ($rand === 10) {
			return true;
		}
	}
};

$breakInInnerWhile = function (): bool {
	while (true) {
		while (true) {
			break;
		}
	}
};

$exitInDoWhile = function (): bool {
	do {
		$rand = rand(0, 10);
		if ($rand === 10) {
			return true;
		} elseif ($rand === 0) {
			return true;
		} else {
			return false;
		}
	} while (true);
};

$exitInDoWhileInCondition = function (): bool {
	do {
		$rand = rand(0, 10);
		if ($rand === 10) {
			return true;
		}
	} while (true);
};

$breakInInnerDoWhile = function (): bool {
	do {
		do {
			break;
		} while (true);
	} while (true);
};

$exitInSwitch = function (): bool {
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
};

$exitInTry = function (): bool {
	try {
		return true;
	} catch (\Throwable $e) {
		throw $e;
	}
};

$exitInTryWithFinally = function (): bool {
	try {
		return true;
	} finally {
	}
};

$exitInCatch = function (): bool {
	try {
		return true;
	} catch (\Throwable $e) {
		return true;
	} finally {

	}
};

$exitInFinnaly = function (): bool {
	try {

	} finally {
		return true;
	}
};
