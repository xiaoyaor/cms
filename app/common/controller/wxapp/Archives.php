<?php

namespace addons\cms\app\common\controller\wxapp;

use addons\cms\library\OrderException;
use addons\cms\model\Archives as ArchivesModel;
use addons\cms\model\Channel;
use addons\cms\model\Comment;
use addons\cms\model\Modelx;
use addons\third\model\Third;
use think\Exception;
use think\exception\HttpResponseException;
use Yansongda\Pay\Exceptions\GatewayException;

/**
 * 文档
 */
class Archives extends Base
{
    protected $noNeedLogin = ['index', 'detail'];

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 读取文档列表
     */
    public function index()
    {
        $params = [];
        $model = (int)$this->request->request('model');
        $channel = (int)$this->request->request('channel');
        $page = (int)$this->request->request('page');

        if ($model) {
            $params['model'] = $model;
        }
        if ($channel) {
            $params['channel'] = $channel;
        }
        $page = max(1, $page);
        $params['limit'] = ($page - 1) * 10 . ',10';

        $list = ArchivesModel::getArchivesList($params);
        $list = collection($list)->toArray();
        foreach ($list as $index => &$item) {
            $item['url'] = $item['fullurl'];
            unset($item['imglink'], $item['textlink'], $item['channellink'], $item['tagslist'], $item['weigh'], $item['status'], $item['deletetime'], $item['memo'], $item['img']);
        }
        $this->success('', ['archivesList' => $list]);
    }

    public function detail()
    {
        $action = $this->request->post("action");
        if ($action && $this->request->isPost()) {
            return $this->$action();
        }
        $diyname = $this->request->param('diyname');
        if ($diyname && !is_numeric($diyname)) {
            $archives = ArchivesModel::getByDiyname($diyname);
        } else {
            $id = $diyname ? $diyname : $this->request->request('id', '');
            $archives = ArchivesModel::get($id);
        }
        if (!$archives || ($archives['user_id'] != $this->auth->id && $archives['status'] != 'normal') || $archives['deletetime']) {
            $this->error(__('No specified article found'));
        }
        $channel = Channel::get($archives['channel_id']);
        if (!$channel) {
            $this->error(__('No specified channel found'));
        }
        $model = Modelx::get($channel['model_id']);
        if (!$model) {
            $this->error(__('No specified model found'));
        }
        $archives->setInc("views", 1);
        $addon = db($model['table'])->where('id', $archives['id'])->find();
        if ($addon) {
            if ($model->fields) {
                $fieldsContentList = $model->getFieldsContentList($model->id);
                //附加列表字段
                array_walk($fieldsContentList, function ($content, $field) use (&$addon) {
                    $addon[$field . '_text'] = isset($content[$addon[$field]]) ? $content[$addon[$field]] : $addon[$field];
                });
            }
            $archives->setData($addon);
        } else {
            $this->error(__('No specified article addon found'));
        }
        //小程序付费阅读将不可见
        $content = $archives->content;
        if ((isset($archives->price) && $archives->price <= 0) || $archives->is_paid_part_of_content || $archives->ispaid) {
            $value = $archives->getData('content');
            $value = str_replace(['##paidbegin##', '##paidend##'], ['<paid>', '</paid>'], $value);
            $value = str_replace(['$$paidbegin$$', '$$paidend$$'], ['<paid>', '</paid>'], $value);
            $pattern = '/<paid>(.*?)<\/paid>/is';
            if (preg_match($pattern, $value) && !$archives->ispaid) {
                $value = preg_replace($pattern, "<div class='alert alert-warning' style='background:#fcf8e3;border:1px solid #faf3cd;color:#c09853;padding:8px;'>付费内容已经隐藏，请付费后查看</div>", $value);
            }
            $content = $value;
        } else {
            if (isset($archives->price) && $archives->price > 0 && !$archives->ispaid) {
                $content = "<div class='alert alert-warning alert-paid' style='background:#fcf8e3;border:1px solid #faf3cd;color:#c09853;padding:8px;'>此文章为付费文章，需要支付￥{$archives->price}元才能查看</div>";
            }
        }
        $archives = array_merge($archives->toArray(), $addon);
        $archives['content'] = $content;

        $commentList = Comment::getCommentList(['aid' => $archives['id']]);
        $commentList = $commentList->getCollection();
        foreach ($commentList as $index => &$item) {
            if ($item['user']) {
                $item['user']['avatar'] = cdnurl($item['user']['avatar'], true);
            }
        }
        $this->request->token();
        $channel = $channel->toArray();
        $channel['url'] = $channel['fullurl'];
        unset($channel['channeltpl'], $channel['listtpl'], $channel['showtpl'], $channel['status'], $channel['weigh'], $channel['parent_id']);
        $this->success('', ['archivesInfo' => $archives, 'channelInfo' => $channel, 'commentList' => $commentList]);
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
        $archives = ArchivesModel::get($id);
        if (!$archives || ($archives['user_id'] != $this->auth->id && $archives['status'] != 'normal') || $archives['deletetime']) {
            $this->error(__('No specified article found'));
        }
        $archives->where('id', $id)->setInc($type === 'like' ? 'likes' : 'dislikes', 1);
        $archives = ArchivesModel::get($id);
        $this->success(__('Operation completed'), ['likes' => $archives->likes, 'dislikes' => $archives->dislikes, 'likeratio' => $archives->likeratio]);
    }

    public function order()
    {
        $id = $this->request->post('id/d');
        $third = Third::where('platform', 'wxapp')->where('user_id', $this->auth->id)->find();
        if (!$third) {
            $this->error("未找到登录用户信息");
        }
        $openid = $third['openid'];
        $archives = \addons\cms\model\Archives::get($id);
        if (!$archives) {
            $this->error("文档未找到");
        }
        //优先使用余额的方式发起支付
        try {
            \addons\cms\model\Order::submitOrder($id, 'balance');
        } catch (OrderException $e) {
            if ($e->getCode() == 1) {
                $this->success($e->getMessage(), null);
            } else {
                //
            }
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        //以微信小程序应用内支付的方式发起支付
        try {
            $content = \addons\cms\model\Order::submitOrder($id, 'wechat', $openid, 'miniapp');
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success("请求成功", (array)json_decode($content, true));

        return;
    }
}
