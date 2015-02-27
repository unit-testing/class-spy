<?php namespace UnitTesting\ClassSpy;
use Closure;

trait WatchableTrait {

	protected static $instances;

	protected $watchableCalls = array();

	protected $watchableResults = array();

	public function getAllMethodCalls()
	{
		return $this->watchableCalls;
	}

	public function getMethodCalls($method, $index = null)
	{
		$result = $this->arrayGet($this->watchableCalls, $method);
		if ($index and $result)
		{
			$index = $index === 'last' ? count($result) : $index;
			$result = $this->arrayGet($result, $index - 1);
		}
		return $result;
	}

	public function getLastMethodCall($method)
	{
		return $this->getMethodCalls($method, 'last');
	}

	private function arrayGet(array $array, $key)
	{
		return isset($array[$key]) ? $array[$key] : null;
	}

	public function setMethodResult($method, $result)
	{
		$this->watchableResults[$method] = $result;
	}

	public function trackMethodCall($function = null, array $args = array())
	{
		if (!$function)
		{
			list($function, $args) = self::extractFunctionAndArgs();
		}
		if (!isset($this->watchableCalls[$function]))
		{
			$this->watchableCalls[$function] = array();
		}
		$this->watchableCalls[$function][] = $args;
		$result = $this->arrayGet($this->watchableResults, $function);
		if ($result instanceof Closure)
		{
			$result = call_user_func_array($result, $args);
		}
		return $result;
	}

	protected static function extractFunctionAndArgs()
	{
		list(, , $call) = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3);
		return array($call['function'], $call['args']);
	}

	protected static function resolveInstance()
	{
		if (!self::$instances)
		{
			self::$instances = new StaticShim;
		}
		return self::$instances;
	}

	protected static function trackStaticMethodCall()
	{
		$instances = self::resolveInstance();
		list($function, $args) = self::extractFunctionAndArgs();
		return $instances->trackMethodCall($function, $args);
	}

	public static function flushStatic()
	{
		self::$instances = null;
	}

	public static function __callStatic($method, array $args)
	{
		$instances = self::resolveInstance();
		$method = str_replace('Static', '', $method);
		return call_user_func_array(array($instances, $method), $args);
	}

}
