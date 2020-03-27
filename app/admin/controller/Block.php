<?php

namespace addons\cms\app\admin\controller;

use addons\base\app\admin\controller\AppBackend;

/**
 * 区块表
 *
 * @icon fa fa-th-large
 */
class Block extends AppBackend
{

    /**
     * Block模型对象
     */
    protected $model = null;
    protected $noNeedRight = ['selectpage_type'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \addons\cms\app\admin\model\Block;
        $this->assign("statusList", $this->model->getStatusList());
    }

    public function index()
    {
        $typeArr = \addons\cms\app\admin\model\Block::distinct('type')->column('type');
        $this->assign('typeList', $typeArr);
        $this->assignconfig('typeList', $typeArr);
        return parent::index();
    }

    public function selectpage_type()
    {
        $list = [];
        $word = (array)$this->request->request("q_word/a");
        $field = $this->request->request('showField');
        $keyValue = $this->request->request('keyValue');
        if (!$keyValue) {
            if (array_filter($word)) {
                foreach ($word as $k => $v) {
                    $list[] = ['id' => $v, $field => $v];
                }
            }
            $typeArr = \addons\cms\app\admin\model\Block::column('type');
            $typeArr = array_unique($typeArr);
            foreach ($typeArr as $index => $item) {
                $list[] = ['id' => $item, $field => $item];
            }
        } else {
            $list[] = ['id' => $keyValue, $field => $keyValue];
        }
        return json(['total' => count($list), 'list' => $list]);
    }

    public function import()
    {
        return parent::import();
    }
}
