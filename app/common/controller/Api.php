<?php

namespace addons\cms\app\common\controller;

use addons\cms\model\Diydata;
use addons\cms\model\Modelx;
use think\Config;
use think\Db;
use think\Exception;
use think\exception\PDOException;

/**
 * Api接口控制器
 * Class Api
 * @package addons\cms\controller
 */
class Api extends Base
{

    public function _initialize()
    {
        Config::set('default_return_type', 'json');

        $apikey = $this->request->request('apikey');
        $config = get_addon_config('cms');
        if (!$config['apikey']) {
            $this->error('请先在后台配置API密钥');
        }
        if ($config['apikey'] != $apikey) {
            $this->error('密钥不正确');
        }

        return parent::_initialize();
    }

    /**
     * 文档数据写入接口
     */
    public function index()
    {

        $data = $this->request->request();
        if (isset($data['user']) && $data['user']) {
            $user = \app\common\model\User::where('nickname', $data['user'])->find();
            if ($user) {
                $data['user_id'] = $user->id;
            }
        }
        //如果有传栏目名称
        if (isset($data['channel']) && $data['channel']) {
            $channel = \addons\cms\model\Channel::where('name', $data['channel'])->where('type', 'list')->find();
            if ($channel) {
                $data['channel_id'] = $channel->id;
            } else {
                $this->error('栏目未找到');
            }
        } else {
            $channel_id = $this->request->request('channel_id');
            $channel = \addons\cms\model\Channel::get($channel_id);
            if (!$channel) {
                $this->error('栏目未找到');
            }
        }
        $model = Modelx::get($channel['model_id']);
        if (!$model) {
            $this->error('模型未找到');
        }
        $data['model_id'] = $model['id'];
        $data['content'] = !isset($data['content']) ? '' : $data['content'];

        Db::startTrans();
        try {
            //副表数据插入会在模型事件中完成
            (new \app\admin\model\cms\Archives)->allowField(true)->save($data);
            Db::commit();
        } catch (PDOException $e) {
            Db::rollback();
            $this->error($e->getMessage());
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success('新增成功');
        return;
    }

    /**
     * 获取栏目列表
     */
    public function channel()
    {
        $channelList = \addons\cms\model\Channel::where('status', '<>', 'hidden')
            ->where('type', 'list')
            ->order('weigh DESC,id DESC')
            ->column('id,name');
        $this->success(__('读取成功'), null, $channelList);
    }

    /**
     * 评论数据写入接口
     */
    public function comment()
    {
        try {
            $params = $this->request->post();
            \addons\cms\model\Comment::postComment($params);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success(__('评论成功'));
    }

    /**
     * 自定义表单数据写入接口
     */
    public function diyform()
    {
        $id = $this->request->request("diyform_id/d");
        $diyform = \addons\cms\model\Diyform::get($id);
        if (!$diyform || $diyform['status'] != 'normal') {
            $this->error("自定义表单未找到");
        }

        //是否需要登录判断
        if ($diyform['needlogin'] && !$this->auth->isLogin()) {
            $this->error("请登录后再操作");
        }

        $diydata = new Diydata($diyform->getData("table"));
        if (!$diydata) {
            $this->error("自定义表未找到");
        }

        $data = $this->request->request();
        try {
            $diydata->allowField(true)->save($data);
        } catch (Exception $e) {
            $this->error("数据提交失败");
        }
        $this->success("数据提交成功", $diyform['redirecturl'] ? $diyform['redirecturl'] : addon_url('cms/index/index'));
    }
}
