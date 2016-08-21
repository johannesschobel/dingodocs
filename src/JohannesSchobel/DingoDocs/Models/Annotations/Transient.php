<?php

namespace JohannesSchobel\DingoDocs\Models\Annotations;

/**
 * Class Transient
 * @package JohannesSchobel\DingoDocs\Models\Annotations
 * @Annotation
 */
class Transient
{
    /**
     * If the route is visible or not
     *
     * @var boolean
     */
    public $value = true;

    public function getValue() {
        return $this->value;
    }
}