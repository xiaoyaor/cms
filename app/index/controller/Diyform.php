<?php

namespace addons\cms\app\index\controller;

use addons\base\controller\AppFrontend;
use app\common\controller\Frontend;
use addons\cms\app\common\model\Diyform as DiyformModel;

/**
 * 会员中心
 */
class Diyform extends AppFrontend
{
    protected $layout = 'default';
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 表单
     */
    public function index()
    {
        $diyname = $this->request->param('diyname');
        if ($diyname && !is_numeric($diyname)) {
            $diyform = DiyformModel::getByDiyname($diyname);
        } else {
            $id = $diyname ? $diyname : $this->request->get('id', '');
            $diyform = DiyformModel::get($id);
        }
        if (!$diyform || $diyform['status'] == 'hidden') {
            $this->error(__('表单未找到'));
        }
        $fields = DiyformModel::getDiyformFields($diyform['id']);
        $this->assign('diyform', $diyform);
        $this->assign('fields', $fields);

        return $this->fetch();
    }
}
