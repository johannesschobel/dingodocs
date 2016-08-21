<?php

namespace JohannesSchobel\DingoDocs\Models\Annotations;

/**
 * Class Group
 * @package JohannesSchobel\DingoDocs\Models\Annotations
 * @Annotation
 */
class Group
{
    /**
     * The name of the group
     *
     * @var string
     */
    public $value;

    public function getValue() {
        return $this->value;
    }
}