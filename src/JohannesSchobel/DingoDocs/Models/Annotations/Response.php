<?php

namespace JohannesSchobel\DingoDocs\Models\Annotations;

/**
 * Class Response
 * @package JohannesSchobel\DingoDocs\Models\Annotations
 * @Annotation
 */
class Response
{
    /**
     * The name of the response
     *
     * @var string
     */
    public $value;

    /**
     * The http status code of the response
     *
     * @var int
     */
    public $status = 200;

    /**
     * The content type of the response
     *
     * @var string
     */
    public $contentType = 'application/json';

    /**
     * The body of the response
     *
     * @var mixed
     */
    public $body = '';

    /**
     * the filepath where the response is stored
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
     * Returns the content of the response
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
            $filepath = 'dingodocs' . DIRECTORY_SEPARATOR . config('dingodocs.examples.response') . DIRECTORY_SEPARATOR . $this->file;
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