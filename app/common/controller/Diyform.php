<?php

namespace addons\cms\app\common\controller;

use addons\cms\library\Service;
use addons\cms\model\Diyform as DiyformModel;
use addons\cms\model\Fields;
use think\Config;
use think\Exception;
use think\Hook;

/**
 * 自定义表单控制器
 * Class Diyform
 * @package addons\cms\controller
 */
class Diyform extends Base
{

    /**
     * 表单
     */
    public function index()
    {
        $diyname = $this->request->param('diyname');
        if ($diyname && !is_numeric($diyname)) {
            $diyform = DiyformModel::getByDiyname($diyname);
        } else {
            $id = $diyname ? $diyname : $this->request->get('id', '');
            $diyform = DiyformModel::get($id);
        }
        if (!$diyform || $diyform['status'] != 'normal') {
            $this->error(__('表单未找到'));
        }
        if ($diyform['needlogin'] && !$this->auth->id) {
            $this->error(__('请登录后再操作'));
        }
        $fields = DiyformModel::getDiyformFields($diyform['id']);
        $data = [
            'fields' => $fields
        ];
        $diyform['fieldslist'] = $this->fetch('common/fields', $data);
        $this->view->assign("__DIYFORM__", $diyform);
        Config::set('cms.title', $diyform['title']);
        Config::set('cms.keywords', $diyform['keywords']);
        Config::set('cms.description', $diyform['description']);

        // 语言检测
        $lang = strip_tags($this->request->langset());
        $site = Config::get("site");
        $upload = \app\common\model\Config::upload();
        // 上传信息配置后
        Hook::listen("upload_config_init", $upload);

        // 配置信息
        $config = [
            'site'           => array_intersect_key($site, array_flip(['name', 'cdnurl', 'version', 'timezone', 'languages'])),
            'upload'         => $upload,
            'modulename'     => 'addons',
            'controllername' => 'diyform',
            'actionname'     => 'index',
            'jsname'         => 'diyform/index',
            'moduleurl'      => rtrim(url("/index", '', false), '/'),
            'language'       => $lang
        ];
        $config = array_merge($config, Config::get("view_replace_str"));

        Config::set('upload', array_merge(Config::get('upload'), $upload));
        // 配置信息后
        Hook::listen("config_init", $config);

        $this->view->assign('jsconfig', $config);
        $this->view->assign('diyform', $diyform);

        Config::set('cms.title', isset($diyform['seotitle']) && $diyform['seotitle'] ? $diyform['seotitle'] : $diyform['title']);
        Config::set('cms.keywords', $diyform['keywords']);
        Config::set('cms.description', $diyform['description']);

        $template = preg_replace("/\.html$/i", "", $diyform['formtpl'] ? $diyform['formtpl'] : 'diyform');
        $template = $this->request->get("noframe", "0") ? "diyform_noframe" : $template;
        return $this->view->fetch('/' . $template);
    }

    /**
     * 提交
     */
    public function post()
    {
        if ($this->request->isPost()) {
            $config = get_addon_config('cms');
            $diyname = $this->request->post('__diyname__');
            $token = $this->request->post('__token__');
            if (session('__token__') != $token) {
                $this->error("Token不正确！", null, ['token' => $this->request->token()]);
            }
            $diyform = DiyformModel::getByDiyname($diyname);
            if (!$diyform || $diyform['status'] != 'normal') {
                $this->error(__('表单未找到'));
            }
            if ($diyform['needlogin'] && !$this->auth->id) {
                $this->error(__('请登录后再操作'));
            }

            $row = $this->request->post('row/a');

            $fields = DiyformModel::getDiyformFields($diyform['id']);
            foreach ($fields as $index => $field) {
                if ($field['isrequire'] && (!isset($row[$field['name']]) || $row[$field['name']] == '')) {
                    $this->error("{$field['title']}不能为空！", null, ['token' => $this->request->token()]);
                }
            }

            $row['user_id'] = $this->auth->id;
            $row['createtime'] = time();
            $row['updatetime'] = time();
            foreach ($row as $index => &$value) {
                if (is_array($value) && isset($value['field'])) {
                    $value = json_encode(\app\common\model\Config::getArrayData($value), JSON_UNESCAPED_UNICODE);
                } else {
                    $value = is_array($value) ? implode(',', $value) : $value;
                }
            }
            try {
                \think\Db::name($diyform['table'])->insert($row);
            } catch (Exception $e) {
                $this->error("发生错误:" . $e->getMessage());
            }
            //发送通知
            Service::notice('CMS收到新的' . $diyform['name'], $config['auditnotice'], $config['noticetemplateid']);

            $this->success($diyform['successtips'] ? $diyform['successtips'] : '提交成功！', $diyform['redirecturl'] ? url($diyform['redirecturl']) : addon_url('cms/index/index'));
        }
        return;
    }
}
