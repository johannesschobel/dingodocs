<?php

namespace JohannesSchobel\DingoDocs\Models\Annotations;
use Illuminate\Support\Facades\Storage;

/**
 * Class Request
 * @package JohannesSchobel\DingoDocs\Models\Annotations
 * @Annotation
 */
class Request
{
    /**
     * The name of the request
     *
     * @var string
     */
    public $value;

    /**
     * The content type of the request
     *
     * @var string
     */
    public $contentType = 'application/json';

    /**
     * The body of the request
     *
     * @var mixed
     */
    public $body = null;

    /**
     * the filepath where the request is stored
     *
     * @var string
     */
    public $file = null;

    /**
     * custom headers for the request
     *
     * @var array
     */
    public $headers = [];

    /**
     * Returns the content of the request
     *
     * Returns the body (if set), the content of the file (if file is present) or an empty string
     *
     * @return string
     */
    public function getContent()
    {
        $content = '';
        if($this->body !== null) {
            $content = $this->body;
            if(is_array($content)) {
                $content = json_encode($content);
            }
        }

        if($this->file !== null) {
            $filepath = 'dingodocs' . DIRECTORY_SEPARATOR . config('dingodocs.examples.request') . DIRECTORY_SEPARATOR . $this->file;
            if(Storage::disk(config('dingodocs.storage_disk'))->has($filepath)) {
                $content = Storage::disk(config('dingodocs.storage_disk'))->get($filepath);
            }
            else {
                $content = 'Unable to find the requested file ' . $filepath;
            }
        }

        if(isValidJSON($content)) {
            $content = dingodocs_formatJSON($content);
        }

        return $content;
    }
}