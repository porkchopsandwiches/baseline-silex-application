<?php

namespace PorkChopSandwiches\Silex\Baseline\ConfigLoaders;

use PorkChopSandwiches\Silex\Utilities\Config\SchemaInterface;
use PorkChopSandwiches\Silex\Utilities\Config\Tree;

interface ConfigLoaderInterface {

	/**
	 * @param array	$defaults
	 *
	 * @return Tree
	 */
	public function getAppConfig (array $defaults = array());

	/**
	 * @return SchemaInterface|null
	 */
	public function getAppConfigSchema ();
}
