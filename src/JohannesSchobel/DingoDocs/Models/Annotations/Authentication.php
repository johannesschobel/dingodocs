<?php

namespace JohannesSchobel\DingoDocs\Models\Annotations;

/**
 * Class Authentication
 * @package JohannesSchobel\DingoDocs\Models\Annotations
 * @Annotation
 */
class Authentication
{
    /**
     * If the route needs authentication
     *
     * @var boolean
     */
    public $value = true;

    public function getValue() {
        return $this->value;
    }
}