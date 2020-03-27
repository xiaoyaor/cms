<?php

namespace addons\cms\app\common\model;

use addons\cms\library\CommentException;
use addons\cms\library\Service;
use app\common\library\Auth;
use app\common\library\Email;
use app\common\model\User;
use think\Db;
use think\Exception;
use think\Model;
use think\Validate;

/**
 * 评论模型
 */
class Comment extends Model
{
    protected $name = "cms_comment";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'create_date',
    ];

    public function getCreateDateAttr($value, $data)
    {
        return human_date($data['createtime']);
    }

    /**
     * 发表评论
     * @param array $params
     * @return bool
     * @throws CommentException
     * @throws Exception
     */
    public static function postComment($params = [])
    {
        $config = get_addon_config('cms');
        $request = request();
        $useragent = substr($request->server('HTTP_USER_AGENT', ''), 0, 255);
        $ip = $request->ip(0, false);
        $auth = Auth::instance();
        $content = $params['content'];

        if (!$auth->id) {
            throw new Exception("请登录后发表评论");
        }
        if ($auth->score < $config['limitscore']['postcomment']) {
            throw new Exception("积分必须大于{$config['limitscore']['postcomment']}才可以发表评论");
        }
        if (!isset($params['aid']) || !isset($params['content'])) {
            throw new Exception("内容不能为空");
        }

        $params['user_id'] = $auth->id;
        $params['type'] = isset($params['type']) ? $params['type'] : 'archives';
        $params['content'] = nl2br($params['content']);
        $params['content'] = preg_replace("/(@([\s\S]*?))\s+/i", '<em>$1</em> ', $params['content']);

        $archives = $params['type'] == 'archives' ? Archives::get($params['aid']) : ($params['type'] == 'special' ? Special::get($params['aid']) : Page::get($params['aid']));
        if (!$archives || $archives['status'] == 'hidden') {
            throw new Exception("文档未找到");
        }

        $rule = [
            'type'       => 'require|in:archives,page,special',
            'pid'        => 'require|number',
            'user_id'    => 'require|number',
            'content|内容' => 'require|length:3,250',
            '__token__'  => 'require|token',
        ];
        $validate = new Validate($rule);
        $result = $validate->check($params);
        if (!$result) {
            throw new Exception($validate->getError());
        }

        //查找最后评论
        $lastComment = self::where(['type' => $params['type'], 'aid' => $params['aid'], 'ip' => $ip])->order('id', 'desc')->find();
        if ($lastComment && time() - $lastComment['createtime'] < 30) {
            throw new Exception("对不起！您发表评论的速度过快！请稍微休息一下，喝杯咖啡");
        }
        if ($lastComment && $lastComment['content'] == $params['content']) {
            throw new Exception("您可能连续了相同的评论，请不要重复提交");
        }
        //审核状态
        $status = 'normal';
        if ($config['iscommentaudit'] == 1) {
            $status = 'hidden';
        } elseif ($config['iscommentaudit'] == 0) {
            $status = 'normal';
        } elseif ($config['iscommentaudit'] == -1) {
            if (!Service::isContentLegal($content)) {
                $status = 'hidden';
            }
        }
        $params['ip'] = $ip;
        $params['useragent'] = $useragent;
        $params['status'] = $status;

        Db::startTrans();
        try {
            (new static())->allowField(true)->save($params);
            $archives->setInc('comments');

            //增加积分
            $status == 'normal' && User::score($config['score']['postcomment'], $auth->id, '发表评论');
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new Exception("发表评论失败");
        }

        //发送通知
        if ($status === 'hidden') {
            Service::notice('CMS收到一条待审核评论', $config['auditnotice'], $config['noticetemplateid']);
            throw new CommentException("发表评论成功，但评论需要显示审核后才会展示", 1);
        }

        if (isset($params['pid'])) {
            //查找父评论，是否并发邮件通知
            $parentComment = self::get($params['pid'], 'user');
            if ($parentComment && $parentComment['subscribe'] && Validate::is($parentComment->user->email, 'email')) {
                $domain = $request->domain();
                $config = get_addon_config('cms');
                $title = "{$parentComment->user->nickname}，您发表在《{$archives['title']}》上的评论有了新回复 - {$config['sitename']}";
                $archivesurl = $domain . $archives['url'];
                $unsubscribe_url = addon_url("cms/comment/unsubscribe", ['id' => $parentComment['id'], 'key' => md5($parentComment['id'] . $parentComment->user->email)], true, true);
                $content = "亲爱的{$parentComment->user->nickname}：<br />您于" . date("Y-m-d H:i:s") .
                    "在《<a href='{$archivesurl}' target='_blank'>{$archives['title']}</a>》上发表的评论<br /><blockquote>{$parentComment['content']}</blockquote>" .
                    "<br />{$auth->nickname}发表了回复，内容是<br /><br />您可以<a href='{$archivesurl}'>点击查看评论详情</a>。" .
                    "<br /><br />如果你不愿意再接受最新评论的通知，<a href='{$unsubscribe_url}'>请点击这里取消</a>";
                try {
                    $email = new Email;
                    $result = $email
                        ->to($parentComment->user->email)
                        ->subject($title)
                        ->message('<div style="min-height:550px; padding: 100px 55px 200px;">' . $content . '</div>')
                        ->send();
                } catch (\think\Exception $e) {
                }
            }
        }

        return true;
    }

    /**
     * 获取评论列表
     * @param $params
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public static function getCommentList($params)
    {
        $type = empty($params['type']) ? 'archives' : $params['type'];
        $aid = empty($params['aid']) ? 0 : $params['aid'];
        $pid = empty($params['pid']) ? 0 : $params['pid'];
        $condition = empty($params['condition']) ? '' : $params['condition'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $fragment = empty($params['fragment']) ? 'comments' : $params['fragment'];
        $row = empty($params['row']) ? 10 : (int)$params['row'];
        $orderby = empty($params['orderby']) ? 'nums' : $params['orderby'];
        $orderway = empty($params['orderway']) ? 'desc' : strtolower($params['orderway']);
        $pagesize = empty($params['pagesize']) ? $row : $params['pagesize'];
        $cache = !isset($params['cache']) ? false : (int)$params['cache'];
        $orderway = in_array($orderway, ['asc', 'desc']) ? $orderway : 'desc';
        $cache = !$cache ? false : $cache;

        $where = ['status' => 'normal'];
        if ($type) {
            $where['type'] = $type;
        }
        if ($aid) {
            $where['aid'] = $aid;
        }
        if ($pid) {
            $where['pid'] = $pid;
        }
        $order = $orderby == 'rand' ? 'rand()' : (in_array($orderby, ['pid', 'id', 'createtime', 'updatetime']) ? "{$orderby} {$orderway}" : "id {$orderway}");

        $list = self::with('user')
            ->where($where)
            ->where($condition)
            ->field($field)
            ->order($order)
            ->cache($cache)
            ->paginate($pagesize, false, ['type' => '\\addons\\cms\\library\\Bootstrap', 'var_page' => 'cp', 'fragment' => $fragment]);
        self::render($list);
        return $list;
    }

    public static function render(&$list)
    {
        foreach ($list as $k => &$v) {
        }
        return $list;
    }

    /**
     * 关联会员模型
     */
    public function user()
    {
        return $this->belongsTo("app\common\model\User")->field('id,nickname,avatar,email')->setEagerlyType(1);
    }

    /**
     * 关联文章模型
     */
    public function archives()
    {
        return $this->belongsTo("addons\cms\model\Archives", 'aid')->field('id,title,image,style,diyname,model_id,channel_id,likes,dislikes,tags,createtime')->setEagerlyType(1);
    }

    /**
     * 关联单页模型
     */
    public function spage()
    {
        return $this->belongsTo("addons\cms\model\Page", 'aid')->field('id,title,createtime')->setEagerlyType(1);
    }
}
