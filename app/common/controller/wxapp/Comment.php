<?php

namespace addons\cms\app\common\controller\wxapp;

use addons\cms\library\CommentException;
use think\Config;
use think\Exception;

/**
 * 评论
 */
class Comment extends Base
{
    protected $noNeedLogin = ['index'];

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 评论列表
     */
    public function index()
    {
        $aid = (int)$this->request->request('aid');
        $page = (int)$this->request->request('page');
        Config::set('paginate.page', $page);
        $commentList = \addons\cms\model\Comment::getCommentList(['aid' => $aid]);
        $this->success('', ['commentList' => $commentList->getCollection()]);
    }

    /**
     * 发表评论
     */
    public function post()
    {
        try {
            $params = $this->request->post();
            \addons\cms\model\Comment::postComment($params);
        } catch (CommentException $e) {
            $this->success($e->getMessage(), null, ['token' => $this->request->token()]);
        } catch (Exception $e) {
            $this->error($e->getMessage(), null, ['token' => $this->request->token()]);
        }
        $this->success(__('评论成功'), null, ['token' => $this->request->token()]);
    }
}
