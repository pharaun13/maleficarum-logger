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

use Maleficarum\Logger\RidProvider;

class Initializer {
    /* ------------------------------------ Class Methods START ---------------------------------------- */

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
                if(!isset($opts['logger.channel_name'])) {
                    throw new \Exception('missing loggers channel name');
                }
                if(!isset($opts['logger.component'])) {
                    throw new \Exception('missing loggers component name');
                }

                if (!isset($opts['logger.rid_provider']) || !($opts['logger.rid_provider'] instanceof RidProvider)) {
                    throw new \Exception('Missing loggers rid provider or wrong type');
                }

                $opts['logger.min_level'] = $opts['logger.min_level'] ?? \Monolog\Logger::DEBUG;

                $logger = new \Monolog\Logger($opts['logger.channel_name']);

                $handler = new \Monolog\Handler\SyslogHandler('[PHP][' . $opts['logger.rid_provider']->getRid() . '][' .$opts['logger.component']. ']', \LOG_USER,
                    $opts['logger.min_level'], true, \LOG_PID);

                $logger->pushHandler($handler);

                if (isset($opts['logger.message_prefix'])) {
                    self::addMessagePrefixProcessor($logger, $opts['logger.message_prefix']);
                }

                if(!empty($opts['logger.processors'])) {
                    foreach($opts['logger.processors'] as $processor) {
                        if (!is_callable($processor)) {
                            throw new \Exception('Wrong logger config, processor is not a callable');
                        }
                        $logger->pushProcessor($processor);
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
