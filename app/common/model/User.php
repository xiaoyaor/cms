<?php

namespace addons\cms\app\common\model;

use addons\cms\library\Service;
use app\common\library\Auth;
use think\Model;

/**
 * 会员模型
 */
class User Extends \app\common\model\User
{

    /**
     * 获取单页列表
     */
    public static function getUserList($params)
    {
        $name = empty($params['name']) ? '' : $params['name'];
        $condition = empty($params['condition']) ? '' : $params['condition'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $row = empty($params['row']) ? 10 : (int)$params['row'];
        $orderby = empty($params['orderby']) ? 'nums' : $params['orderby'];
        $orderway = empty($params['orderway']) ? 'desc' : strtolower($params['orderway']);
        $limit = empty($params['limit']) ? $row : $params['limit'];
        $cache = !isset($params['cache']) ? true : (int)$params['cache'];
        $imgwidth = empty($params['imgwidth']) ? '' : $params['imgwidth'];
        $imgheight = empty($params['imgheight']) ? '' : $params['imgheight'];
        $orderway = in_array($orderway, ['asc', 'desc']) ? $orderway : 'desc';

        $where = [];
        if ($name !== '') {
            $where['name'] = $name;
        }
        $order = $orderby == 'rand' ? 'rand()' : (in_array($orderby, ['id', 'username', 'money', 'score', 'level', 'jointime', 'logintime']) ? "{$orderby} {$orderway}" : "id {$orderway}");

        $list = self::where($where)
            ->where($condition)
            ->field($field)
            ->orderRaw($order)
            ->limit($limit)
            ->cache($cache)
            ->select();
        self::render($list, $imgwidth, $imgheight);
        return $list;
    }

    public static function render(&$list, $imgwidth, $imgheight)
    {
        $width = $imgwidth ? 'width="' . $imgwidth . '"' : '';
        $height = $imgheight ? 'height="' . $imgheight . '"' : '';
        foreach ($list as $k => &$v) {
            $v['textlink'] = '<a href="' . $v['url'] . '">' . $v['nickname'] . '</a>';
            $v['imglink'] = '<a href="' . $v['url'] . '"><img src="' . $v['avatar'] . '" border="" ' . $width . ' ' . $height . ' /></a>';
            $v['img'] = '<img src="' . $v['avatar'] . '" border="" ' . $width . ' ' . $height . ' />';
        }
        return $list;
    }

}
