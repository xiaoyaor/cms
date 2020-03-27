<?php

namespace addons\cms\app\common\model;

use think\Cache;
use think\Db;
use think\Model;
use think\View;

/**
 * 栏目模型
 */
class Channel extends Model
{
    protected $name = "cms_channel";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
        'url',
        'fullurl'
    ];
    protected static $config = [];

    protected static $parentIds = null;

    protected static $outlinkParentIds = null;

    protected static function init()
    {
        $config = get_addon_config('cms');
        self::$config = $config;
    }

    public function getUrlAttr($value, $data)
    {
        $diyname = $data['diyname'] ? $data['diyname'] : $data['id'];
        return isset($data['type']) && isset($data['outlink']) && $data['type'] == 'link' ? $this->getAttr('outlink') : addon_url('cms/channel/index', [':id' => $data['id'], ':diyname' => $diyname], true);
    }

    public function getFullurlAttr($value, $data)
    {
        $diyname = $data['diyname'] ? $data['diyname'] : $data['id'];
        return isset($data['type']) && isset($data['outlink']) && $data['type'] == 'link' ? $this->getAttr('outlink') : addon_url('cms/channel/index', [':id' => $data['id'], ':diyname' => $diyname], true, true);
    }

    public function getImageAttr($value, $data)
    {
        $value = $value ? $value : self::$config['default_channel_img'];
        return cdnurl($value);
    }

    public function getOutlinkAttr($value, $data)
    {
        $indexUrl = $view_replace_str = config('view_replace_str.__PUBLIC__');
        $indexUrl = rtrim($indexUrl, '/');
        return str_replace('__INDEX__', $indexUrl, $value);
    }

    public function getTagcolorAttr($value, $data)
    {
        $color = ['primary', 'default', 'success', 'warning', 'danger'];
        $index = $data['id'] % count($color);
        return isset($color[$index]) ? $color[$index] : $color[0];
    }

    /**
     * 判断是否拥有子列表
     * @param $value
     * @param $data
     * @return bool|mixed
     */
    public function getHasChildAttr($value, $data)
    {
        static $checked = [];
        if (isset($checked[$data['id']])) {
            return $checked[$data['id']];
        }
        if (is_null(self::$parentIds)) {
            self::$parentIds = self::where('parent_id', '>', 0)->cache(false)->where('status', 'normal')->column('parent_id');
        }
        if (self::$parentIds && in_array($data['id'], self::$parentIds)) {
            return true;
        }
        return false;
    }

    /**
     * 判断是否当前页面
     * @param $value
     * @param $data
     * @return bool
     */
    public function getIsActiveAttr($value, $data)
    {
        $url = request()->url();
        $channel = View::instance()->__CHANNEL__;
        if (($channel && ($channel['id'] == $this->id || $channel['parent_id'] == $this->id)) || $this->url == $url) {
            return true;
        } else {
            if ($this->has_child) {
                if (is_null(self::$outlinkParentIds)) {
                    self::$outlinkParentIds = self::where('type', 'link')->where('status', 'normal')->column('outlink,parent_id');
                }
                if (self::$outlinkParentIds && isset(self::$outlinkParentIds[$url]) && self::$outlinkParentIds[$url] == $this->id) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 获取栏目所有子级的ID
     * @param mixed $ids      栏目ID或集合ID
     * @param bool  $withself 是否包含自身
     * @return array
     */
    public static function getChannelChildrenIds($ids, $withself = true)
    {
        $cacheName = 'childrens-' . $ids . '-' . $withself;
        $result = Cache::get($cacheName);
        if ($result === false) {
            $channelList = Channel::where('status', 'normal')
                ->order('weigh desc,id desc')
                ->cache(true)
                ->select();

            $result = [];
            $tree = \fast\Tree::instance();
            $tree->init(collection($channelList)->toArray(), 'parent_id');
            $channelIds = is_array($ids) ? $ids : explode(',', $ids);
            foreach ($channelIds as $index => $channelId) {
                $result = array_merge($result, $tree->getChildrenIds($channelId, $withself));
            }
            Cache::set($cacheName, $result);
        }
        return $result;
    }

    /**
     * 获取栏目列表
     * @param $tag
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getChannelList($tag)
    {
        $type = empty($tag['type']) ? '' : $tag['type'];
        $typeid = !isset($tag['typeid']) ? '' : $tag['typeid'];
        $model = !isset($tag['model']) ? '' : $tag['model'];
        $condition = empty($tag['condition']) ? '' : $tag['condition'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $row = empty($tag['row']) ? 10 : (int)$tag['row'];
        $orderby = empty($tag['orderby']) ? 'weigh' : $tag['orderby'];
        $orderway = empty($tag['orderway']) ? 'desc' : strtolower($tag['orderway']);
        $limit = empty($tag['limit']) ? $row : $tag['limit'];
        $cache = !isset($tag['cache']) ? true : (int)$tag['cache'];
        $imgwidth = empty($tag['imgwidth']) ? '' : $tag['imgwidth'];
        $imgheight = empty($tag['imgheight']) ? '' : $tag['imgheight'];
        $orderway = in_array($orderway, ['asc', 'desc']) ? $orderway : 'desc';
        $cache = !$cache ? false : $cache;
        $where = ['status' => 'normal'];

        if ($type === 'top') {
            //顶级分类
            $where['parent_id'] = 0;
        } elseif ($type === 'brother') {
            $subQuery = self::where('id', 'in', $typeid)->field('parent_id')->buildSql();
            //同级
            $where['parent_id'] = ['exp', Db::raw(' IN ' . '(' . $subQuery . ')')];
        } elseif ($type === 'son') {
            $subQuery = self::where('parent_id', 'in', $typeid)->field('id')->buildSql();
            //子级
            $where['id'] = ['exp', Db::raw(' IN ' . '(' . $subQuery . ')')];
        } elseif ($type === 'sons') {
            //所有子级
            $where['id'] = ['in', self::getChannelChildrenIds($typeid)];
        } else {
            if ($typeid !== '') {
                $where['id'] = ['in', $typeid];
            }
        }
        if ($model !== '') {
            $where['model_id'] = ['in', $model];
        }
        $order = $orderby == 'rand' ? 'rand()' : (in_array($orderby, ['createtime', 'updatetime', 'views', 'weigh', 'id']) ? "{$orderby} {$orderway}" : "createtime {$orderway}");
        $list = self::where($where)
            ->where($condition)
            ->field($field)
            ->order($order)
            ->limit($limit)
            ->cache($cache)
            ->select();
        self::render($list, $imgwidth, $imgheight);
        return $list;
    }

    /**
     * 渲染数据
     * @param array $list
     * @param int   $imgwidth
     * @param int   $imgheight
     * @return array
     */
    public static function render(&$list, $imgwidth, $imgheight)
    {
        $width = $imgwidth ? 'width="' . $imgwidth . '"' : '';
        $height = $imgheight ? 'height="' . $imgheight . '"' : '';
        foreach ($list as $k => &$v) {
            $v['hasimage'] = $v->getData('image') ? true : false;
            $v['textlink'] = '<a href="' . $v['url'] . '">' . $v['name'] . '</a>';
            $v['channellink'] = '<a href="' . $v['url'] . '">' . $v['name'] . '</a>';
            $v['outlink'] = $v['outlink'];
            $v['imglink'] = '<a href="' . $v['url'] . '"><img src="' . $v['image'] . '" border="" ' . $width . ' ' . $height . ' /></a>';
            $v['img'] = '<img src="' . $v['image'] . '" border="" ' . $width . ' ' . $height . ' />';
        }
        return $list;
    }

    /**
     * 获取面包屑导航
     * @param array $channel
     * @param array $archives
     * @param array $tags
     * @param array $page
     * @return array
     */
    public static function getBreadcrumb($channel, $archives = [], $tags = [], $page = [])
    {
        $list = [];
        $list[] = ['name' => __('Home'), 'url' => addon_url('cms/index/index', [], false)];
        if ($channel) {
            if ($channel['parent_id']) {
                $channelList = self::where('status', 'normal')
                    ->order('weigh desc,id desc')
                    ->field('id,name,type,parent_id,diyname,outlink')
                    ->cache(true)
                    ->select();
                //获取栏目的所有上级栏目
                $parents = \fast\Tree::instance()->init(collection($channelList)->toArray(), 'parent_id')->getParents($channel['id']);
                foreach ($parents as $k => $v) {
                    $list[] = ['name' => $v['name'], 'url' => $v['url']];
                }
            }
            $list[] = ['name' => $channel['name'], 'url' => $channel['url']];
        }
        if ($archives) {
            //$list[] = ['name' => $archives['title'], 'url' => $archives['url']];
        }
        if ($tags) {
            $list[] = ['name' => $tags['name'], 'url' => $tags['url']];
        }
        if ($page) {
            $list[] = ['name' => $page['title'], 'url' => $page['url']];
        }
        return $list;
    }

    /**
     * 获取导航栏目列表HTML
     * @param       $channel
     * @param array $tag
     * @return mixed|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getNav($channel, $tag = [])
    {
        $condition = empty($tag['condition']) ? '' : $tag['condition'];
        $cache = !isset($tag['cache']) ? true : (int)$tag['cache'];
        $maxLevel = !isset($tag['maxlevel']) ? 0 : $tag['maxlevel'];
        $cacheName = 'nav-' . md5(serialize($tag));
        $result = Cache::get($cacheName);
        if ($result === false) {
            $channelList = Channel::where($condition)
                ->where('status', 'normal')
                ->order('weigh desc,id desc')
                ->cache($cache)
                ->select();
            $tree = \fast\Tree::instance();
            $tree->init(collection($channelList)->toArray(), 'parent_id');
            $result = self::getTreeUl($tree, 0, $channel ? $channel['id'] : '', '', 1, $maxLevel);
            Cache::set($cacheName, $result);
        }
        return $result;
    }

    public static function getTreeUl($tree, $myid, $selectedids = '', $disabledids = '', $level = 1, $maxlevel = 0)
    {
        $str = '';
        $childs = $tree->getChild($myid);
        if ($childs) {
            foreach ($childs as $value) {
                $id = $value['id'];
                unset($value['child']);
                $selected = $selectedids && in_array($id, (is_array($selectedids) ? $selectedids : explode(',', $selectedids))) ? 'selected' : '';
                $disabled = $disabledids && in_array($id, (is_array($disabledids) ? $disabledids : explode(',', $disabledids))) ? 'disabled' : '';
                $value = array_merge($value, array('selected' => $selected, 'disabled' => $disabled));
                $value = array_combine(array_map(function ($k) {
                    return '@' . $k;
                }, array_keys($value)), $value);
                $itemtpl = '<li class="@dropdown" value=@id @selected @disabled><a data-toggle="@toggle" data-target="#" href="@url">@name @caret</a> @childlist</li>';
                $nstr = strtr($itemtpl, $value);
                $childlist = '';
                if (!$maxlevel || $level < $maxlevel) {
                    $childdata = self::getTreeUl($tree, $id, $selectedids, $disabledids, $level + 1, $maxlevel);
                    $childlist = $childdata ? '<ul class="dropdown-menu" role="menu">' . $childdata . '</ul>' : "";
                }
                $str .= strtr($nstr, [
                    '@childlist' => $childlist,
                    '@caret'     => $childlist ? ($level == 1 ? '<span class="caret"></span>' : '') : '',
                    '@dropdown'  => $childlist ? ($level == 1 ? 'dropdown' : 'dropdown-submenu') : '',
                    '@toggle'    => $childlist ? 'dropdown' : ''
                ]);
            }
        }
        return $str;
    }

    public function model()
    {
        return $this->belongsTo('Modelx', 'model_id')->setEagerlyType(0);
    }
}
