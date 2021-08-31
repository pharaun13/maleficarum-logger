<?php
/**
 * This class carries ioc initialization functionality used by this component.
 *
 * Container options:
 * - string 'prefix' (optional) 'Maleficarum' by default; Will use given prefix in log identifier.
 * - string 'logger.message_prefix' (optional) if provided will result in given prefix being added to every log message
 */
declare (strict_types=1);

namespace Maleficarum\Logger\Initializer;

class Initializer {
    /* ------------------------------------ Class Methods START ---------------------------------------- */

    private static $defaultPlugins = [
        ['\Maleficarum\ContextTracing\Plugin\MaleficarumMonologLogger', 'addProcessor']
    ];
    /**
     * This method will initialize the entire package.
     *
     * @param array $opts Container options
     *
     * @return string
     */
    static public function initialize(array $opts = []): string {
        // load default builder if skip not requested
        $builders = $opts['builders'] ?? [];
        is_array($builders) or $builders = [];
        if (!isset($builders['logger']['skip'])) {
            \Maleficarum\Ioc\Container::registerBuilder('Monolog\Logger', function () use ($opts) {
                $logger = new \Monolog\Logger('api');
                $prefix = isset($opts['prefix']) ? $opts['prefix'] : "Maleficarum";
                $handler = new \Monolog\Handler\SyslogHandler('[PHP][' . $prefix . '][Api]', \LOG_USER,
                    \Monolog\Logger::DEBUG, true, \LOG_PID);
                $logger->pushHandler($handler);
                if (isset($opts['logger.message_prefix'])) {
                    self::addMessagePrefixProcessor($logger, $opts['logger.message_prefix']);
                }

                if (isset($opts['logger.plugins'])) {
                    if (!is_array($opts['logger.plugins'])) {
                        throw new \InvalidArgumentException('logger.plugins option must be an array of callables, given: ' . gettype($opts['logger.plugins']));
                    }
                    foreach ($opts['logger.plugins'] as $plugin) {
                        if(!is_callable($plugin)) {
                            throw new \InvalidArgumentException(
                                sprintf('Logger plugins must be callables, %s is not callable', $plugin)
                            );
                        }
                        $plugin($logger);
                    }
                }
                foreach (self::$defaultPlugins as $plugin) {
                    $class = $plugin[0];
                    if (class_exists($class)) {
                        $plugin($logger);
                    }
                }

                return $logger;
            });
        }

        // load logger object
        \Maleficarum\Ioc\Container::registerShare('Maleficarum\Logger', \Maleficarum\Ioc\Container::get('Monolog\Logger'));

        // return initializer name
        return __METHOD__;
    }

    /**
     * @param \Monolog\Logger $logger
     * @param string $messagePrefix
     */
    private static function addMessagePrefixProcessor(\Monolog\Logger $logger, string $messagePrefix) {
        $logger->pushProcessor(function (array $record) use ($messagePrefix) {
            $record['message'] = $messagePrefix . $record['message'];

            return $record;
        });
    }

    /* ------------------------------------ Class Methods END ------------------------------------------ */
}
