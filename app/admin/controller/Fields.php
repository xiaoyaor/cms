<?php

namespace addons\cms\app\admin\controller;

use app\common\model\Config;
use addons\base\app\admin\controller\AppBackend;

/**
 * 模型字段表
 *
 * @icon fa fa-circle-o
 */
class Fields extends AppBackend
{

    /**
     * Fields模型对象
     */
    protected $model = null;
    protected $modelValidate = true;
    protected $modelSceneValidate = true;

    protected $noNeedRight = ['rulelist'];
    protected $multiFields = 'isfilter,iscontribute';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \addons\cms\app\admin\model\Fields;
        $this->assign("statusList", $this->model->getStatusList());
        $this->assign('typeList', Config::getTypeList());
        $this->assign('regexList', Config::getRegexList());
    }

    /**
     * 查看
     */
    public function index()
    {
        $model_id = $this->request->param('model_id', 0);
        $diyform_id = $this->request->param('diyform_id', 0);
        $condition = $model_id ? ['model_id' => $model_id] : ['diyform_id' => $diyform_id];
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($condition)
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($condition)
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $nameArr = [
                'id'          => '主键',
                'user_id'     => '会员ID',
                'channel_id'  => '栏目ID',
                'model_id'    => '模型ID',
                'special_id'  => '专题ID',
                'title'       => '标题',
                'style'       => '样式',
                'flag'        => '标志',
                'image'       => '缩略图',
                'keywords'    => '关键字',
                'description' => '描述',
                'tags'        => '标签',
                'weigh'       => '权重',
                'views'       => '浏览次数',
                'comments'    => '评论次数',
                'likes'       => '点赞次数',
                'dislikes'    => '点踩次数',
                'diyname'     => '自定义名称',
                'createtime'  => '创建时间',
                'updatetime'  => '更新时间',
                'publishtime' => '发布时间',
                'deletetime'  => '删除时间',
                'memo'        => '备注',
                'status'      => '状态'
            ];
            if ($model_id) {
                $list = collection($list)->toArray();
                $tableInfoList = \think\Db::name('cms_archives')->getTableInfo();
                $tableInfoList['fields'] = array_reverse($tableInfoList['fields']);
                foreach ($tableInfoList['fields'] as $index => $field) {
                    $type = isset($tableInfoList['type'][$field]) ? substr($tableInfoList['type'][$field], 0, stripos($tableInfoList['type'][$field], '(')) : 'unknown';
                    $item = [
                        'state' => false, 'model_id' => $model_id, 'diyform_id' => '-', 'name' => $field, 'title' => isset($nameArr[$field]) ? $nameArr[$field] : '',
                        'type'  => $type, 'issystem' => true, 'isfilter' => 0, 'iscontribute' => 0, 'status' => 'normal', 'createtime' => 0, 'updatetime' => 0
                    ];
                    $list[] = $item;
                }
            }
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        $this->assignconfig('model_id', $model_id);
        $this->assignconfig('diyform_id', $diyform_id);
        $this->assign('model_id', $model_id);
        $this->assign('diyform_id', $diyform_id);

        $model = $model_id ? \addons\cms\app\admin\model\Modelx::get($model_id) : \addons\cms\app\admin\model\Diyform::get($diyform_id);
        $this->assign('model', $model);
        $modelList = $model_id ? \addons\cms\app\admin\model\Modelx::all() : \addons\cms\app\admin\model\Diyform::all();
        $this->assign('modelList', $modelList);

        return $this->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        $model_id = $this->request->param('model_id', 0);
        $diyform_id = $this->request->param('diyform_id', 0);
        $this->assign('model_id', $model_id);
        $this->assign('diyform_id', $diyform_id);
        return parent::add();
    }

    /**
     * 批量操作
     * @param string $ids
     */
    public function multi($ids = "")
    {
        $params = $this->request->request('params');
        parse_str($params, $paramsArr);
        if (isset($paramsArr['isfilter'])) {
            $field = \addons\cms\app\admin\model\Fields::get($ids);
            if (!$field || !in_array($field['type'], ['radio', 'checkbox', 'select', 'selects', 'array'])) {
                $this->error('只有类型为单选、复选、下拉列表、数组才可以加入列表筛选');
            }
        }
        return parent::multi($ids);
    }

    /**
     * 规则列表
     * @internal
     */
    public function rulelist()
    {
        //主键
        $primarykey = $this->request->request("keyField");
        //主键值
        $keyValue = $this->request->request("keyValue", "");

        $keyValueArr = array_filter(explode(',', $keyValue));
        $regexList = Config::getRegexList();
        $list = [];
        foreach ($regexList as $k => $v) {
            if ($keyValueArr) {
                if (in_array($k, $keyValueArr)) {
                    $list[] = ['id' => $k, 'name' => $v];
                }
            } else {
                $list[] = ['id' => $k, 'name' => $v];
            }
        }
        return json(['list' => $list]);
    }
}
