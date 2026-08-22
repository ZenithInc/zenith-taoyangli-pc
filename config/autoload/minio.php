<?php
return array(
    //minio server
    'endpoint'  =>  env('MINIO_ENDPOINT'),
    'version' => env('MINIO_API_VERSION'),
    'region'  => env('MINIO_REGION'),  //China (Beijing)
    'use_path_style_endpoint' => true,
    'credentials' => [
        'key'    => env('MINIO_ACCESS_KEY'),
        'secret' => env('MINIO_SECRET_KEY'),
    ],
    'bucket' => env('MINIO_BUCKET'),
    'prefix'    => env('MINIO_PREFIX'),   //自定义的键名，bucket作为桶的名字，是顶层的文件目录；剩余下级目录的表示，通过Key来实现;会存在桶名下的prefix目录下
    'acl'    => env('MINIO_ACL'),
);
