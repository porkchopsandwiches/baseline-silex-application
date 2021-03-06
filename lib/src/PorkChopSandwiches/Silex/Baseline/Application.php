<?php

namespace PorkChopSandwiches\Silex\Baseline;

use PorkChopSandwiches\PreserialiserServiceProvider\PreserialiserServiceProvider;
use PorkChopSandwiches\Silex\Utilities\Config\Tree;
use Silex\Application as SilexApplication;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use PorkChopSandwiches\Silex\Utilities\Arrays;
use PorkChopSandwiches\Preserialiser\Preserialiser;
use Monolog\Logger;
use Twig_Environment;
use Symfony\Component\EventDispatcher\EventDispatcher;
use PorkChopSandwiches\Silex\Baseline\ConfigLoaders\ConfigLoaderInterface;
use PorkChopSandwiches\Silex\Utilities\Config\Exceptions\InvalidKeyException;
use PorkChopSandwiches\Silex\Utilities\Config\Exceptions\NonExistentKeyException;

/**
 * Class Application
 * @abstract
 */
class Application extends SilexApplication {

	/** @var ConfigLoaderInterface */
	protected $config_loader;

	/**
	 * @param ConfigLoaderInterface $config_loader
	 * @return $this
	 */
	public function setConfigLoader (ConfigLoaderInterface $config_loader) {
		$this -> config_loader = $config_loader;
		return $this;
	}

	# -----------------------------------------------------
	# Singleton handling
	# -----------------------------------------------------

	/** @var Application $app */
	static protected $app = null;

	/**
	 * @return Application
	 */
	static public function getInstance () {
		if (is_null(self::$app)) {
			self::$app = new static();
		}

		return self::$app;
	}

	# -----------------------------------------------------
	# Accessor methods
	# -----------------------------------------------------

	/**
	 * @return Arrays
	 */
	static public function getArraysService () {
		return self::$app["app.arrays"];
	}

	/**
	 * @return Session
	 */
	static public function getSession () {
		return self::$app["session"];
	}

	/**
	 * @return Logger
	 */
	static public function getMonolog () {
		return self::$app["monolog"];
	}

	/**
	 * @return Preserialiser
	 */
	static public function getPreserialiser () {
		return self::$app["preserialiser"];
	}

	/**
	 * @return Twig_Environment
	 */
	static public function getTwig () {
		return self::$app["twig"];
	}

	/**
	 * @return EventDispatcher
	 */
	static public function getDispatcher () {
		return self::$app["dispatcher"];
	}

	/**
	 * @return ControllerCollection
	 */
	static public function getControllersFactory () {
		return new ControllerCollection(self::$app["route_factory"]);
	}

	/**
	 * @return mixed|Tree
	 *
	 * @throws InvalidKeyException
	 * @throws NonExistentKeyException
	 */
	static public function getAppConfig () {
		$args = func_get_args();

		/** @var Tree $config */
		$config = self::$app["app.config"];

		if (!count($args)) {
			return $config;
		} else {
			return $config[implode(".", $args)];
		}
	}

	# -----------------------------------------------------
	# Configuration
	# -----------------------------------------------------

	/**
	 * @return array
	 */
	protected function getBaselineConfig () {
		return array(
			"environment"	=> array(
				"debug"			=> false,
				"debug_log"		=> false
			),
			"monolog" => array(
				"path" => "monolog.log"
			),
			"session" => array(
				"enabled"	=> false
			),
			"twig" => array(
				"enabled" => true,
				"path" => "source/twig/views"
			)
		);
	}

	/**
	 * Prepares the App configuration.
	 */
	final protected function bootstrapConfig () {
		$this["app.config"]	= $this -> config_loader -> getAppConfig($this -> getBaselineConfig());
	}

	# -----------------------------------------------------
	# App Environment
	# -----------------------------------------------------

	/**
	 * Prepare the environment, registering the Error and Exception handlers, and allowing HTTP method parameter overriding.
	 */
	protected function bootstrapEnvironment () {
		$this["debug"]		= !!$this["app.config"]["environment.debug"];
		Errorhandler::register();
		ExceptionHandler::register($this["debug"]);
		Request::enableHttpMethodParameterOverride();
	}

	# -----------------------------------------------------
	# Logging
	# -----------------------------------------------------

	/**
	 * Set up Monolog if logging is enabled.
	 */
	final protected function bootstrapLogging () {
		if ($this -> isLoggingEnabled()) {
			$this -> configureLogging();
		}
	}

	/**
	 * @return bool
	 */
	final protected function isLoggingEnabled () {
		return $this["debug"] && $this["app.config"]["environment.debug_log"];
	}

	/**
	 * Register and configure the MonologServiceProvider
	 */
	protected function configureLogging () {
		$this -> register(new MonologServiceProvider(), array(
			"monolog.logfile"	=> $this["app.config"]["monolog.path"],
			"monolog.name"		=> "App"
		));
	}

	# -----------------------------------------------------
	# Session
	# -----------------------------------------------------

	/**
	 * Set up the Session provider if Sessions are enabled.
	 */
	final protected function bootstrapSession () {
		if ($this -> isSessionEnabled()) {
			$this -> configureSession();
		}
	}

	/**
	 * @return bool
	 */
	final protected function isSessionEnabled () {
		return !!$this["app.config"]["session.enabled"];
	}

	/**
	 * Register and configure the SessionServiceProvider
	 */
	protected function configureSession () {
		$this -> register(new SessionServiceProvider());
	}

	# -----------------------------------------------------
	# Twig
	# -----------------------------------------------------

	/**
	 * Set up the Twig provider if Twig is enabled.
	 */
	protected function bootstrapTwig () {
		if ($this -> isTwigEnabled()) {
			$this -> configureTwig();
		}
	}

	/**
	 * @return bool
	 */
	protected function isTwigEnabled () {
		return !!$this["app.config"]["twig.enabled"];
	}

	/**
	 * Register and configure the TwigServiceProvider.
	 */
	protected function configureTwig () {
		$this -> register(new TwigServiceProvider(), array(
			"twig.path"	=> $this["app.config"]["twig.path"]
		));
	}

	# -----------------------------------------------------
	# Booting
	# -----------------------------------------------------

	protected function bootstrapInternalServices () {
		$this["app.arrays"] = $this -> share(function () {
			return new Arrays();
		});

		$this -> register(new PreserialiserServiceProvider(), array(
			"preserialiser.default_args"    => array(
				"app"		=> $this
			)
		));
	}

	public function bootstrap () {
		$this -> bootstrapInternalServices();
		$this -> bootstrapConfig();
		$this -> bootstrapEnvironment();
		$this -> bootstrapLogging();
		$this -> bootstrapSession();
		$this -> bootstrapTwig();
	}
}
