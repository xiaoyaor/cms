<?php

namespace addons\cms\app\common\controller;

use addons\cms\model\Special as SpecialModel;
use think\Config;
use think\Exception;

/**
 * 专题控制器
 * Class Special
 * @package addons\cms\controller
 */
class Special extends Base
{
    /**
     * 专题页面
     * @return string
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $diyname = $this->request->param('diyname');
        if ($diyname && !is_numeric($diyname)) {
            $special = SpecialModel::getByDiyname($diyname);
        } else {
            $id = $diyname ? $diyname : $this->request->param('id', '');
            $special = SpecialModel::get($id);
        }
        if (!$special || $special['status'] == 'hidden') {
            $this->error(__('No specified article found'));
        }
        $special->setInc("views", 1);
        $this->view->assign("__SPECIAL__", $special);
        Config::set('cms.title', isset($special['seotitle']) && $special['seotitle'] ? $special['seotitle'] : $special['title']);
        Config::set('cms.keywords', $special['keywords']);
        Config::set('cms.description', $special['description']);
        $special['template'] = $special['template'] ? $special['template'] : 'special.html';
        $template = preg_replace('/\.html$/', '', $special['template']);
        if ($this->request->isAjax()) {
            $this->success("", "", $this->view->fetch('common/special_list'));
        }
        return $this->view->fetch('/' . $template);
    }

}
