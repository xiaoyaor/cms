<?php

namespace addons\cms\app\admin\model;

use addons\cms\library\FulltextSearch;
use addons\cms\library\Service;
use app\common\model\Config;
use app\common\model\User;
use think\Model;
use traits\model\SoftDelete;

class Archives extends Model
{
    //use SoftDelete;

    // 表名
    protected $name = 'cms_archives';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';
    // 追加属性
    protected $append = [
        'flag_text',
        'status_text',
        'publishtime_text',
        'url',
        'fullurl',
        'style_bold',
        'style_color',
    ];

    public function getUrlAttr($value, $data)
    {
        $diyname = $data['diyname'] ? $data['diyname'] : $data['id'];
        return addon_url('cms/archives/index', [':id' => $data['id'], ':diyname' => $diyname, ':channel' => $data['channel_id']], true, true);
    }

    public function getFullurlAttr($value, $data)
    {
        $diyname = $data['diyname'] ? $data['diyname'] : $data['id'];
        return addon_url('cms/archives/index', [':id' => $data['id'], ':diyname' => $diyname, ':channel' => $data['channel_id']], true, true);
    }

    public function getOriginData()
    {
        return $this->origin;
    }

    /**
     * 批量设置数据
     * @param $data
     * @return $this
     */
    public function setData($data)
    {
        if (is_object($data)) {
            $data = get_object_vars($data);
        }
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    protected static function init()
    {
    }

    public static function onAfterWrite($row)
    {
        $config = get_addon_config('cms');
        if (isset($row['channel_id'])) {
            //在更新成功后刷新副表、TAGS表数据、栏目表
            $channel = Channel::get($row->channel_id);
            if ($channel) {
                $model = Modelx::get($channel['model_id']);
                if ($model && isset($row['content'])) {
                    $values = array_intersect_key($row->getData(), array_flip($model->fields));
                    $values['id'] = $row['id'];
                    $values['content'] = $row['content'];
                    db($model['table'])->insert($values, true);
                }
            }
        }
        if (isset($row['tags'])) {
            $tags = array_filter(explode(',', $row['tags']));
            if ($tags) {
                $tagslist = Tags::where('name', 'in', $tags)->select();
                foreach ($tagslist as $k => $v) {
                    $archives = explode(',', $v['archives']);
                    if (!in_array($row['id'], $archives)) {
                        $archives[] = $row['id'];
                        $v->archives = implode(',', $archives);
                        $v->nums++;
                        $v->save();
                    }
                    $tags = array_udiff($tags, [$v['name']], 'strcasecmp');
                }
                $list = [];
                foreach ($tags as $k => $v) {
                    $list[] = ['name' => $v, 'archives' => $row['id'], 'nums' => 1];
                }
                if ($list) {
                    (new Tags())->saveAll($list);
                }
            }
        }
        $changedData = $row->getChangedData();
        if (isset($changedData['status']) && $changedData['status'] == 'normal') {
            //增加积分
            User::score($config['score']['postarchives'], $row['user_id'], '发布文章');
            //推送到熊掌号和百度站长
            if ($config['baidupush']) {
                $urls = [$row->fullurl];
                \think\Hook::listen("baidupush", $urls);
            }
        }
        if ($config['searchtype'] == 'xunsearch') {
            //更新全文搜索
            FulltextSearch::update($row->id);
        }
        return true;
    }

    public static function onAfterInsert($row)
    {
        $pk = $row->getPk();
        $channel = Channel::get($row['channel_id']);
        $row->getQuery()->where($pk, $row[$pk])->update(['model_id' => $channel ? $channel['model_id'] : 0]);
        Channel::where('id', $row['channel_id'])->setInc('items');
        return true;
    }

    public static function onBeforeInsert($row)
    {
        return true;
    }

    public static function onBeforeWrite($row)
    {
        $changedData = $row->getChangedData();
        if (isset($changedData['flag'])) {
            $row['weigh'] = is_array($changedData['flag']) && in_array('top', $changedData['flag'])
                ? ($row['weigh'] == 0 ? 9999 : $row['weigh'])
                : ($row['weigh'] == 9999 ? 0 : $row['weigh']);
        }

        if (isset($row['content'])) {
            $row['content'] = Service::autolinks($row['content']);
        }
        //在更新之前对数组进行处理
        foreach ($row->getData() as $k => $value) {
            if (is_array($value) && is_array(reset($value))) {
                $value = json_encode(self::getArrayData($value), JSON_UNESCAPED_UNICODE);
            } else {
                $value = is_array($value) ? implode(',', $value) : $value;
            }
            $row->$k = $value;
        }
        return true;
    }

    public static function onAfterDelete($row)
    {
        $config = get_addon_config('cms');
        if ($row['status'] == 'normal') {
            User::score(-$config['score']['postarchives'], $row['user_id'], '删除文章');
        }
        if ($config['searchtype'] == 'xunsearch') {
            FulltextSearch::del($row);
        }
        return true;
    }

    public function getFlagList()
    {
        return ['hot' => __('Hot'), 'new' => __('New'), 'recommend' => __('Recommend'), 'top' => __('Top')];
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden'), 'rejected' => __('Status rejected'), 'pulloff' => __('Status pulloff')];
    }

    public function getStyleBoldAttr($value, $data)
    {
        return in_array('b', explode('|', $data['style']));
    }

    public function getStyleColorAttr($value, $data)
    {
        $result = preg_match("/(#([0-9a-z]{6}))/i", $data['style'], $matches);
        return $result ? $matches[1] : '';
    }

    public function getFlagTextAttr($value, $data)
    {
        $value = $value ? $value : $data['flag'];
        $valueArr = $value ? explode(',', $value) : [];
        $list = $this->getFlagList();
        return implode(',', array_intersect_key($list, array_flip($valueArr)));
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getPublishtimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['publishtime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setPublishtimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : ($value ? $value : null);
    }

    protected function setCreatetimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : ($value ? $value : null);
    }

    public static function getArrayData($data)
    {
        if (!isset($data['value'])) {
            $result = [];
            foreach ($data as $index => $datum) {
                $result['field'][$index] = $datum['key'];
                $result['value'][$index] = $datum['value'];
            }
            $data = $result;
        }
        $fieldarr = $valuearr = [];
        $field = isset($data['field']) ? $data['field'] : (isset($data['key']) ? $data['key'] : []);
        $value = isset($data['value']) ? $data['value'] : [];
        foreach ($field as $m => $n) {
            if ($n != '') {
                $fieldarr[] = $field[$m];
                $valuearr[] = $value[$m];
            }
        }
        return $fieldarr ? array_combine($fieldarr, $valuearr) : [];
    }

    public function channel()
    {
        return $this->belongsTo('Channel', 'channel_id', '', [], 'LEFT')->setEagerlyType(0);
    }
}
