<?php

namespace addons\cms\library;

use addons\cms\model\Modelx;
use addons\xunsearch\library\Xunsearch;
use think\Config;
use think\Exception;
use think\View;

class FulltextSearch
{

    public static function config()
    {
        $data = [
            [
                'name'   => 'cms',
                'title'  => 'CMS内容管理系统',
                'fields' => [
                    ['name' => 'pid', 'type' => 'id', 'title' => '主键'],
                    ['name' => 'id', 'type' => 'numeric', 'title' => 'ID'],
                    ['name' => 'title', 'type' => 'title', 'title' => '标题'],
                    ['name' => 'content', 'type' => 'body', 'title' => '内容',],
                    ['name' => 'type', 'type' => 'string', 'title' => '类型', 'index' => 'self'],
                    ['name' => 'category_id', 'type' => 'numeric', 'title' => '分类ID', 'index' => 'self',],
                    ['name' => 'user_id', 'type' => 'numeric', 'title' => '会员ID', 'index' => 'self',],
                    ['name' => 'url', 'type' => 'string', 'title' => '链接',],
                    ['name' => 'views', 'type' => 'numeric', 'title' => '浏览次数',],
                    ['name' => 'comments', 'type' => 'numeric', 'title' => '评论次数',],
                    ['name' => 'createtime', 'type' => 'date', 'title' => '发布时间',],
                ]
            ]
        ];
        return $data;
    }

    /**
     * 重置搜索索引数据库
     */
    public static function reset()
    {
        \addons\cms\model\Archives::where('status', 'normal')->chunk(100, function ($list) {
            foreach ($list as $item) {
                self::add($item);
            }
        });
        return true;
    }

    /**
     * 添加索引
     * @param $row
     */
    public static function add($row)
    {
        self::update($row, true);
    }

    /**
     * 更新索引
     * @param      $row
     * @param bool $add
     */
    public static function update($row, $add = false)
    {
        if (is_numeric($row)) {
            $row = \addons\cms\model\Archives::get($row);
            if (!$row) {
                return;
            }
        }
        if (isset($row['status']) && $row['status'] != 'normal') {
            self::del($row);
            return;
        }
        $data = [];
        if ($row instanceof \addons\cms\model\Archives || $row instanceof \app\admin\model\cms\Archives) {
            $content = '';
            $model = Modelx::get($row['model_id']);
            if ($model) {
                $content = \think\Db::name($model['table'])->where('id', $row['id'])->value("content");
                $content = $content ? strip_tags($content) : '';
            }
            $data['id'] = isset($row['id']) ? $row['id'] : 0;
            $data['title'] = isset($row['title']) ? $row['title'] : '';
            $data['category_id'] = isset($row['category_id']) ? $row['category_id'] : 0;
            $data['user_id'] = isset($row['user_id']) ? $row['user_id'] : 0;
            $data['content'] = $content;
            $data['comments'] = isset($row['comments']) ? $row['comments'] : 0;
            $data['createtime'] = isset($row['createtime']) ? $row['createtime'] : 0;
            $data['views'] = isset($row['views']) ? $row['views'] : 0;
            $data['type'] = 'archives';
            $data['url'] = $row->fullurl;
        }
        if ($data) {
            $data['pid'] = substr($data['type'], 0, 1) . $data['id'];
            Xunsearch::instance('cms')->update($data, $add);
        }
    }

    /**
     * 删除
     * @param $row
     */
    public static function del($row)
    {
        $pid = "a" . (is_numeric($row) ? $row : ($row && isset($row['id']) ? $row['id'] : 0));
        if ($pid) {
            Xunsearch::instance('cms')->del($pid);
        }
    }

    /**
     * 获取搜索结果
     * @return array
     */
    public static function search($q, $page = 1, $pagesize = 20, $order = '', $fulltext = true, $fuzzy = false, $synonyms = false)
    {
        return Xunsearch::instance('cms')->search($q, $page, $pagesize, $order, $fulltext, $fuzzy, $synonyms);
    }

    /**
     * 获取建议搜索关键字
     * @param string $q     关键字
     * @param int    $limit 返回条数
     */
    public static function suggestion($q, $limit = 10)
    {
        return Xunsearch::instance('cms')->suggestion($q, $limit);
    }

    /**
     * 获取搜索热门关键字
     * @return array
     * @throws \XSException
     */
    public static function hot()
    {
        return Xunsearch::instance('cms')->getXS()->search->getHotQuery();
    }

}