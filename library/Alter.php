<?php

namespace addons\cms\library;

class Alter
{
    protected static $instance = null;
    protected $config = [];
    protected $data = [
        'table'   => '',
        'oldname' => '',
        'name'    => '',
        'type'    => 'VARCHAR',
        'length'  => '255',
        'content' => '',
        'comment' => '',
        'after'   => '',
    ];

    public function __construct($options = [])
    {
        $this->options = array_merge($this->config, $options);
    }

    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }

        return self::$instance;
    }

    public function setTable($table)
    {
        $this->data['table'] = db()->name($table)->getTable();
        return $this;
    }

    public function setType($type)
    {
        switch ($type) {
            case 'checkbox':
            case 'selects':
                $this->data['type'] = 'SET';
                break;
            case 'radio':
            case 'select':
                $this->data['type'] = 'ENUM';
                break;
            case 'number':
                $this->data['type'] = 'INT';
                break;
            case 'date':
            case 'datetime':
            case 'time':
                $this->data['type'] = strtoupper($type);
                break;
            case 'editor':
                $this->data['type'] = 'TEXT';
                break;
            default:
                $this->data['type'] = 'VARCHAR';
                break;
        }
        return $this;
    }

    public function setOldname($oldname)
    {
        $this->data['oldname'] = $oldname;
        return $this;
    }

    public function setName($name)
    {
        $this->data['name'] = $name;
        return $this;
    }

    public function setLength($length)
    {
        $this->data['length'] = $length;
        return $this;
    }

    public function setContent($content)
    {
        $this->data['content'] = $content;
        return $this;
    }

    public function setComment($comment)
    {
        $this->data['comment'] = $comment;
        return $this;
    }

    public function setDefaultvalue($defaultvalue)
    {
        $this->data['defaultvalue'] = $defaultvalue;
        return $this;
    }

    public function setDecimals($decimals)
    {
        $this->data['decimals'] = $decimals;
        return $this;
    }

    protected function process()
    {
        if ($this->data['type'] == 'INT') {
            if ($this->data['decimals'] > 0) {
                $this->data['type'] = 'DECIMAL';
                $this->data['length'] = "({$this->data['length']},{$this->data['decimals']})";
            } else {
                $this->data['length'] = "({$this->data['length']})";
            }
            $this->data['defaultvalue'] = $this->data['defaultvalue'] == '' ? 'NULL' : $this->data['defaultvalue'];
        } elseif (in_array($this->data['type'], ['SET', 'ENUM'])) {
            $content = \app\common\model\Config::decode($this->data['content']);
            $this->data['length'] = "('" . implode("','", array_keys($content)) . "')";
            $this->data['defaultvalue'] = in_array($this->data['defaultvalue'], array_keys($content)) ? $this->data['defaultvalue'] : ($this->data['type'] == 'ENUM' ? key($content) : '');
        } elseif (in_array($this->data['type'], ['DATE', 'TIME', 'DATETIME'])) {
            $this->data['length'] = '';
            $this->data['defaultvalue'] = "NULL";
        } elseif (in_array($this->data['type'], ['TEXT'])) {
            $this->data['length'] = "(0)";
            $this->data['defaultvalue'] = 'NULL';
        } else {
            $this->data['length'] = "({$this->data['length']})";
        }
        $this->data['defaultvalue'] = strtoupper($this->data['defaultvalue']) === 'NULL' ? "NULL" : "'{$this->data['defaultvalue']}'";
    }

    /**
     * 获取添加字段的SQL
     * @return string
     */
    public function getAddSql()
    {
        $this->process();

        $sql = "ALTER TABLE `{$this->data['table']}` "
            . "ADD `{$this->data['name']}` {$this->data['type']} {$this->data['length']} "
            . "DEFAULT {$this->data['defaultvalue']} "
            . "COMMENT '{$this->data['comment']}' "
            . ($this->data['after'] ? "AFTER `{$this->data['after']}`" : '');
        return $sql;
    }

    public function getModifySql()
    {
        $this->process();

        $sql = "ALTER TABLE `{$this->data['table']}` "
            . ($this->data['oldname'] ? 'CHANGE' : 'MODIFY') . " COLUMN " . ($this->data['oldname'] ? "`{$this->data['oldname']}`" : '') . " `{$this->data['name']}` {$this->data['type']} {$this->data['length']} "
            . "DEFAULT {$this->data['defaultvalue']} "
            . "COMMENT '{$this->data['comment']}' "
            . ($this->data['after'] ? "AFTER `{$this->data['after']}`" : '');
        return $sql;
    }

    /**
     * 获取删除字段的SQL
     * @return string
     */
    public function getDropSql()
    {
        $sql = "ALTER TABLE `{$this->data['table']}` "
            . "DROP `{$this->data['name']}`";
        return $sql;
    }
}
