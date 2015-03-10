<?php

namespace PorkChopSandwiches\Silex\Baseline\ConfigLoaders;

use PorkChopSandwiches\Silex\Utilities\Arrays;
use PorkChopSandwiches\Silex\Utilities\Config\Tree;
use Exception;

class FileConfigLoader extends ConfigLoader {

	/** @var string $root_path */
	protected $root_path;

	/** @var Arrays $arrays */
	protected $arrays;

	public function __construct ($root_path) {
		$this -> root_path = $root_path;
		$this -> arrays = new Arrays();
	}

	/**
	 * @param string	$name
	 *
	 * @return mixed
	 */
	protected function loadConfigFile ($name) {
		return require($this -> root_path . "/config/" . $name . ".php");
	}

	/**
	 * @param array	$defaults
	 *
	 * @throws Exception
	 *
	 * @return Tree
	 */
	public function getAppConfig (array $defaults = array()) {
		$config = $this -> loadConfigFile("config");

		if (!is_array($config)) {
			throw new Exception("Config file must return an array.");
		}

		$config = $this -> arrays -> deepMerge($defaults, $config);
		return new Tree($config, $this -> getAppConfigSchema());
	}
}
