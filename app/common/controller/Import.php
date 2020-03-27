<?php

namespace addons\cms\app\common\controller;

use addons\cms\library\VicDict;

/**
 * 导入
 * Class Import
 * @package addons\cms\controller
 */
class Import extends Base
{
    protected $noNeedLogin = ["*"];
    protected $layout = 'default';

    public function _initialize()
    {
        parent::_initialize();

        if (!$this->request->isCli()) {
            $this->error('只允许在终端进行操作!');
        }
    }

    /**
     * 导入词典
     */
    public function dict()
    {
        define('_VIC_WORD_DICT_PATH_', ADDON_PATH . 'cms/data/dict.json');
        $dict = new VicDict('json');

        //添加词语词库 add(词语,词性) 可以是除保留字符（/，\ ， \x  ，\i），以外的utf-8编码的任何字符
        $lines = file(ADDON_PATH . 'cms/data/dict.txt', FILE_IGNORE_NEW_LINES);
        foreach ($lines as $index => $line) {
            $lineArr = explode(' ', $line);
            $dict->add($lineArr[0], 'n');
        }

        //保存词库
        $dict->save();
        echo "done";
    }

}
