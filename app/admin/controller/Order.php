<?php

namespace addons\cms\app\admin\controller;

use addons\base\app\admin\controller\AppBackend;

/**
 * 订单管理
 *
 * @icon fa fa-cny
 */
class Order extends AppBackend
{

    /**
     * Order模型对象
     * @var \addons\cms\app\admin\model\Order
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \addons\cms\app\admin\model\Order;
        $this->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            $this->relationSearch = true;
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->alias('Order')
                ->with(['user', 'archives'])
                ->where($where)
                ->order($sort, $order)
                ->count();
            $list = $this->model
                ->alias('Order')
                ->with(['user', 'archives'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $item) {
                $item->user->visible(['id', 'username', 'nickname', 'avatar']);
                $item->archives->visible(['id', 'title', 'diyname']);
            }
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }

        return $this->fetch();
    }
}
