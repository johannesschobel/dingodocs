<?php

namespace JohannesSchobel\DingoDocs\Models\Annotations;

/**
 * Class QueryParameter
 * @package JohannesSchobel\DingoDocs\Models\Annotations
 * @Annotation
 */
class QueryParameter
{
    /**
     * The name of the parameter
     *
     * @var string
     */
    public $value;

    /**
     * The type of the parameter
     *
     * @var string
     */
    public $type = 'string';

    /**
     * Indicates if the parameter is required
     *
     * @var boolean
     */
    public $required = false;

    /**
     * The description of the parameter
     *
     * @var string
     */
    public $description = '';

    /**
     * The default value of the parameter
     *
     * @var mixed
     */
    public $default = '';
}