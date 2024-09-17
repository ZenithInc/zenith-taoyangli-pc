<?php
return [
    'invoice' => [
        'host' => 'http://117.40.195.43:29763',
    ],
    'passowner' => [
        'url' => 'https://api.taoyangli.cn',
//        'applicationId' => 'b824188dea894a38b4c97516639cbfe1',
//        'appKey' => 'de9d6f1fa10826f2ca502633693f9fb4',
//        'appSecret' => '87b04de91d36c1e10b351b6a61580953',
        'applicationId' => '12620936824811ee8c0984a93e086e68',//正式
        'appKey' => '67ba1719727fbb6eaffe7f4fbae2683d',
        'appSecret' => 'fd0bf74d71df227aac9833ab614a807a',
        'username' => '15267133680',
        'ticket_send' => [
            'formheaduuid' => '02ea5b76c00343fba24857af9d523dcd', //表名
            'fields' => ['status'=>'3drc7kkwfefuts7u','remark'=>'gscy30xexqftjokq','verification_code'=>'fes3ppiyuoo61pza'],
            '02ea5b76c00343fba24857af9d523dcd' => [
                'formheaduuid' => '02ea5b76c00343fba24857af9d523dcd', //表名
                'fields' => ['status'=>'3drc7kkwfefuts7u','remark'=>'gscy30xexqftjokq','verification_code'=>'fes3ppiyuoo61pza'],
            ],
            'ccf9fca6e91111ee87d10242ac130002' => [
                'formheaduuid' => 'ccf9fca6e91111ee87d10242ac130002', //表名
                'fields' => ['status'=>'3drc7kkwfefuts7u','remark'=>'gscy30xexqftjokq','verification_code'=>'fes3ppiyuoo61pza'],
            ],
        ],
        'GroupAppointment' => [
            'formheaduuid' => 'd1bc2189f25e428694daa3ed8d725da2', //表名
            'fields' => ['openid'=>'3tviprykoyvkzkkd','user_id'=>'xtmp1jdj76xt7yxc'],
        ],
        'Lease' => [//租赁
            'formheaduuid' => 'bf1e13e70b34428f8e83a2f250b392f5', //表名
            'fields' => ['openid'=>'leqs1y7ot5to74ds','user_id'=>''],
        ],
        'Vagrancy' => [//景漂
            'formheaduuid' => 'd6e46e35f22b42d6a7f6c2fd3bc66f47', //表名
            'fields' => ['openid'=>'tpht6rbtwhklgsxm','user_id'=>''],
        ],
        'citizen' => [ //市民卡
            'formheaduuid' => 'a49c6768f9014f678ae859829703757d', //表名
            'fields' => ['name'=>'eql6ftae4hwqvsqx','idcard'=>'ahe8tyfgccllyzep','check_status'=>'21xvwuq7j7g4gg4k','openid'=>'m4z9zueuv0ybvp9a'],
        ],
        'Complaint' => [//投诉建议
            'formheaduuid' => 'a15b99ad27704cb59e523f8dccf2bf03', //表名
            'fields' => ['openid'=>'csa52wtzdacuvnbw','user_id'=>''],
        ]
    ],

    'common'=>[//Low code
        'market_user'=>[//市集_个人信息
            'formheaduuid' => '1ceda9c934744cdfb5a29e2e2ba2a530', //表名
            'needpage'=>true,//是否需要分页
            'list_filters'=>[//列表筛选条件

                   'openid'=>[
                       'name'=>'用户opeid',
                       'key'=>'v1vdsrr4880g5eot',//低代码的key
                       'required'=>1,//是否必须
                   ]

            ]
        ],
        'market_goods'=>[//市集_产品信息
            'formheaduuid' => '20defd3b271b47e099ee29a7aff5d7e4', //表名
            'needpage'=>true,//是否需要分页
            'list_filters'=>[//列表筛选条件

                    'openid'=>[
                        'name'=>'用户opeid',
                        'key'=>'sxze6wgeq5qrxazu',//低代码的key
                        'required'=>1,//是否必须
                    ]

            ]
        ],
        'market_active'=>[//市集_活动
            'formheaduuid' => '0c39855eb8324ab5bd8b4243051e9300', //表名
            'needpage'=>true,//是否需要分页
            'list_filters'=>[//列表筛选条件

                    'openid'=>[
                        'name'=>'用户opeid',
                        'key'=>'sxze6wgeq5qrxazu',//低代码的key
                        'required'=>1,//是否必须
                    ],
                'recruit_status'=>[
                    'name'=>'招募状态',
                    'key'=>'iqsk48tyqwb6grpz',//低代码的key
                    'required'=>0,//是否必须
                ],

            ]
        ],
        'market_section'=>[//市集_片区管理
            'formheaduuid' => 'b5b9dba6b12049fea723ec31c1ea5b01', //表名
            'needpage'=>true,//是否需要分页
            'list_filters'=>[//列表筛选条件

//                    'openid'=>[
//                        'name'=>'用户opeid',
//                        'key'=>'sxze6wgeq5qrxazu',//低代码的key
//                        'required'=>1,//是否必须
//                    ]

            ]
        ],
        'market_apply'=>[//市集_报名情况
            'formheaduuid' => '06d797f4b275414eaf7359413f893e86', //表名
            'needpage'=>true,//是否需要分页
            'list_filters'=>[//列表筛选条件
                    'openid'=>[
                        'name'=>'用户opeid',
                        'key'=>'k2lmeuvbewad1e2d',//低代码的key
                        'required'=>1,//是否必须
                    ]

            ]
        ],
    ],
];