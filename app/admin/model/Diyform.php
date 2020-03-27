<?php

namespace addons\cms\app\admin\model;

use think\Config;
use think\Model;

class Diyform extends Model
{

    // 表名
    protected $name = 'cms_diyform';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'url'
    ];

    public static function init()
    {
    }

    public static function onAfterInsert($row)
    {
        $prefix = Config::get('database.prefix');
        $sql = "CREATE TABLE `{$prefix}{$row['table']}` (
              `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `user_id` int(10) DEFAULT NULL COMMENT '会员ID',
              `createtime` int(10) DEFAULT NULL COMMENT '添加时间',
              `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
              PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='{$row['name']}'";
        db()->query($sql);
        return true;
    }

    public function getUrlAttr($value, $data)
    {
        $diyname = $data['diyname'] ? $data['diyname'] : $data['id'];
        return addon_url('cms/diyform/index', [':id' => $data['id'], ':diyname' => $diyname]);
    }

    public function getFieldsAttr($value, $data)
    {
        return is_array($value) ? $value : ($value ? explode(',', $value) : []);
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }
}
