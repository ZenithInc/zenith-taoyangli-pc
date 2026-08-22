<?php
return array(
    'sms_driver' => env('SMS_DRIVER'),
    'app_downloadurl' => env('SMS_APP_DOWNLOAD_URL'),
    'alisms' => array(
       'regionId' => env('SMS_ALIYUN_REGION_ID'),
       'endPointName' => env('SMS_ALIYUN_ENDPOINT'),
       'accesskey' => env('SMS_ALIYUN_ACCESS_KEY_ID'),
       'accessSecret' => env('SMS_ALIYUN_ACCESS_KEY_SECRET'),
       'signname' => env('SMS_ALIYUN_SIGN_NAME')
    ),
   'gdusms' => array(
        'operId' => env('SMS_GDU_OPERATOR_ID'),
        'operPass' => env('SMS_GDU_OPERATOR_PASSWORD')
    ),
    'lxtsms' => array(
        'User' => env('SMS_LXT_USER'),
        'Password' => env('SMS_LXT_PASSWORD'),
        'CorpId' => env('SMS_LXT_CORP_ID')
    )
);

