<?php

namespace addons\cms\app\common\controller\wxapp;

use app\common\controller\Api;
use app\common\library\Auth;
use think\Config;
use think\Lang;

class Base extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];
    //设置返回的会员字段
    protected $allowFields = ['id', 'username', 'nickname', 'mobile', 'avatar', 'score', 'level', 'bio', 'balance', 'money'];

    public function _initialize()
    {
        parent::_initialize();

        Config::set('default_return_type', 'json');
        Auth::instance()->setAllowFields($this->allowFields);

        //这里手动载入语言包
        Lang::load(ROOT_PATH . '/addons/cms/lang/zh-cn.php');
        Lang::load(APP_PATH . '/index/lang/zh-cn/user.php');
    }
}
