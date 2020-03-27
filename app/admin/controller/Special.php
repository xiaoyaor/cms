<?php

namespace addons\cms\app\admin\controller;

use addons\base\app\admin\controller\AppBackend;

/**
 * 专题管理
 *
 * @icon fa fa-newspaper-o
 */
class Special extends AppBackend
{

    /**
     * Special模型对象
     * @var \addons\cms\app\admin\model\Special
     */
    protected $model = null;

    protected $noNeedRight = ['check_element_available'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \addons\cms\app\admin\model\Special;
        $this->assign("flagList", $this->model->getFlagList());
        $this->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 检测元素是否可用
     * @internal
     */
    public function check_element_available()
    {
        $id = $this->request->request('id');
        $name = $this->request->request('name');
        $value = $this->request->request('value');
        $name = substr($name, 4, -1);
        if (!$name) {
            $this->error(__('Parameter %s can not be empty', 'name'));
        }
        if ($id) {
            $this->model->where('id', '<>', $id);
        }
        $exist = $this->model->where($name, $value)->find();
        if ($exist) {
            $this->error(__('The data already exist'));
        } else {
            $this->success();
        }
    }


    public function index()
    {
        parent::index();
    }


}
