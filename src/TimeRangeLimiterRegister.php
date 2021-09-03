<?php

namespace Cxgphper;

use Cxgphper\Exception\TimeRangeLimiterException;
use Cxgphper\Annotation\Mapping\TimeRangeLimiter;

class TimeRangeLimiterRegister
{
    /**
     * @var array
     *
     * @example
     * [
     *     'className' => [
     *         'method' => [
     *              'key' => '',
     *              'range' => '',
     *              'frequency' => '',
     *         ],
     *     ]
     * ]
     */
    private static $rateLimiters = [];

    /**
     * @param string $className
     * @param string $method
     * @param TimeRangeLimiter $timeRangeLimiter
     *
     * @throws TimeRangeLimiterException
     */
    public static function registerRateLimiter(string $className, string $method, TimeRangeLimiter $timeRangeLimiter): void
    {
        if (isset(self::$rateLimiters[$className][$method])) {
            throw new TimeRangeLimiterException(sprintf(
                '`@TimeRangeLimiter` must be only one on method(%s->%s)!',
                $className,
                $method
            ));
        }

        $rlConfig = [];
        $config = $timeRangeLimiter->getConfig();
        foreach ($config as $key) {
            $configMethod = sprintf('get%s', ucfirst($key));
            $rlConfig[$key] = $timeRangeLimiter->{$configMethod}();
        }

        self::$rateLimiters[$className][$method] = $rlConfig;
    }

    /**
     * @param string $className
     * @param string $method
     *
     * @return array
     */
    public static function getRateLimiter(string $className, string $method): array
    {
        return self::$rateLimiters[$className][$method];
    }

}
