<?php

namespace addons\cms\app\admin\controller;

use addons\base\app\admin\controller\AppBackend;

/**
 * 标签表
 *
 * @icon fa fa-tags
 */
class Tags extends AppBackend
{

    /**
     * Tags模型对象
     */
    protected $model = null;
    protected $noNeedRight = ['selectpage', 'autocomplete'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \addons\cms\app\admin\model\Tags;
    }

    public function selectpage()
    {
        $response = parent::selectpage();
        $word = (array)$this->request->request("q_word/a");
        if (array_filter($word)) {
            $result = $response->getData();
            $list = [];
            foreach ($result['list'] as $index => $item) {
                $list[] = strtolower($item['name']);
            }
            foreach ($word as $k => $v) {
                if (!in_array(strtolower($v), $list)) {
                    array_unshift($result['list'], ['id' => $v, 'name' => $v]);
                }
                $result['total']++;
            }
            $response->data($result);
        }
        return $response;
    }

    public function autocomplete()
    {
        $q = $this->request->request('q');
        $list = \addons\cms\app\admin\model\Tags::where('name', 'like', '%' . $q . '%')->column('name');
        echo json_encode($list);
        return;
    }

}
