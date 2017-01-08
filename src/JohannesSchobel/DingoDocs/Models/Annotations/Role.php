<?php

namespace JohannesSchobel\DingoDocs\Models\Annotations;

/**
 * Class Role
 * @package JohannesSchobel\DingoDocs\Models\Annotations
 * @Annotation
 */
class Role
{
    /**
     * The name of the role
     *
     * @var string
     */
    public $value;

    public function getValue() {
        return $this->value;
    }
}