<?php

namespace addons\cms\app\common\model;

class Fields extends \think\Model
{

    // 表名
    protected $name = 'cms_fields';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'content_list',
        'isrequire',
    ];
    protected static $listFields = ['select', 'selects', 'checkbox', 'radio', 'array'];

    protected static function init()
    {
    }

    public function getIsrequireAttr($value, $data)
    {
        return $data['rule'] && in_array('required', explode(',', $data['rule']));
    }

    /**
     * 获取字典列表字段
     * @return array
     */
    public static function getListFields()
    {
        return self::$listFields;
    }

    public function getContentListAttr($value, $data)
    {
        return in_array($data['type'], self::$listFields) ? \app\common\model\Config::decode($data['content']) : $data['content'];
    }

    public function model()
    {
        return $this->belongsTo('Modelx', 'model_id')->setEagerlyType(0);
    }
}
