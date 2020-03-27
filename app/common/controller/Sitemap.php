<?php

namespace addons\cms\app\common\controller;

use think\Config;

/**
 * Sitemap控制器
 * Class Sitemap
 * @package addons\cms\controller
 */
class Sitemap extends Base
{
    protected $noNeedLogin = ['*'];
    protected $options = [
        'item_key'  => '',
        'root_node' => 'urlset',
        'item_node' => 'url',
        'root_attr' => 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:mobile="http://www.baidu.com/schemas/sitemap-mobile/1/"'
    ];

    public function _initialize()
    {
        parent::_initialize();
        Config::set('default_return_type', 'xml');
    }

    /**
     * Sitemap集合
     */
    public function index()
    {
        $list = [
            ['loc' => addon_url('cms/sitemap/archives', '', false, true),],
            ['loc' => addon_url('cms/sitemap/tags', '', false, true),],
        ];
        $this->options = [
            'item_key'  => '',
            'root_node' => 'sitemapindex',
            'item_node' => 'sitemap',
            'root_attr' => ''
        ];
        return xml($list, 200, [], $this->options);
    }

    /**
     * 文章
     */
    public function archives()
    {
        $questionList = \addons\cms\model\Archives::where('status', 'normal')->cache(3600)->field('id,channel_id,diyname,createtime')->paginate(500000);
        $list = [];
        foreach ($questionList as $index => $item) {
            $list[] = [
                'loc'      => $item->fullurl,
                'priority' => 0.8
            ];
        }
        return xml($list, 200, [], $this->options);
    }

    /**
     * 标签
     */
    public function tags()
    {
        $tagList = \addons\cms\model\Tags::cache(3600)->field('id,name')->paginate(500000);
        $list = [];
        foreach ($tagList as $index => $item) {
            $list[] = [
                'loc'      => $item->fullurl,
                'priority' => 0.6
            ];
        }
        return xml($list, 200, [], $this->options);
    }
}
