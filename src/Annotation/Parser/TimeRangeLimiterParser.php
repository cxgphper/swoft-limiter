<?php

namespace Cxgphper\Annotation\Parser;

use Cxgphper\Annotation\Mapping\TimeRangeLimiter;
use Cxgphper\TimeRangeLimiterRegister;
use Swoft\Annotation\Annotation\Mapping\AnnotationParser;
use Swoft\Annotation\Annotation\Parser\Parser;
use Cxgphper\Exception\TimeRangeLimiterException;

/**
 * @AnnotationParser(annotation=TimeRangeLimiter::class)
 */
class TimeRangeLimiterParser extends Parser
{
    /**
     * Parse object
     *
     * @param int $type Class or Method or Property
     * @param object $annotationObject Annotation object
     * @return array
     * Return empty array is nothing to do!
     * When class type return [$beanName, $className, $scope, $alias] is to inject bean
     * When property type return [$propertyValue, $isRef] is to reference value
     * @throws TimeRangeLimiterException
     */
    public function parse(int $type, $annotationObject): array
    {
        if ($type != self::TYPE_METHOD) {
            return [];
        }

        TimeRangeLimiterRegister::registerRateLimiter($this->className, $this->methodName, $annotationObject);
        return [];
    }
}
