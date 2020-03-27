<?php

namespace addons\cms\app\common\controller;

use addons\cms\model\Page as PageModel;
use think\Config;

/**
 * CMS单页控制器
 * Class Page
 * @package addons\cms\controller
 */
class Page extends Base
{
    public function index()
    {
        $diyname = $this->request->param('diyname');
        if ($diyname && !is_numeric($diyname)) {
            $page = PageModel::getByDiyname($diyname);
        } else {
            $id = $diyname ? $diyname : $this->request->param('id', '');
            $page = PageModel::get($id);
        }
        if (!$page || $page['status'] != 'normal') {
            $this->error(__('No specified page found'));
        }
        $page->setInc('views');
        $this->view->assign("__PAGE__", $page);
        Config::set('cms.title', isset($page['seotitle']) && $page['seotitle'] ? $page['seotitle'] : $page['title']);
        Config::set('cms.keywords', $page['keywords']);
        Config::set('cms.description', $page['description']);
        $template = preg_replace("/\.html$/i", "", $page['showtpl'] ? $page['showtpl'] : 'page');
        return $this->view->fetch('/' . $template);
    }

    /**
     * 赞与踩
     */
    public function vote()
    {
        $id = (int)$this->request->post("id");
        $type = trim($this->request->post("type", ""));
        if (!$id || !$type) {
            $this->error(__('Operation failed'));
        }
        $page = \addons\cms\model\Page::get($id);
        if (!$page) {
            $this->error(__('No specified page found'));
        }
        $page->where('id', $id)->setInc($type === 'like' ? 'likes' : 'dislikes', 1);
        $page = \addons\cms\model\Page::get($id);
        $this->success(__('Operation completed'), null, ['likes' => $page->likes, 'dislikes' => $page->dislikes, 'likeratio' => $page->likeratio]);
    }
}
