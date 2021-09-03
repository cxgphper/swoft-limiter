<?php declare(strict_types=1);

namespace Cxgphper;

use Cxgphper\Exception\TimeRangeLimiterException;
use ReflectionException;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Redis\Redis;
use Swoft\Stdlib\Helper\Arr;
use Swoft\Stdlib\Helper\PhpHelper;
use Swoft\Stdlib\Reflections;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * @Bean("timeRangeLimterHandler")
 */
class TimeRangeLimterHandler
{
    /**
     * @var int
     */
    private $range = 5;

    /**
     * @var int
     */
    private $frequency = 1;

    /**
     * 检查指定时间范围内的请求频率(请求次数)
     * @param callable|array $callback
     * @param string $className
     * @param string $method
     * @param $target
     * @param array $params
     *
     * @return mixed
     * @throws TimeRangeLimiterException|ReflectionException
     */
    public function checkFrequency($callback, string $className, string $method, $target, array $params)
    {
        $config = TimeRangeLimiterRegister::getRateLimiter($className, $method);
        $key = $config['key'] ?? '';

        // 解析key
        if (!empty($key)) {
            $key = $this->evaluateKey($key, $className, $method, $params);
        }

        $commonConfig = [
            'range' => $this->range,
            'frequency' => $this->frequency,
        ];

        // Default Key
        if (empty($key)) {
            $key = md5(sprintf('%s:%s', $className, $method));
        }

        $config['key'] = $key;

        $config = Arr::merge($commonConfig, $config);
        $fallback = $config['fallback'] ?? '';

        if ($method == $fallback) {
            throw new TimeRangeLimiterException(sprintf('Method(%s) and fallback must be different', $method));
        }

        $isAvailable = true;
        if (Redis::exists($key)) {
            $submitCount = Redis::get($key);
            if ($submitCount >= $config['frequency']) {
                $isAvailable = false;
            }
            $submitCount++;
            Redis::set($key, $submitCount, $config['range']);
        } else {
            Redis::set($key, 1, $config['range']);
        }

        // 正常的情况下
        if ($isAvailable) {
            return PhpHelper::call($callback);
        }

        // 有指定回调
        if (!empty($fallback)) {
            return PhpHelper::call([$target, $fallback], ...$params);
        }

        // 没指定回调，直接抛出异常
        throw new TimeRangeLimiterException(sprintf('Time Range frequency (%s->%s) to Limit!', $className, $method));
    }

    /**
     * 求key的值
     * @param string $key
     * @param string $className
     * @param string $method
     * @param array $params
     *
     * @return string
     * @throws ReflectionException
     */
    private function evaluateKey(string $key, string $className, string $method, array $params): string
    {
        $values = [];
        $rcMethod = Reflections::get($className);
        $rcParams = $rcMethod['methods'][$method]['params'] ?? [];

        $index = 0;
        foreach ($rcParams as $rcParam) {
            [$pName] = $rcParam;
            $values[$pName] = $params[$index];
            $index++;
        }

        // Inner vars
        $values['CLASS'] = $className;
        $values['METHOD'] = $method;

        // Parse express language
        $el = new ExpressionLanguage();
        return $el->evaluate($key, $values);
    }
}
