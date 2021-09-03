<?php

namespace Cxgphper\Aspect;

use Cxgphper\Exception\TimeRangeLimiterException;
use Cxgphper\TimeRangeLimterHandler;
use Swoft\Aop\Annotation\Mapping\Before;
use Swoft\Aop\Annotation\Mapping\Aspect;
use Swoft\Aop\Annotation\Mapping\PointAnnotation;
use Swoft\Aop\Point\ProceedingJoinPoint;
use Swoft\Aop\Proxy;
use Swoft\Bean\Annotation\Mapping\Inject;
use Cxgphper\Annotation\Mapping\TimeRangeLimiter;
use ReflectionException;

/**
 * @Aspect()
 * @PointAnnotation(
 *     include={TimeRangeLimiter::class}
 * )
 */
class TimeRangeLimiterAspect
{
    /**
     * @Inject()
     *
     * @var TimeRangeLimterHandler
     */
    private $timeRangeLimterHandler;

    /**
     * @Before()
     *
     * @param ProceedingJoinPoint $proceedingJoinPoint
     *
     * @return mixed
     * @throws TimeRangeLimiterException|ReflectionException
     */
    public function beforeAdvice(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $args = $proceedingJoinPoint->getArgs();
        $target = $proceedingJoinPoint->getTarget();
        $method = $proceedingJoinPoint->getMethod();
        $className = get_class($target);
        $className = Proxy::getOriginalClassName($className);

        return $this->timeRangeLimterHandler->checkFrequency([$proceedingJoinPoint, 'proceed'], $className, $method, $target, $args);
    }
}
