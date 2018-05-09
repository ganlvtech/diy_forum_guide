<?php
/**
 * 自定义论坛导读
 *
 * 安装脚本
 *
 * 用于自动修改 forum.php
 *
 * upload/source/plugin/diy_forum_guide/install.php
 *
 * @author Ganlv
 */

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

$diy_forum_guide_uninstall_search_code = '@\s*//\s*?<<<<<<<<<< added by plugin diy_forum_guide[\s\S]*?//\s*?>>>>>>>>>> added by plugin diy_forum_guide.*@i';

$diy_forum_guide_install_search_code = '@if\s*?\(\s*?!\s*?defined\s*?\(\s*?\'IN_DISCUZ\'\s*?\)\s*?\)\s*?{\s*?exit\s*?\(\s*?\'Access Denied\'\s*?\)\s*?;\s*?}@i';

$diy_forum_guide_install_add_code = <<<'EOT'
// <<<<<<<<<< added by plugin diy_forum_guide 请勿删除这行和下方类似的注释，这行注释在卸载此插件用于定位代码
if (!in_array($view, ['hot', 'digest', 'new', 'my', 'newthread', 'sofa'])) {
    require_once DISCUZ_ROOT . './source/plugin/diy_forum_guide/diy_forum_guide.class.php';
    if (base_plugin_diy_forum_guide_forum::is_diy_view()) {
        return (require DISCUZ_ROOT . './source/plugin/diy_forum_guide/module/forum/forum_guide.php');
    }
}
// >>>>>>>>>> added by plugin diy_forum_guide
EOT;

$diy_forum_guide_install_forum_php_path = realpath(DISCUZ_ROOT . './source/module/forum/forum_guide.php');

try {
    $diy_forum_guide_install_forum_php_content = file_get_contents($diy_forum_guide_install_forum_php_path);

    // 判断是否已安装，如果已安装则把当前安装的代码删除
    $diy_forum_guide_install_forum_php_content = preg_replace($diy_forum_guide_uninstall_search_code, '', $diy_forum_guide_install_forum_php_content);

    if (1 === preg_match($diy_forum_guide_install_search_code, $diy_forum_guide_install_forum_php_content, $diy_forum_guide_install_matches)) {
        $diy_forum_guide_install_match_code = $diy_forum_guide_install_matches[0];
        $diy_forum_guide_install_forum_php_content = str_replace($diy_forum_guide_install_match_code, "{$diy_forum_guide_install_match_code}\r\n{$diy_forum_guide_install_add_code}", $diy_forum_guide_install_forum_php_content);
        if (is_writable($diy_forum_guide_install_forum_php_path)) {
            if (0 >= file_put_contents($diy_forum_guide_install_forum_php_path, $diy_forum_guide_install_forum_php_content)) {
                cpmsg_error("自动安装出错，请手动在 {$diy_forum_guide_install_forum_php_path} 的\n{$diy_forum_guide_install_match_code}\n语句之后添加如下代码\n{$diy_forum_guide_install_add_code}\n（这段代码也可以在本插件目录下的 install.php 中找到）");
            } else {
                // forum_guide.php 文件已修改
                $diy_forum_guide_install_record = C::t('common_plugin')->fetch_by_identifier('diy_forum_guide');
                C::t('common_pluginvar')->update_by_variable($diy_forum_guide_install_record['pluginid'], 'forum_guide_php_edited', array('value' => '1'));
            }
        } else {
            cpmsg_error("自动安装出错，{$diy_forum_guide_install_forum_php_path} 不可写，请手动在 {$diy_forum_guide_install_forum_php_path} 的\n{$diy_forum_guide_install_match_code}\n语句之后添加如下代码\n{$diy_forum_guide_install_add_code}\n（这段代码也可以在本插件目录下的 install.php 中找到）");
        }
    } else {
        $diy_forum_guide_install_match_code = <<<'EOT'
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
EOT;
        cpmsg_error("自动安装出错，请手动在 {$diy_forum_guide_install_forum_php_path} 的\n{$diy_forum_guide_install_match_code}\n语句之后添加如下代码\n{$diy_forum_guide_install_add_code}\n（这段代码也可以在本插件目录下的 install.php 中找到）");
    }
} catch (Exception $e) {
    cpmsg_error('自动安装出错，错误信息：' . $e->getMessage());
}

$finish = true;
