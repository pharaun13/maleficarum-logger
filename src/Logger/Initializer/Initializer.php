<?php
/**
 * This class carries ioc initialization functionality used by this component.
 */
declare (strict_types=1);

namespace Maleficarum\Logger\Initializer;

class Initializer {

	/* ------------------------------------ Class Methods START ---------------------------------------- */

	/**
	 * This method will initialize the entire package.
	 * @return string
	 */
	static public function initialize(array $opts = []) : string {
		// load default builder if skip not requested
		$builders = $opts['builders'] ?? [];
		is_array($builders) or $builders = [];
		if (!isset($builders['logger']['skip'])) {
			\Maleficarum\Ioc\Container::register('Monolog\Logger', function () {
				$logger = new \Monolog\Logger('api');
				$prefix = isset($opts['prefix']) ? $opts['prefix'] : "Maleficarum";
				$handler = new \Monolog\Handler\SyslogHandler('[PHP]['.$prefix.'][Api]', \LOG_USER, \Monolog\Logger::DEBUG, true, \LOG_PID);
				$logger->pushHandler($handler);
				return $logger;
			});
		} 

		// load logger object
		\Maleficarum\Ioc\Container::registerDependency('Maleficarum\Logger', \Maleficarum\Ioc\Container::get('Monolog\Logger'));

		// return initializer name
		return __METHOD__;
	}

	/* ------------------------------------ Class Methods END ------------------------------------------ */

}