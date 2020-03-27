<?php

namespace addons\cms\app\common\controller;

use addons\cms\model\Archives;
use addons\cms\model\Channel as ChannelModel;
use addons\cms\model\Modelx;
use think\Config;

/**
 * 栏目控制器
 * Class Channel
 * @package addons\cms\controller
 */
class Channel extends Base
{
    public function index()
    {
        $diyname = $this->request->param('diyname');

        if ($diyname && !is_numeric($diyname)) {
            $channel = ChannelModel::getByDiyname($diyname);
        } else {
            $id = $diyname ? $diyname : $this->request->param('id', '');
            $channel = ChannelModel::get($id);
        }
        if (!$channel) {
            $this->error(__('No specified channel found'));
        }

        $filterlist = [];
        $orderlist = [];

        $filter = $this->request->get('filter/a', []);
        $orderby = $this->request->get('orderby', '');
        $orderway = $this->request->get('orderway', '', 'strtolower');
        $params = ['filter' => '', 'id' => $channel->id, 'diyname' => $channel->diyname];
        if ($filter) {
            $params['filter'] = $filter;
        }
        if ($orderby) {
            $params['orderby'] = $orderby;
        }
        if ($orderway) {
            $params['orderway'] = $orderway;
        }
        if ($channel['type'] === 'link') {
            $this->redirect($channel['outlink']);
        }

        $model = Modelx::get($channel['model_id']);
        if (!$model) {
            $this->error(__('No specified model found'));
        }
        $fields = [];
        $multiValueField = [];
        foreach ($model->fields_list as $k => $v) {
            if (!$v['isfilter'] || !in_array($v['type'], ['select', 'selects', 'checkbox', 'radio', 'array']) || !$v['content_list']) {
                continue;
            }
            if (in_array($v['type'], ['selects', 'checkbox'])) {
                $multiValueField[] = $v['name'];
            }
            $fields[] = [
                'name'    => $v['name'],
                'title'   => $v['title'],
                'content' => $v['content_list']
            ];
        }
        $filter = array_intersect_key($filter, array_flip(array_column($fields, 'name')));
        foreach ($fields as $k => $v) {
            $content = [];
            $all = ['' => __('All')] + $v['content'];
            foreach ($all as $m => $n) {
                $active = ($m === '' && !isset($filter[$v['name']])) || (isset($filter[$v['name']]) && $filter[$v['name']] == $m) ? true : false;
                $prepare = $m === '' ? array_diff_key($filter, [$v['name'] => $m]) : array_merge($filter, [$v['name'] => $m]);
                $url = '?' . http_build_query(array_merge(['filter' => $prepare], array_diff_key($params, ['filter' => ''])));
                $content[] = ['value' => $m, 'title' => $n, 'active' => $active, 'url' => $url];
            }

            $filterlist[] = [
                'name'    => $v['name'],
                'title'   => $v['title'],
                'content' => $content,
            ];
        }

        $sortrank = [
            ['name' => 'default', 'field' => 'weigh', 'title' => __('Default')],
            ['name' => 'views', 'field' => 'views', 'title' => __('Views')],
            ['name' => 'id', 'field' => 'id', 'title' => __('Post date')],
        ];

        $orderby = $orderby && in_array($orderby, ['default', 'id', 'views']) ? $orderby : 'default';
        $orderway = $orderway ? $orderway : 'desc';
        foreach ($sortrank as $k => $v) {
            $url = '?' . http_build_query(array_merge($params, ['orderby' => $v['name'], 'orderway' => $v['name'] == $orderby ? ($orderway == 'desc' ? 'asc' : 'desc') : 'desc']));
            $v['active'] = $orderby == $v['name'] ? true : false;
            $v['orderby'] = $orderway;
            $v['url'] = $url;
            $orderlist[] = $v;
        }
        $orderby = $orderby == 'default' ? 'weigh DESC,id DESC' : $orderby;
        $pagelist = Archives::with(['channel'])->alias('a')
            ->where('status', 'normal')
            ->whereNull('deletetime')
            ->where(function ($query) use ($filter, $multiValueField) {
                foreach ($filter as $index => $item) {
                    if (in_array($index, $multiValueField)) {
                        $query->where("FIND_IN_SET(:{$index}, `{$index}`)");
                    } else {
                        $query->where($index, $item);
                    }
                }
            })
            ->bind($multiValueField ? array_intersect_key($filter, array_flip($multiValueField)) : [])
            ->join($model['table'] . ' n', 'a.id=n.id', 'LEFT')
            ->field('a.*')
            ->field('id,content', true, config('database.prefix') . $model['table'], 'n')
            ->where('channel_id', 'in', \addons\cms\model\Channel::getChannelChildrenIds($channel['id']))
            ->where('model_id', $channel->model_id)
            ->order($orderby, $orderway)
            ->paginate($channel['pagesize'], false, ['type' => '\\addons\\cms\\library\\Bootstrap']);

        $fieldsContentList = $model->getFieldsContentList($model->id);
        foreach ($pagelist as $index => $item) {
            Archives::appendTextAttr($fieldsContentList, $item);
        }

        $pagelist->appends($params);
        $this->view->assign("__FILTERLIST__", $filterlist);
        $this->view->assign("__ORDERLIST__", $orderlist);
        $this->view->assign("__PAGELIST__", $pagelist);
        $this->view->assign("__CHANNEL__", $channel);

        Config::set('cms.title', isset($channel['seotitle']) && $channel['seotitle'] ? $channel['seotitle'] : $channel['name']);
        Config::set('cms.keywords', $channel['keywords']);
        Config::set('cms.description', $channel['description']);
        $template = preg_replace('/\.html$/', '', $channel["{$channel['type']}tpl"]);

        if ($this->request->isAjax()) {
            $this->success("", "", $this->view->fetch('common/' . $template . '_ajax'));
        }
        return $this->view->fetch('/' . $template);
    }
}
