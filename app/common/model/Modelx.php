<?php

namespace addons\cms\app\common\model;

use think\Model;

/**
 * 模型
 */
class Modelx extends Model
{
    protected $name = "cms_model";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
    ];

    public function getFieldsAttr($value, $data)
    {
        return is_array($value) ? $value : ($value ? explode(',', $value) : []);
    }

    public function getSettingAttr($value, $data)
    {
        return (array)json_decode($value, true);
    }

    public function getFieldsListAttr($value, $data)
    {
        return Fields::where('model_id', $data['id'])->where('status', 'normal')->cache(true)->select();
    }

    public function getFieldsContentList($model_id)
    {
        $list = Fields::where('model_id', $model_id)
            ->field('id,name,type,content')
            ->where('status', 'normal')
            ->cache(true)
            ->select();
        $fieldsList = [];
        $listFields = Fields::getListFields();
        foreach ($list as $index => $item) {
            if (in_array($item['type'], $listFields)) {
                $fieldsList[$item['name']] = $item['content_list'];
            }
        }
        return $fieldsList;
    }
}
