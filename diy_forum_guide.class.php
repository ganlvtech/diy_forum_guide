<?php
/**
 * 自定义论坛导读
 *
 * 插件核心 Hook 文件
 *
 * upload/source/plugin/diy_forum_guide/diy_forum_guide.class.php
 *
 * @author Ganlv
 */

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class base_plugin_diy_forum_guide_forum
{
    public function __construct()
    {
        self::load_config();
    }

    private static function load_config()
    {
        if (!self::is_mod_guide()) {
            return;
        }
        global $_G;
        if (isset($_G['cache']['plugin']['diy_forum_guide']['views'])) {
            return;
        }
        loadcache('plugin');
        $config =& $_G['cache']['plugin']['diy_forum_guide'];
        $views = [];
        for ($i = 0; ; ++$i) {
            $prefix = 'view_' . $i . '_';
            if (!isset($config[$prefix . 'permalink'])) {
                break;
            }
            if (empty($config[$prefix . 'permalink']) || empty($config[$prefix . 'title']) || empty($config[$prefix . 'forums'])) {
                continue;
            }
            $forums = unserialize($config[$prefix . 'forums']);
            if (empty($forums)) {
                continue;
            }
            $permalink = $config[$prefix . 'permalink'];
            $title = $config[$prefix . 'title'];
            safefilter($title);
            $data = [];
            if ($permalink == $_GET['view']) {
                $description = $config[$prefix . 'description'];
                if ($description) {
                    require_once libfile('function/discuzcode');
                    $description = discuzcode(dhtmlspecialchars(censor(trim($config[$prefix . 'description']))), 0, 0, 0, 0, 1, 1, 0, 0, 1);
                }
                $hot = (bool)$config[$prefix . 'hot'];
                $digest = (bool)$config[$prefix . 'digest'];
                $limit = (int)$config[$prefix . 'limit'];
                $dt = (int)$config[$prefix . 'dt'];
                $cachetimelimit = (int)$config[$prefix . 'cachetimelimit'];
                $orderby = $config[$prefix . 'orderby'];
                if (!in_array($orderby, ['newthread', 'reply', 'lastpost'])) {
                    $orderby = 'newthread';
                }
                $data = compact('description', 'forums', 'hot', 'digest', 'orderby', 'limit', 'dt', 'cachetimelimit');
            }
            $views[$permalink] = compact('title', 'data');
        }
        $_G['cache']['plugin']['diy_forum_guide']['views'] =& $views;
    }

    public static function guide_nav_extra()
    {
        if (self::is_mod_guide() && !self::is_system_view() && self::is_diy_view()) {
            global $_G;
            if (!$_G['cache']['plugin']['diy_forum_guide']['forum_guide_php_edited']) {
                global $mod, $navtitle;
                $navtitle = str_replace('{bbname}', $_G['setting']['bbname'], $_G['setting']['seotitle']['forum']);
                $_G['setting']['threadhidethreshold'] = 1;
                require __DIR__ . '/module/forum/forum_guide.php';
                exit;
            }
        }
        return '';
    }

    public static function guide_nav_extra_output()
    {
        if (self::is_mod_guide()) {
            $output = '';
            global $_G;
            $view = $_GET['view'];
            $views =& $_G['cache']['plugin']['diy_forum_guide']['views'];
            foreach ($views as $permalink => &$item) {
                $li_class = ($permalink == $view) ? ' class="xw1 a"' : '';
                $li_a_href_view = dhtmlspecialchars(durlencode($permalink));
                $output .= "<li{$li_class}><a href=\"forum.php?mod=guide&amp;view={$li_a_href_view}\">{$item['title']}</a></li>";
            }
            return $output;
        }
        return '';
    }

    public static function is_diy_view()
    {
        if (self::is_mod_guide()) {
            self::load_config();
            global $_G;
            return isset($_G['cache']['plugin']['diy_forum_guide']['views'][$_GET['view']]);
        }
        return false;
    }

    public static function is_system_view()
    {
        return in_array($_GET['view'], ['hot', 'digest', 'new', 'my', 'newthread', 'sofa']);
    }

    public static function is_mod_guide()
    {
        return $_GET['mod'] === 'guide';
    }

    public static function forum_thread_fetch_all_for_guide($type, $limittid, $tids = [], $heatslimit = 3, $dateline = 0, $start = 0, $limit = 600, $fids = 0)
    {
        global $_G;
        $view_data = $_G['cache']['plugin']['diy_forum_guide']['views'][$type]['data'];
        $addsql = '';
        if ($view_data['hot']) {
            $addsql .= ' AND heats>=' . intval($heatslimit);
        }
        if ($view_data['digest']) {
            $addsql .= ' AND digest>0';
        }
        if (getglobal('setting/followforumid')) {
            $addsql .= ' AND ' . DB::field('fid', getglobal('setting/followforumid'), '<>');
        }
        if ($tids) {
            $tids = dintval($tids, true);
            $tidsql = DB::field('tid', $tids);
        } else {
            if ($view_data['forums']) {
                $fids = $view_data['forums'];
            }
            if ($view_data['limit']) {
                $limit = $view_data['limit'];
            }
            if ($view_data['dt']) {
                $dateline = TIMESTAMP - $view_data['dt'];
            }

            $limittid = intval($limittid);
            $tidsql = 'tid>' . $limittid;
            $fids = dintval($fids, true);
            if ($fids) {
                $tidsql .= is_array($fids) && $fids ? ' AND fid IN(' . dimplode($fids) . ')' : ' AND fid=' . $fids;
            }
            if ($dateline) {
                $addsql .= ' AND dateline > ' . intval($dateline);
            }
            if ($view_data['orderby'] == 'newthread') {
                $orderby = 'tid';
            } elseif ($view_data['orderby'] == 'reply') {
                $orderby = 'lastpost';
                $addsql .= ' AND replies > 0';
            } else {
                $orderby = 'lastpost';
            }
            $addsql .= ' AND displayorder>=0 ORDER BY ' . $orderby . ' DESC ' . DB::limit($start, $limit);
        }
        return DB::fetch_all("SELECT * FROM " . DB::table('forum_thread') . " WHERE " . $tidsql . $addsql);
    }

    public static function get_view_cache_time_limit($default = 900)
    {
        if (self::is_mod_guide()) {
            if (!self::is_system_view() && self::is_diy_view()) {
                global $_G;
                return $_G['cache']['plugin']['diy_forum_guide']['views'][$_GET['view']]['data']['cachetimelimit'];
            }
        }
        return $default;
    }
}

class plugin_diy_forum_guide
{
}

class plugin_diy_forum_guide_forum extends base_plugin_diy_forum_guide_forum
{
}

class mobileplugin_diy_forum_guide extends base_plugin_diy_forum_guide_forum
{
    public static function global_header_mobile()
    {
        self::guide_nav_extra();
        return '';
    }
}
