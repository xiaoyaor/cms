<?php

namespace addons\cms\app\admin\model;

use think\facade\Event;
use think\Model;


class Special extends Model
{


    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'cms_special';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'url',
        'fullurl',
        'flag_text',
        'status_text'
    ];

    protected static function init()
    {
    }

    public static function onAfterWrite($row)
    {
        $config = get_addon_config('cms');
        $changedData = $row->getChangedData();
        if (isset($changedData['status']) && $changedData['status'] == 'normal') {
            //推送到熊掌号+百度站长
            if ($config['baidupush']) {
                $urls = [$row->fullurl];
                Event::listen("baidupush", $urls);
            }
        }
        return true;
    }

    public function getUrlAttr($value, $data)
    {
        $diyname = $data['diyname'] ? $data['diyname'] : $data['id'];
        return addon_url('cms/special/index', [':id' => $data['id'], ':diyname' => $diyname]);
    }

    public function getFullurlAttr($value, $data)
    {
        $diyname = $data['diyname'] ? $data['diyname'] : $data['id'];
        return addon_url('cms/special/index', [':id' => $data['id'], ':diyname' => $diyname], true, true);
    }

    public function getFlagList()
    {
        return ['hot' => __('Hot'), 'new' => __('New'), 'recommend' => __('Recommend'), 'top' => __('Top')];
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }


    public function getFlagTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['flag']) ? $data['flag'] : '');
        $valueArr = explode(',', $value);
        $list = $this->getFlagList();
        return implode(',', array_intersect_key($list, array_flip($valueArr)));
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setFlagAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }


}
