<?php
/**
 * 自定义论坛导读
 *
 * 卸载脚本
 *
 * 用于自动复原 forum.php
 *
 * upload/source/plugin/diy_forum_guide/uninstall.php
 *
 * @author Ganlv
 */


if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

$diy_forum_guide_uninstall_search_code = '@\s*//\s*?<<<<<<<<<< added by plugin diy_forum_guide[\s\S]*?//\s*?>>>>>>>>>> added by plugin diy_forum_guide@i';

$diy_forum_guide_uninstall_forum_php_path = realpath(DISCUZ_ROOT . './source/module/forum/forum_guide.php');

try {
    $diy_forum_guide_uninstall_forum_php_content = file_get_contents($diy_forum_guide_uninstall_forum_php_path);
    if (1 === preg_match($diy_forum_guide_uninstall_search_code, $diy_forum_guide_uninstall_forum_php_content, $diy_forum_guide_uninstall_matches)) {
        $diy_forum_guide_uninstall_match_code = $diy_forum_guide_uninstall_matches[0];
        $diy_forum_guide_uninstall_forum_php_content = str_replace($diy_forum_guide_uninstall_match_code, '', $diy_forum_guide_uninstall_forum_php_content);
        if (is_writable($diy_forum_guide_uninstall_forum_php_path)) {
            if (0 >= file_put_contents($diy_forum_guide_uninstall_forum_php_path, $diy_forum_guide_uninstall_forum_php_content)) {
                cpmsg_error("自动卸载出错，请手动删除 {$diy_forum_guide_uninstall_forum_php_path} 中的下列代码\n{$diy_forum_guide_uninstall_match_code}");
            }
        } else {
            cpmsg_error("自动卸载出错，{$diy_forum_guide_uninstall_forum_php_path} 不可写，请手动删除 {$diy_forum_guide_uninstall_forum_php_path} 中的下列代码\n{$diy_forum_guide_uninstall_match_code}");
        }
    }
} catch (Exception $e) {
    cpmsg_error('自动卸载出错，错误信息：' . $e->getMessage());
}

$finish = true;
