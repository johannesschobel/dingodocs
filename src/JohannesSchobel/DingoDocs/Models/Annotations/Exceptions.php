<?php

namespace JohannesSchobel\DingoDocs\Models\Annotations;

/**
 * Class Exceptions
 * @package JohannesSchobel\DingoDocs\Models\Annotations
 * @Annotation
 */
class Exceptions
{
    /**
     * A List of all Exceptions
     *
     * @var array<\JohannesSchobel\DingoDocs\Models\Annotations\Exception>
     */
    public $value = [];

    public function getExceptions() {
        return $this->value;
    }
}