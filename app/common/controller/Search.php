<?php

namespace addons\cms\app\common\controller;

use addons\cms\library\FulltextSearch;
use addons\cms\model\Archives;

/**
 * 搜索控制器
 * Class Search
 * @package addons\cms\controller
 */
class Search extends Base
{
    public function index()
    {
        $config = get_addon_config('cms');
        if ($config['searchtype'] == 'xunsearch') {
            return $this->xunsearch();
        }
        $search = $this->request->get("search", $this->request->get("q", ""));

        $filterlist = [];
        $orderlist = [];

        $orderby = $this->request->get('orderby', '');
        $orderway = $this->request->get('orderway', '', 'strtolower');
        $params = ['search' => $search];
        if ($orderby) {
            $params['orderby'] = $orderby;
        }
        if ($orderway) {
            $params['orderway'] = $orderway;
        }

        $sortrank = [
            ['name' => 'default', 'field' => 'weigh', 'title' => __('Default')],
            ['name' => 'views', 'field' => 'views', 'title' => __('Views')],
            ['name' => 'id', 'field' => 'id', 'title' => __('Post date')],
        ];

        $orderby = $orderby && in_array($orderby, ['default', 'id', 'views']) ? $orderby : 'default';
        $orderway = $orderway ? $orderway : 'desc';
        foreach ($sortrank as $k => $v) {
            $url = '?' . http_build_query(array_merge($params, ['orderby' => $v['name'], 'orderway' => ($orderway == 'desc' ? 'asc' : 'desc')]));
            $v['active'] = $orderby == $v['name'] ? true : false;
            $v['orderby'] = $orderway;
            $v['url'] = $url;
            $orderlist[] = $v;
        }
        $orderby = $orderby == 'default' ? 'weigh' : $orderby;

        $pagelist = Archives
            ::where('status', 'normal')
            ->whereNull('deletetime')
            ->where('title', 'like', "%{$search}%")
            ->order($orderby, $orderway)
            ->paginate(10, false, ['type' => '\\addons\\cms\\library\\Bootstrap']);
        $pagelist->appends($params);
        $this->view->assign("__FILTERLIST__", $filterlist);
        $this->view->assign("__ORDERLIST__", $orderlist);
        $this->view->assign("__PAGELIST__", $pagelist);
        \think\Config::set('cms.title', __("Search for %s", $search));
        return $this->view->fetch('/search');
    }

    public function typeahead()
    {
        $search = $this->request->post("search", $this->request->post("q", ""));
        $list = Archives
            ::where('status', 'normal')
            ->whereNull('deletetime')
            ->where('title', 'like', "%{$search}%")
            ->order('id', 'desc')
            ->field('id,title,diyname,channel_id,likes,dislikes,tags,createtime')
            ->limit(10)
            ->select();
        $result = collection($list)->toArray();
        $result[] = ['id' => 0, 'title' => __('Search more %s', $search), 'url' => addon_url("cms/search/index", [':search' => $search, 'search' => $search])];
        return json($result);
    }

    /**
     * Xunsearch搜索
     * @return string
     * @throws \think\Exception
     */
    public function xunsearch()
    {
        $orderList = [
            'relevance'       => '默认排序',
            'createtime_desc' => '发布时间从新到旧',
            'createtime_asc'  => '发布时间从旧到新',
            'views_desc'      => '浏览次数从多到少',
            'views_asc'       => '浏览次数从少到多',
            'comments_desc'   => '评论次数从多到少',
            'comments_asc'    => '评论次数从少到多',
        ];

        $q = $this->request->get('q', $this->request->get('search', ''));
        $page = $this->request->get('page/d', '1');
        $order = $this->request->get('order', '');
        $fulltext = $this->request->get('fulltext/d', '1');
        $fuzzy = $this->request->get('fuzzy/d', '0');
        $synonyms = $this->request->get('synonyms/d', '0');

        $total_begin = microtime(true);
        $search = null;
        $pagesize = 10;

        $result = FulltextSearch::search($q, $page, $pagesize, $order, $fulltext, $fuzzy, $synonyms);

        // 计算总耗时
        $total_cost = microtime(true) - $total_begin;

        //获取热门搜索
        $hot = FulltextSearch::hot();

        $data = [
            'q'           => $q,
            'error'       => '',
            'total'       => $result['total'],
            'count'       => $result['count'],
            'search_cost' => $result['microseconds'],
            'docs'        => $result['list'],
            'pager'       => $result['pager'],
            'corrected'   => $result['corrected'],
            'highlight'   => $result['highlight'],
            'related'     => $result['related'],
            'search'      => $search,
            'fulltext'    => $fulltext,
            'synonyms'    => $synonyms,
            'fuzzy'       => $fuzzy,
            'order'       => $order,
            'orderList'   => $orderList,
            'hot'         => $hot,
            'total_cost'  => $total_cost,
        ];

        \think\Config::set('cms.title', __("Search for %s", $q));
        $this->view->assign("title", $q);
        $this->view->assign($data);
        return $this->view->fetch('/xunsearch');
    }

    public function suggestion()
    {
        $q = trim($this->request->get('q', ''));
        $terms = [];
        $config = get_addon_config('cms');
        if ($config['searchtype'] == 'xunsearch') {
            $terms = FulltextSearch::suggestion($q);
        }
        return json($terms);
    }
}
