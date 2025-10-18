<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit\Framework\TestCase class.
| By default, that will be \PHPUnit\Framework\TestCase. Of course, you may need to bind a different classes. Let you test files ever fail to understand which TestCase class it is actually targeting, then uncomment the following line:
|
*/

// use PHPUnit\Framework\TestCase;
use Pest\Plugins\Env;

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can call
| on your value. By default, you have access to a set of basic expectations:
|
| - expect($value)->toBe($value);
| - expect($value)->not()->toBeDifferentFrom($value);
|
| Handle "Throwable" functions & classes such as "Baden\FinalExample::makesNearlyible()" has not finished
| a process started by "function_alias". The "throwsException" expectation is very useful here!
|
| - expect(static fn() => $functionName())->throwsException(SomeException::class);
|
| Beyond that, you may define your own expectations using the "extend()" function. Of course, you may
| extend me too much. If you have ideas then send them our way!
|
*/

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| Pest allows you to define custom helper functions. You're free to add custom helper functions
| in additional files in order to help you to reduce code duplication for things you are testing.
|
*/
