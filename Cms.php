<?php

namespace addons\cms;

use addons\cms\library\FulltextSearch;
use addons\cms\model\Archives;
use addons\cms\model\Channel;
use app\common\library\Auth;
use app\common\library\Menu;
use fast\Tree;
use think\Addons;
use think\exception\PDOException;
use think\Request;
use think\View;

/**
 * CMS插件
 */
class Cms extends Addons
{
    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        $menu=[];
        $config_file= ADDON_PATH ."cms" . DIRECTORY_SEPARATOR .'config'. DIRECTORY_SEPARATOR . "menu.php";
        if (is_file($config_file)) {
            $menu = include $config_file;
        }
        if($menu){
            Menu::create($menu);
        }
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        Menu::delete('cms');
        return true;
    }

    /**
     * 插件启用方法
     */
    public function enable()
    {
        Menu::enable('cms');
    }

    /**
     * 插件启用方法
     * @return array
     */
    public function menu()
    {
        return Menu::getAuthRuleIdsByName('cms');
    }

    /**
     * 插件禁用方法
     */
    public function disable()
    {
        Menu::disable('cms');
    }

    /**
     * 应用初始化
     */
    public function appInit()
    {
        // 自定义路由变量规则
        \think\Route::pattern([
            'diyname' => "[a-zA-Z0-9\-_]+",
            'id'      => "\d+",
        ]);
    }

    /**
     * 脚本替换
     */
    public function viewFilter(& $content)
    {
        $request = \think\Request::instance();
        $dispatch = $request->dispatch();

        if ($request->module() || !isset($dispatch['method'][0]) || $dispatch['method'][0] != '\think\addons\Route') {
            return;
        }
        $addon = isset($dispatch['var']['addon']) ? $dispatch['var']['addon'] : $request->param('addon');
        if ($addon != 'cms') {
            return;
        }
        $style = '';
        $script = '';
        $result = preg_replace_callback("/<(script|style)\s(data\-render=\"(script|style)\")([\s\S]*?)>([\s\S]*?)<\/(script|style)>/i", function ($match) use (&$style, &$script) {
            if (isset($match[1]) && in_array($match[1], ['style', 'script'])) {
                ${$match[1]} .= str_replace($match[2], '', $match[0]);
            }
            return '';
        }, $content);
        $content = preg_replace_callback('/^\s+(\{__STYLE__\}|\{__SCRIPT__\})\s+$/m', function ($matches) use ($style, $script) {
            return $matches[1] == '{__STYLE__}' ? $style : $script;
        }, $result ? $result : $content);
    }

    /**
     * 会员中心边栏后
     * @return mixed
     * @throws \Exception
     */
    public function userSidenavAfter()
    {
        $request = Request::instance();
        $actionname = strtolower($request->action());
        $data = [
            'actionname' => $actionname
        ];
        return $this->fetch('view/hook/user_sidenav_after', $data);
    }

    public function xunsearchConfigInit()
    {
        return FulltextSearch::config();
    }

    public function xunsearchIndexReset($project)
    {
        if (!$project['isaddon'] || $project['name'] != 'cms') {
            return;
        }
        return FulltextSearch::reset();
    }

}
