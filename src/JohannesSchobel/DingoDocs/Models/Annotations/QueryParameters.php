<?php

namespace JohannesSchobel\DingoDocs\Models\Annotations;

/**
 * Class QueryParameters
 * @package JohannesSchobel\DingoDocs\Models\Annotations
 * @Annotation
 */
class QueryParameters
{
    /**
     * A List of all Query Parameters
     *
     * @var array<\JohannesSchobel\DingoDocs\Models\Annotations\QueryParameter>
     */
    public $value = [];

    public function getParameters() {
        return $this->value;
    }
}