<?php
return array(
    //minio server
    'endpoint'  =>  'http://47.111.13.130:9003',
    'version' => 'latest',
    'region'  => 'cn-north-1',  //China (Beijing)
    'use_path_style_endpoint' => true,
    'credentials' => [
        'key'    => 'AKIADTSFODNN7EXAMTRYL',
        'secret' => 'wKalrXUtnTEMI/P7MDENT/bPxRfiCYEXAMMDEKGA',
    ],
    'bucket' => 'tyl',
    'prefix'    => 'sample_apk/',   //自定义的键名，bucket作为桶的名字，是顶层的文件目录；剩余下级目录的表示，通过Key来实现;会存在桶名下的prefix目录下
    'acl'    => 'public-read',
);