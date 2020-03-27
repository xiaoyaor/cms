<?php

namespace addons\cms\library;

use addons\cms\library\aip\AipContentCensor;
use addons\cms\library\aip\AipNlp;
use fast\Http;
use think\Hook;

class Service
{

    /**
     * 检测内容是否合法
     * @param $content
     * @return bool
     */
    public static function isContentLegal($content)
    {
        $config = get_addon_config('cms');
        if ($config['audittype'] == 'local') {
            // 敏感词过滤
            $handle = SensitiveHelper::init()->setTreeByFile(ADDON_PATH . 'cms/data/words.dic');
            //首先检测是否合法
            $isLegal = $handle->islegal($content);
            return $isLegal ? true : false;
        } else {
            $client = new AipContentCensor($config['aip_appid'], $config['aip_apikey'], $config['aip_secretkey']);
            $result = $client->antiSpam($content);
            if (isset($result['result']) && $result['result']['spam'] > 0) {
                return false;
            }
        }
        return true;
    }

    /**
     * 获取标题的关键字
     * @param $title
     * @return array
     */
    public static function getContentTags($title)
    {
        $arr = [];
        $config = get_addon_config('cms');
        if ($config['nlptype'] == 'local') {
            !defined('_VIC_WORD_DICT_PATH_') && define('_VIC_WORD_DICT_PATH_', ADDON_PATH . 'cms/data/dict.json');
            $handle = new VicWord('json');
            $result = $handle->getAutoWord($title);
            foreach ($result as $index => $item) {
                $arr[] = $item[0];
            }
        } else {
            $client = new AipNlp($config['aip_appid'], $config['aip_apikey'], $config['aip_secretkey']);
            $result = $client->lexer($title);
            if (isset($result['items'])) {
                foreach ($result['items'] as $index => $item) {
                    if (!in_array($item['pos'], ['v', 'vd', 'nd', 'a', 'ad', 'an', 'd', 'm', 'q', 'r', 'p', 'c', 'u', 'xc', 'w'])) {
                        $arr[] = $item['item'];
                    }
                }
            }
        }
        foreach ($arr as $index => $item) {
            if (mb_strlen($item) == 1) {
                unset($arr[$index]);
            }
        }
        return array_filter($arr);
    }

    /**
     * 内容关键字自动加链接
     */
    public static function autolinks($value)
    {
        $links = [];

        $value = preg_replace_callback('~(<a .*?>.*?</a>|<.*?>)~i', function ($match) use (&$links) {
            return '<' . array_push($links, $match[1]) . '>';
        }, $value);

        $config = get_addon_config('cms');
        $autolinks = $config['autolinks'];
        $value = preg_replace_callback('/(' . implode('|', array_keys($autolinks)) . ')/i', function ($match) use ($autolinks) {
            if (!isset($autolinks[$match[1]])) {
                return $match[0];
            } else {
                return '<a href="' . $autolinks[$match[1]] . '" target="_blank">' . $match[0] . '</a>';
            }
        }, $value);
        return preg_replace_callback('/<(\d+)>/', function ($match) use (&$links) {
            return $links[$match[1] - 1];
        }, $value);
    }

    /**
     * 推送消息通知
     * @param string $content     内容
     * @param string $type        类型
     * @param string $template_id 模板ID
     */
    public static function notice($content, $type, $template_id)
    {
        try {
            if ($type == 'dinghorn') {
                Hook::listen('msg_notice', $template_id, [
                    'content' => $content
                ]);
            } elseif ($type == 'vbot') {
                Hook::listen('vbot_send_msg', $template_id, [
                    'content' => $content
                ]);
            }
        } catch (\Exception $e) {

        }
    }

}