<?php

namespace addons\cms\app\common\controller\wxapp;

use addons\cms\model\Comment;
use addons\cms\model\Page;

/**
 * 我的
 */
class My extends Base
{
    protected $noNeedLogin = ['aboutus'];

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 我发表的评论
     */
    public function comment()
    {
        $page = (int)$this->request->request('page');
        $commentList = Comment::
        with('archives')
            ->where(['user_id' => $this->auth->id])
            ->order('id desc')
            ->page($page, 10)
            ->select();
        foreach ($commentList as $index => $item) {
            $item->create_date = human_date($item->createtime);
        }

        $this->success('', ['commentList' => $commentList]);
    }

    /**
     * 关于我们
     */
    public function aboutus()
    {
        $pageInfo = Page::getByDiyname('aboutus');
        if (!$pageInfo || $pageInfo['status'] != 'normal') {
            $this->error(__('单页未找到'));
        }
        $pageInfo = $pageInfo->toArray();
        unset($pageInfo['status'], $pageInfo['showtpl']);
        $this->success('', ['pageInfo' => $pageInfo]);
    }
}
