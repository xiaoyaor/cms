<?php

namespace app\admin\validate\cms;

use think\Validate;

class Fields extends Validate
{

    /**
     * 验证规则
     */
    protected $rule = [
        'name|名称'            => 'require|unique:cms_fields,model_id^name',
        'title|管理员'          => 'require',
        'model_id|模型ID'      => 'require|integer',
        'diyform_id|自定义表单ID' => 'require|integer',
        'status|状态'          => 'require|in:normal,hidden',
    ];

    /**
     * 提示消息
     */
    protected $message = [
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => [
            'name', 'title', 'model_id', 'diyform_id', 'status'
        ],
        'edit' => [
            'name', 'title', 'model_id', 'diyform_id', 'status'
        ],
    ];

    public function __construct(array $rules = array(), $message = array(), $field = array())
    {
        //如果是编辑模式，则排除下主键
        $ids = request()->param("ids");
        $model_id = request()->param('model_id');
        $fieldName = $model_id ? 'model_id' : 'diyform_id';
        if ($ids) {
            $this->rule['name|名称'] = "require|unique:cms_fields,{$fieldName}^name,{$ids},id";
        } else {
            $this->rule['name|名称'] = "require|unique:cms_fields,{$fieldName}^name";
        }
        parent::__construct($rules, $message, $field);
    }
}
