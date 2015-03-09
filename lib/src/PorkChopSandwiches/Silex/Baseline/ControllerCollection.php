<?php

namespace PorkChopSandwiches\Silex\Baseline;

use Silex\ControllerCollection as SilexControllerCollection;
use Silex\Controller;

/**
 * Class ControllerCollection
 *
 * @method ControllerCollection assert(string $variable, string $regexp)
 * @method ControllerCollection value(string $variable, mixed $default)
 * @method ControllerCollection method(string $method)
 * @method ControllerCollection requireHttp()
 * @method ControllerCollection requireHttps()
 * @method ControllerCollection before(mixed $callback)
 * @method ControllerCollection after(mixed $callback)
 */
class ControllerCollection extends SilexControllerCollection {

	/** @var string $callback_prefix */
	private $callback_prefix = "";

	/**
	 * @param string	$callback_prefix
	 * @return $this
	 */
	public function setCallbackPrefix ($callback_prefix) {
		$this -> callback_prefix = $callback_prefix;
		return $this;
	}

	/**
	 * @param string $variable
	 * @param mixed $callback
	 * @return $this
	 */
	public function convert ($variable, $callback) {

		if (is_string($callback)) {
			$callback = $this -> callback_prefix . ":" . $callback;
		}

		parent::convert($variable, $callback);

		return $this;
	}

	private function configureController (Controller $controller, $route_name = null, $before = null, $after = null) {
		if ($route_name) {
			$controller -> bind($route_name);
		}

		if ($before) {
			$controller -> before($this -> callback_prefix . ":" . $before);
		}

		if ($after) {
			$controller -> after($this -> callback_prefix . ":" . $after);
		}
	}

	/**
	 * @param string	$pattern
	 * @param string	$to
	 * @param string	$route_name
	 * @param string	$before
	 * @param string	$after
	 * @return $this
	 */
	public function addGet ($pattern, $to = null, $route_name = null, $before = null, $after = null) {
		$controller = $this -> get($pattern, $this -> callback_prefix . ":" . $to);
		$this -> configureController($controller, $route_name, $before, $after);

		return $this;
	}

	/**
	 * @param string	$pattern
	 * @param string	$to
	 * @param string	$route_name
	 * @param string	$before
	 * @param string	$after
	 * @return $this
	 */
	public function addPost ($pattern, $to = null, $route_name = null, $before = null, $after = null) {
		$controller = $this -> post($pattern, $this -> callback_prefix . ":" . $to);
		$this -> configureController($controller, $route_name, $before, $after);
		return $this;
	}

	/**
	 * @param string	$pattern
	 * @param string	$to
	 * @param string	$route_name
	 * @param string	$before
	 * @param string	$after
	 * @return $this
	 */
	public function addPut ($pattern, $to = null, $route_name = null, $before = null, $after = null) {
		$controller = $this -> put($pattern, $this -> callback_prefix . ":" . $to);
		$this -> configureController($controller, $route_name, $before, $after);
		return $this;
	}

	/**
	 * @param string	$pattern
	 * @param string	$to
	 * @param string	$route_name
	 * @param string	$before
	 * @param string	$after
	 * @return $this
	 */
	public function addDelete ($pattern, $to = null, $route_name = null, $before = null, $after = null) {
		$controller = $this -> delete($pattern, $this -> callback_prefix . ":" . $to);
		$this -> configureController($controller, $route_name, $before, $after);
		return $this;
	}

	/**
	 * @param array		$methods
	 * @param string	$pattern
	 * @param string	$to
	 * @param string	$route_name
	 * @param string	$before
	 * @param string	$after
	 * @return $this
	 */
	public function addMatch (array $methods, $pattern, $to = null, $route_name = null, $before = null, $after = null) {
		$controller = $this -> match($pattern, $this -> callback_prefix . ":" . $to) -> method(implode("|", $methods));
		$this -> configureController($controller, $route_name, $before, $after);
		return $this;
	}
}
