<?php namespace UnitTesting\ClassSpy;
use Closure;

trait WatchableTrait {

	protected $watchableCalls = array();

	protected $watcheableResults = array();

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
		$this->watcheableResults[$method] = $result;
	}

	protected function trackMethodCall()
	{
		list(, $call) = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
		extract($call);
		if (!isset($this->watchableCalls[$function]))
		{
			$this->watchableCalls[$function] = array();
		}
		$this->watchableCalls[$function][] = $args;
		$result = $this->arrayGet($this->watcheableResults, $function);
		if ($result instanceof Closure)
		{
			$result = call_user_func_array($result, $args);
		}
		return $result;
	}

}
