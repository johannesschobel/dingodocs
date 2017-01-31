<?php

namespace JohannesSchobel\DingoDocs\Models\Annotations;

/**
 * Class Exception
 * @package JohannesSchobel\DingoDocs\Models\Annotations
 * @Annotation
 */
class Exception
{
    /**
     * The HTTP status code of the exception
     *
     * @var string
     */
    public $value = 500;

    /**
     * The description of the exception
     *
     * @var string
     */
    public $description = '';
}