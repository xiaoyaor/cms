<?php

namespace addons\cms\app\admin\controller;

use addons\base\app\admin\controller\AppBackend;

/**
 * 评论管理
 *
 * @icon fa fa-comment
 */
class Comment extends AppBackend
{

    /**
     * Comment模型对象
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \addons\cms\app\admin\model\Comment;
        $this->assign("typeList", $this->model->getTypeList());
        $this->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 查看
     */
    public function index()
    {
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->alias('Comment')
                ->with(['archives', 'spage', 'user'])
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->alias('Comment')
                ->with(['archives', 'spage', 'user'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach ($list as $index => $item) {
                $item->user->visible(['id', 'username', 'nickname', 'avatar']);
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        $this->assignconfig("typeList", $this->model->getTypeList());
        return $this->fetch();
    }
}
