<?php

namespace addons\cms\app\common\controller\wxapp;

use addons\third\library\Service;
use addons\third\model\Third;
use app\common\library\Auth;
use fast\Http;
use think\Config;
use think\Validate;

/**
 * 会员
 */
class User extends Base
{
    protected $noNeedLogin = ['index', 'login'];

    protected $token = '';

    public function _initialize()
    {
        $this->token = $this->request->post('token');
        if ($this->request->action() == 'login' && $this->token) {
            $this->request->post(['token' => '']);
        }
        parent::_initialize();

        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }

        $ucenter = get_addon_info('ucenter');
        if ($ucenter && $ucenter['state']) {
            include ADDON_PATH . 'ucenter' . DS . 'uc.php';
        }
    }

    /**
     * 登录
     */
    public function login()
    {
        $config = get_addon_config('cms');
        $code = $this->request->post("code");
        $rawData = $this->request->post("rawData", '', 'trim');
        if (!$code || !$rawData) {
            $this->error("参数不正确");
        }
        $userInfo = (array)json_decode($rawData, true);

        $params = [
            'appid'      => $config['wxappid'],
            'secret'     => $config['wxappsecret'],
            'js_code'    => $code,
            'grant_type' => 'authorization_code'
        ];
        $result = Http::sendRequest("https://api.weixin.qq.com/sns/jscode2session", $params, 'GET');
        if ($result['ret']) {
            $json = (array)json_decode($result['msg'], true);
            if (isset($json['openid'])) {
                //如果有传Token
                if ($this->token) {
                    $this->auth->init($this->token);
                    //检测是否登录
                    if ($this->auth->isLogin()) {
                        $third = Third::where(['openid' => $json['openid'], 'platform' => 'wxapp'])->find();
                        if ($third && $third['user_id'] == $this->auth->id) {
                            $this->success("登录成功", ['userInfo' => $this->auth->getUserinfo()]);
                        }
                    }
                }

                $platform = 'wxapp';
                $result = [
                    'openid'        => $json['openid'],
                    'userinfo'      => [
                        'nickname' => $userInfo['nickName'],
                    ],
                    'access_token'  => $json['session_key'],
                    'refresh_token' => '',
                    'expires_in'    => isset($json['expires_in']) ? $json['expires_in'] : 0,
                ];
                $extend = ['gender' => $userInfo['gender'], 'nickname' => $userInfo['nickName'], 'avatar' => $userInfo['avatarUrl']];
                $ret = Service::connect($platform, $result, $extend);
                if ($ret) {
                    $auth = Auth::instance();
                    $this->success("登录成功", ['userInfo' => $auth->getUserinfo()]);
                } else {
                    $this->error("连接失败");
                }
            } else {
                $this->error("登录失败");
            }
        }

        return;
    }

    /**
     * 绑定账号
     */
    public function bind()
    {
        $account = $this->request->post("account");
        $password = $this->request->post("password");
        if (!$account || !$password) {
            $this->error("参数不正确");
        }

        $account = $this->request->post('account');
        $password = $this->request->post('password');
        $rule = [
            'account'  => 'require|length:3,50',
            'password' => 'require|length:6,30',
        ];

        $msg = [
            'account.require'  => 'Account can not be empty',
            'account.length'   => 'Account must be 3 to 50 characters',
            'password.require' => 'Password can not be empty',
            'password.length'  => 'Password must be 6 to 30 characters',
        ];
        $data = [
            'account'  => $account,
            'password' => $password,
        ];
        $validate = new Validate($rule, $msg);
        $result = $validate->check($data);
        if (!$result) {
            $this->error(__($validate->getError()));
            return false;
        }
        $field = Validate::is($account, 'email') ? 'email' : (Validate::regex($account, '/^1\d{10}$/') ? 'mobile' : 'username');
        $user = \app\common\model\User::get([$field => $account]);
        if (!$user) {
            $this->error('账号未找到');
        }
        $third = Third::where(['user_id' => $user->id, 'platform' => 'wxapp'])->find();
        if ($third) {
            $this->error('账号已经绑定其他小程序账号');
        }

        $third = Third::where(['user_id' => $this->auth->id, 'platform' => 'wxapp'])->find();
        if (!$third) {
            $this->error('未找到登录信息');
        }

        if ($this->auth->login($account, $password)) {
            $third->user_id = $this->auth->id;
            $third->save();
            $this->success("绑定成功", ['userInfo' => $this->auth->getUserinfo()]);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 个人资料
     */
    public function profile()
    {
        $user = $this->auth->getUser();
        $username = $this->request->request('username');
        $nickname = $this->request->request('nickname');
        $bio = $this->request->request('bio');
        $avatar = $this->request->request('avatar');
        if (!$username || !$nickname) {
            $this->error("用户名和昵称不能为空");
        }
        $exists = \app\common\model\User::where('username', $username)->where('id', '<>', $this->auth->id)->find();
        if ($exists) {
            $this->error(__('Username already exists'));
        }
        $avatar = str_replace(Config::get('upload.cdnurl'), '', $avatar);
        $user->username = $username;
        $user->nickname = $nickname;
        $user->bio = $bio;
        $user->avatar = $avatar;
        $user->save();
        $this->success('', ['userInfo' => $this->auth->getUserinfo()]);
    }

    /**
     * 保存头像
     */
    public function avatar()
    {
        $user = $this->auth->getUser();
        $avatar = $this->request->request('avatar');
        if (!$avatar) {
            $this->error("头像不能为空");
        }
        $avatar = str_replace(Config::get('upload.cdnurl'), '', $avatar);
        $user->avatar = $avatar;
        $user->save();
        $this->success('', ['userInfo' => $this->auth->getUserInfo()]);
    }
}
