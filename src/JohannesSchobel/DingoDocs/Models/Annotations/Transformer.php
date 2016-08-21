<?php

namespace JohannesSchobel\DingoDocs\Models\Annotations;
use League\Fractal\TransformerAbstract;

/**
 * Class Transformer
 * @package JohannesSchobel\DingoDocs\Models\Annotations
 * @Annotation
 */
class Transformer
{
    /**
     * The name of the transformer
     *
     * @var string
     */
    public $value;

    public function getValue() {
        return $this->value;
    }

    public function getTransformer() {

        if(isset($this->value)) {
            // now try to find the transformer class
            $transformerClass = $this->value;
            if (is_subclass_of($transformerClass, TransformerAbstract::class)) {
                $transformer = new $transformerClass();
                return $transformer;
            }
            else {
                // could not instantiate the transformer
                return null;
            }
        }

        return null;
    }
}