<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Elasticsearch Config
    |--------------------------------------------------------------------------
    |
    */

    'hosts'       => explode(',', env('ELASTICSEARCH_HOSTS', 'localhost:9200')),
    'basicAuthPw' => env('ELASTICSEARCH_PW'),
    'caCertPath'  => env('ELASTICSEARCH_CA_CERT_PATH'),
];
