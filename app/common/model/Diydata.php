<?php

namespace addons\cms\app\common\model;

use think\Model;

class Diydata extends Model
{

    protected static $tableName = null;

    public function __construct($name = null)
    {
        if (!is_null($name)) {
            self::$tableName = $name;
        }

        $this->name = self::$tableName;
        // 执行初始化操作
        $this->initialize();
    }

    /**
     * 写入数据
     * @access public
     * @param array      $data  数据数组
     * @param array|true $field 允许字段
     * @return $this
     */
    public static function create($data = [], $field = null)
    {
        $model = new static();
        if (!empty($field)) {
            $model->allowField($field);
        }
        $model->isUpdate(false)->save($data, []);
        return $model;
    }

}
