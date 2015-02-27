# UnitTesting\ClassSpy #

In some cases, you may want to mock a class's methods without using testing facility like https://github.com/padraic/mockery. This trait will let you do that.

## Installation ##
* `composer require --dev unit-testing/class-spy`
* or in `require-dev` block of `composer.json`, add `"unit-testing/class-spy": "*"` and then run `composer update`

## Usage ##
In your phpunit test, you'll create a stub of your class under test. In that stub definition, add `use \UnitTesting\ClassSpy\WatchableTrait;`. Your stub will be extended with the following methods:

* `protected trackMethodCall`: For any method you want to test on the stub, you can create (or override its parent) method by calling `return $this->trackMethodCall();` from within the method.
* `getAllMethodCalls`: Returns an array of all the arguments passed to all tracked methods. The array has a list of method names as keys and an array of arguments passed to the method as the value.
* `getMethodCall($method, $index = null)`: Pass the method name to get the arguments passed to it. Optionally add an index (where 1 = the first call) to get the arguments for a specific call if the method was called more than once. If the argument was never called, or was not called less times than the index provided, it will return null.
* `getLastMethodCall($method)`: Get the last set of arguments for a particular method.
* `setMethodResult($method, $result)`: Fake a specific result to be passed back from your tracked method. If the `$result` parameter is an instance `Closure`, the Closure will be executed with the actual parameters and its result will be returned.

## Example ##
```
<?php namespace UnitTesting\ClassSpy;

class WatchableTraitIntegrationTest extends \PHPUnit_Framework_TestCase {

	function test_WatchableTrait_TracksCalls()
	{
		$instance = new SomeTestClassStub;

		// let's make some calls to this method
		$instance->doSomething('foo');
		$instance->doSomething();
		$instance->doSomethingElse('zee');
		$instance->doSomething('baz', 'boo');

		// all the tracked method and their arguments can be retrieved with getAllMethodCalls()
		$this->assertEquals(array(
			'doSomething' => array(
				array('foo'),
				array(),
				array('baz', 'boo'),
			),
			'doSomethingElse' => array(
				array('zee'),
			),
		), $instance->getAllMethodCalls());

		// get a specific method's argument calls
		$this->assertEquals(array(
			array('foo'),
			array(),
			array('baz', 'boo'),
		), $instance->getMethodCalls('doSomething'));

		// get a specific method's argument calls with an index, remember 2 = second call
		$this->assertEquals(array(), $instance->getMethodCalls('doSomething', 2));

		// get the last argument call
		$this->assertEquals(array('baz', 'boo'), $instance->getMethodCalls('doSomething', 'last'));

		// another way to do the above
		$this->assertEquals(array('baz', 'boo'), $instance->getLastMethodCall('doSomething'));

		// trying to access a call on a tracked method with an index beyond the actual number of calls yields null
		$this->assertNull($instance->getMethodCalls('doSomething', 100));

		// something that is never called will yield null
		$this->assertNull($instance->getMethodCalls('doSomethingThatIsNotTracked'));
	}

	function test_WatchableTrait_CanSetResponses()
	{
		$instance = new SomeTestClassStub;
		// let's set up a simple response
		$instance->setMethodResult('doSomething', 'same result every time');

		// it will always return same thing
		$this->assertEquals('same result every time', $instance->doSomething());
		$this->assertEquals('same result every time', $instance->doSomething('foo'));
		$this->assertEquals('same result every time', $instance->doSomething('foo', 'bar'));

		// return something based upon the parameter
		$instance->setMethodResult('doSomething', function($param)
		{
			return $param . ' result';
		});

		// it will now return value based upon our closure
		$this->assertEquals('foo result', $instance->doSomething('foo'));
		$this->assertEquals('bar result', $instance->doSomething('bar'));
	}
}

class SomeTestClassStub {
	// add this trait to set this class up for testing
	use \UnitTesting\ClassSpy\WatchableTrait;

	function doSomething()
	{
		// this will actuall track the method and its arguments.
		// Be sure to return its value if you want to mock some return values.
		return $this->trackMethodCall();
	}

	function doSomethingElse()
	{
		// we'll just set up another one the same as above for illustration purposes.
		return $this->trackMethodCall();
	}
}
```
