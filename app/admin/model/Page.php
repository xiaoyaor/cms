<?php

namespace addons\cms\app\admin\model;

use think\Model;

class Page extends Model
{

    // 表名
    protected $name = 'cms_page';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'flag_text',
        'status_text',
        'url'
    ];

    protected static function init()
    {
    }

    public static function onAfterInsert($row)
    {
        $row->save(['weigh' => $row['id']]);
        return true;
    }

    public function getUrlAttr($value, $data)
    {
        return addon_url('cms/page/index', [':diyname' => $data['diyname']]);
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

    public function getFlagList()
    {
        return ['hot' => __('Hot'), 'index' => __('Index'), 'recommend' => __('Recommend')];
    }

    public function getFlagTextAttr($value, $data)
    {
        $value = $value ? $value : $data['flag'];
        $valueArr = explode(',', $value);
        $list = $this->getFlagList();
        return implode(',', array_intersect_key($list, array_flip($valueArr)));
    }
}
