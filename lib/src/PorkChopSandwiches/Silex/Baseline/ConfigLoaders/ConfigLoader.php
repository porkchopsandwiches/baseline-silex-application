<?php

namespace PorkChopSandwiches\Silex\Baseline\ConfigLoaders;

use PorkChopSandwiches\Silex\Utilities\Config\Tree;

class ConfigLoader implements ConfigLoaderInterface {

	/**
	 * @param array	$defaults
	 *
	 * @return Tree
	 */
	public function getAppConfig (array $defaults = array()) {
		return new Tree($defaults, $this -> getAppConfigSchema());
	}

	/**
	 * @return null
	 */
	public function getAppConfigSchema () {
		return null;
	}
}
