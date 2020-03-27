<?php
/**
 * 菜单配置文件
 */

return [
    [
        'name'    => 'cms',
        'title'   => 'CMS管理',
        'sublist' => [
            [
                'name'    => 'cms/archives',
                'title'   => '内容管理',
                'icon'    => 'fa fa-file-text-o',
                'sublist' => [
                    ['name' => 'cms/archives/index', 'title' => '查看'],
                    ['name' => 'cms/archives/content', 'title' => '副表管理', 'remark' => '用于管理模型副表的数据列表,不建议在此进行删除操作'],
                    ['name' => 'cms/archives/add', 'title' => '添加'],
                    ['name' => 'cms/archives/edit', 'title' => '修改'],
                    ['name' => 'cms/archives/del', 'title' => '删除'],
                    ['name' => 'cms/archives/multi', 'title' => '批量更新'],
                ]
            ],
            [
                'name'    => 'cms/channel',
                'title'   => '栏目管理',
                'icon'    => 'fa fa-list',
                'sublist' => [
                    ['name' => 'cms/channel/index', 'title' => '查看'],
                    ['name' => 'cms/channel/add', 'title' => '添加'],
                    ['name' => 'cms/channel/edit', 'title' => '修改'],
                    ['name' => 'cms/channel/del', 'title' => '删除'],
                    ['name' => 'cms/channel/multi', 'title' => '批量更新'],
                    ['name' => 'cms/channel/admin', 'title' => '栏目授权', 'remark' => '分配管理员可管理的栏目数据，此功能需要先开启插件管理CMS配置的栏目授权开关'],
                ],
                'remark'  => '用于管理网站的分类，可进行无限级分类，注意只有类型为列表的才可以添加文章'
            ],
            [
                'name'    => 'cms/modelx',
                'title'   => '模型管理',
                'icon'    => 'fa fa-th',
                'sublist' => [
                    ['name' => 'cms/modelx/index', 'title' => '查看'],
                    ['name' => 'cms/modelx/add', 'title' => '添加'],
                    ['name' => 'cms/modelx/edit', 'title' => '修改'],
                    ['name' => 'cms/modelx/del', 'title' => '删除'],
                    ['name' => 'cms/modelx/multi', 'title' => '批量更新'],
                    [
                        'name'    => 'cms/fields',
                        'title'   => '字段管理',
                        'icon'    => 'fa fa-fields',
                        'ismenu'  => 0,
                        'sublist' => [
                            ['name' => 'cms/fields/index', 'title' => '查看'],
                            ['name' => 'cms/fields/add', 'title' => '添加'],
                            ['name' => 'cms/fields/edit', 'title' => '修改'],
                            ['name' => 'cms/fields/del', 'title' => '删除'],
                            ['name' => 'cms/fields/duplicate', 'title' => '复制'],
                            ['name' => 'cms/fields/multi', 'title' => '批量更新'],
                        ],
                        'remark'  => '用于管理模型或表单的字段，进行相关的增删改操作，灰色为主表字段无法修改'
                    ]
                ],
                'remark'  => '在线添加修改删除模型，管理模型字段和相关模型数据'
            ],
            [
                'name'    => 'cms/tags',
                'title'   => '标签管理',
                'icon'    => 'fa fa-tags',
                'sublist' => [
                    ['name' => 'cms/tags/index', 'title' => '查看'],
                    ['name' => 'cms/tags/add', 'title' => '添加'],
                    ['name' => 'cms/tags/edit', 'title' => '修改'],
                    ['name' => 'cms/tags/del', 'title' => '删除'],
                    ['name' => 'cms/tags/multi', 'title' => '批量更新'],
                ],
                'remark'  => '用于管理文章关联的标签,标签的添加在添加文章时自动维护,无需手动添加标签'
            ],
            [
                'name'    => 'cms/block',
                'title'   => '区块管理',
                'icon'    => 'fa fa-th-large',
                'sublist' => [
                    ['name' => 'cms/block/index', 'title' => '查看'],
                    ['name' => 'cms/block/add', 'title' => '添加'],
                    ['name' => 'cms/block/edit', 'title' => '修改'],
                    ['name' => 'cms/block/del', 'title' => '删除'],
                    ['name' => 'cms/block/multi', 'title' => '批量更新'],
                ],
                'remark'  => '用于管理站点的自定义区块内容,常用于广告、JS脚本、焦点图、片段代码等'
            ],
            [
                'name'    => 'cms/page',
                'title'   => '单页管理',
                'icon'    => 'fa fa-file',
                'sublist' => [
                    ['name' => 'cms/page/index', 'title' => '查看'],
                    ['name' => 'cms/page/add', 'title' => '添加'],
                    ['name' => 'cms/page/edit', 'title' => '修改'],
                    ['name' => 'cms/page/del', 'title' => '删除'],
                    ['name' => 'cms/page/multi', 'title' => '批量更新'],
                ],
                'remark'  => '用于管理网站的单页面，可任意创建修改删除单页面'
            ],
            [
                'name'    => 'cms/comment',
                'title'   => '评论管理',
                'icon'    => 'fa fa-comment',
                'sublist' => [
                    ['name' => 'cms/comment/index', 'title' => '查看'],
                    ['name' => 'cms/comment/add', 'title' => '添加'],
                    ['name' => 'cms/comment/edit', 'title' => '修改'],
                    ['name' => 'cms/comment/del', 'title' => '删除'],
                    ['name' => 'cms/comment/multi', 'title' => '批量更新'],
                ],
                'remark'  => '用于管理用户在网站上发表的评论，可任意修改或隐藏评论'
            ],
            [
                'name'    => 'cms/diyform',
                'title'   => '自定义表单管理',
                'icon'    => 'fa fa-list',
                'sublist' => [
                    ['name' => 'cms/diyform/index', 'title' => '查看'],
                    ['name' => 'cms/diyform/add', 'title' => '添加'],
                    ['name' => 'cms/diyform/edit', 'title' => '修改'],
                    ['name' => 'cms/diyform/del', 'title' => '删除'],
                    ['name' => 'cms/diyform/multi', 'title' => '批量更新'],
                ],
                'remark'  => '可在线创建自定义表单，管理表单字段和数据列表'
            ],
            [
                'name'    => 'cms/diydata',
                'title'   => '自定义表单数据管理',
                'icon'    => 'fa fa-list',
                'ismenu'  => 0,
                'sublist' => [
                    ['name' => 'cms/diydata/index', 'title' => '查看'],
                    ['name' => 'cms/diydata/add', 'title' => '添加'],
                    ['name' => 'cms/diydata/edit', 'title' => '修改'],
                    ['name' => 'cms/diydata/del', 'title' => '删除'],
                    ['name' => 'cms/diydata/multi', 'title' => '批量更新'],
                    ['name' => 'cms/diydata/import', 'title' => '导入'],
                ],
                'remark'  => '可在线管理自定义表单的数据列表'
            ],
            [
                'name'    => 'cms/order',
                'title'   => '订单管理',
                'icon'    => 'fa fa-cny',
                'ismenu'  => 1,
                'sublist' => [
                    ['name' => 'cms/order/index', 'title' => '查看'],
                    ['name' => 'cms/order/add', 'title' => '添加'],
                    ['name' => 'cms/order/edit', 'title' => '修改'],
                    ['name' => 'cms/order/del', 'title' => '删除'],
                    ['name' => 'cms/order/multi', 'title' => '批量更新'],
                ],
                'remark'  => '可在线管理付费查看所产生的订单'
            ],
            [
                'name'    => 'cms/special',
                'title'   => '专题管理',
                'icon'    => 'fa fa-newspaper-o',
                'ismenu'  => 1,
                'sublist' => [
                    ['name' => 'cms/special/index', 'title' => '查看'],
                    ['name' => 'cms/special/add', 'title' => '添加'],
                    ['name' => 'cms/special/edit', 'title' => '修改'],
                    ['name' => 'cms/special/del', 'title' => '删除'],
                    ['name' => 'cms/special/multi', 'title' => '批量更新'],
                ],
                'remark'  => '可在线管理专题列表'
            ]
        ]
    ]
];