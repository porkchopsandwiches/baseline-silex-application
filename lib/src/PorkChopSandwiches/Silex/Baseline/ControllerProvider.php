<?php

namespace PorkChopSandwiches\Silex\Baseline;

use Silex\ControllerProviderInterface;
use Silex\Application as SilexApplication;


abstract class ControllerProvider implements ControllerProviderInterface {

	const DATE_PATTERN = "\\d{4}-\\d{1,2}-\\d{1,2}";
	const INTEGER_PATTERN = "\\d+";

	protected $controller_id = "app.frontend.controllers.controller";
	protected $controller_class	= "\\Vendor\\Project\\Controllers\\Controller";

	/** @var ControllerCollection $collection */
	protected $collection;

	/**
	 * @param SilexApplication $app
	 * @return ControllerCollection
	 */
	public function connect (SilexApplication $app) {
		$this -> collection = Application::getControllersFactory();
		$this -> collection -> setCallbackPrefix($this -> controller_id);

		$app[$this -> controller_id] = $app -> share(function ($app) {
			$class = $this -> controller_class;
			return new $class($app);
		});

		$this -> configureRoutes($this -> collection);
		return $this -> collection;
	}

	/**
	 * @param ControllerCollection $collection
	 */
	abstract protected function configureRoutes(ControllerCollection $collection);
}
