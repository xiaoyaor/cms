<?php

namespace addons\cms\app\admin\controller;

use addons\base\app\admin\controller\AppBackend;
use think\Exception;

/**
 * 内容模型表
 *
 * @icon fa fa-th
 */
class Modelx extends AppBackend
{

    /**
     * Model模型对象
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \addons\cms\app\admin\model\Modelx;
    }

    /**
     * 复制模型
     */
    public function duplicate($ids = "")
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $table = $this->request->request("table");
            try {
                $data = [
                    'name'       => $row->getData('name') . '_copy',
                    'table'      => $table,
                    'fields'     => $row->fields,
                    'channeltpl' => $row->channeltpl,
                    'listtpl'    => $row->listtpl,
                    'showtpl'    => $row->showtpl,
                    'setting'    => $row->setting,
                ];
                $modelx = \addons\cms\app\admin\model\Modelx::create($data, true);
                $fieldsList = \addons\cms\app\admin\model\Fields::where('model_id', $row['id'])->select();
                foreach ($fieldsList as $index => $item) {
                    $data = $item->toArray();
                    $data['model_id'] = $modelx->id;
                    unset($data['id'], $data['createtime'], $data['updatetime']);
                    \addons\cms\app\admin\model\Fields::create($data, true);
                }
            } catch (Exception $e) {
                $this->error("复制失败，错误：" . $e->getMessage());
            }
            $this->success();
        }
        $this->assign("row", $row);
        return $this->fetch();
    }
}
