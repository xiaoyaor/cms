<?php

namespace addons\cms\app\common\controller;

use addons\cms\model\Archives;
use addons\cms\model\Tags as TagsModel;

/**
 * 标签控制器
 * Class Tags
 * @package addons\cms\controller
 */
class Tags extends Base
{
    public function index()
    {
        $tags = null;
        $name = $this->request->param('name');
        if ($name) {
            $tags = TagsModel::getByName($name);
        }
        if (!$tags) {
            $this->error(__('No specified tags found'));
        }

        $filterlist = [];
        $orderlist = [];

        $orderby = $this->request->get('orderby', '');
        $orderway = $this->request->get('orderway', '', 'strtolower');
        $params = [];
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
        $orderby = $orderby == 'default' ? 'weigh DESC,id DESC' : $orderby;
        $pagelist = Archives::with(['channel'])
            ->where('status', 'normal')
            ->where('id', 'in', explode(',', $tags['archives']))
            ->order($orderby, $orderway)
            ->paginate(10, false, ['type' => '\\addons\\cms\\library\\Bootstrap']);
        $pagelist->appends($params);
        $this->view->assign("__FILTERLIST__", $filterlist);
        $this->view->assign("__ORDERLIST__", $orderlist);
        $this->view->assign("__TAGS__", $tags);
        $this->view->assign("__PAGELIST__", $pagelist);
        \think\Config::set('cms.title', $tags['name']);
        return $this->view->fetch('/tags');
    }
}
