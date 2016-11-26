<?php

return [
    /*
     * The versions for which the docs shall be generated
     */
    'versions' => ['v1'],

    /*
     * debug messages? [I] are shown
     */
    'debug' => false,

    /*
     * The base folder for the output files. WITHOUT trailing slash!
     * Each version (see above) gets its own file.
     */
    'outputpath' => './public/dingodocs/',

    /*
     * Title of the documentation
     */
    'title' => 'API Documentation',

    /*
     * the rows for the textfields for request and response
     */
    'size' => [
        'request'   => 10,
        'response'  => 10
    ],

    /*
     * The path, where the example requests and responses are stored.
     */
    'examples'  => [
        'request'   => 'request',
        'response'  => 'response'
    ],

    /*
     * The name of the preferred storage disk
     */
    'storage_disk' => 'local',

    /*
     * the default values if no annotation is present
     */
    'defaults' => [
        'group'                 => 'Uncategorized',
        'authentication'        => false,
        'transient'             => false,
        'transformer'           => null,
        'validator'             => null,
        'queryparameters'       => [],
        'validatorparameters'   => [],
        'request'               => null,
        'response'              => null,
    ]


];