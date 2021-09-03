<?php

namespace Cxgphper\Annotation\Mapping;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\Common\Annotations\Annotation\Attributes;

/**
 * @Annotation
 * @Target("METHOD")
 * @Attributes({
 *     @Attribute("key",type="string"),
 *     @Attribute("range", type="int"),
 *     @Attribute("frequency", type="int"),
 * })
 */
class TimeRangeLimiter
{

    /**
     * @var string
     */
    private $key = '';

    /**
     * @var int
     */
    private $range = 5;

    /**
     * @var int
     */
    private $frequency = 1;

    /**
     * @var string
     */
    private $fallback = '';

    /**
     * @var array
     */
    private $config = [];

    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $this->frequency = $values['frequency'] = $values['value'];
            unset($values['value']);
        }
        if (isset($values['key'])) {
            $this->key = $values['key'];
        }
        if (isset($values['range'])) {
            $this->range = $values['range'];
        }
        if (isset($values['frequency'])) {
            $this->frequency = $values['frequency'];
        }
        if (isset($values['fallback'])) {
            $this->fallback = $values['fallback'];
        }
        $this->config = array_keys($values);
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return int
     */
    public function getRange(): int
    {
        return $this->range;
    }

    /**
     * @return int
     */
    public function getFrequency(): int
    {
        return $this->frequency;
    }

    /**
     * @return string
     */
    public function getFallback(): string
    {
        return $this->fallback;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

}
