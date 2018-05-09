<?php
if (php_sapi_name() !== 'cli') {
    exit;
}
$vars = [
    [
        'title' => '标题',
        'description' => '支持 [color][b][s][i][u]',
        'variable' => 'title',
        'type' => 'text',
    ],
    [
        'title' => '描述',
        'description' => '类似于版块描述、版规等，支持 [color][b][s][i][u] 基础代码以及 [size][url][img] 等高级 discuz 代码',
        'variable' => 'description',
        'type' => 'textarea',
    ],
    [
        'title' => ' URL 标签',
        'variable' => 'permalink',
        'type' => 'text',
    ],
    [
        'title' => '板块',
        'variable' => 'forums',
        'type' => 'forums',
    ],
    [
        'title' => '仅热门',
        'variable' => 'hot',
        'type' => 'radio',
        'value' => '0',
    ],
    [
        'title' => '仅精华',
        'variable' => 'digest',
        'type' => 'radio',
        'value' => '0',
    ],
    [
        'title' => '排序规则',
        'variable' => 'orderby',
        'type' => 'select',
        'value' => 'newthread',
        'extra' => "newthread=最新主题\nreply=最新回复\nlastpost=最新发表",
    ],
    [
        'title' => '数量限制',
        'variable' => 'limit',
        'type' => 'text',
        'value' => '600',
    ],
    [
        'title' => '时间限制',
        'description' => '单位是秒，一天为86400，一周是604800，一个月是2592000，一年是31536000',
        'variable' => 'dt',
        'type' => 'text',
        'value' => '604800',
    ],
    [
        'title' => '缓存更新间隔',
        'description' => '单位是秒，一天为86400，一周是604800，一个月是2592000，一年是31536000',
        'variable' => 'cachetimelimit',
        'type' => 'text',
        'value' => '900',
    ],
];
ob_start();
?>
    <item id="var">
        <item id="0">
            <item id="displayorder"><![CDATA[0]]></item>
            <item id="title"><![CDATA[是否已改 forum_guide.php]]></item>
            <item id="description"><![CDATA[为了更好的兼容性，您最好按照本插件目录下的 install.php 中的几行代码添加到 ./source/module/forum/forum_guide.php 中 if (!defined('IN_DISCUZ')) { exit('Access Denied'); } 之后。如果您的服务器允许写入文件的话，这个操作在安装时会在安装插件时自动进行，这个设置会自动设置为“是”，如果在安装时出现了错误提示，您必须手动修改文件，然后手动修改这个设置。当然，如果不修改文件或不修改这个设置的话，插件仍然可以运行，只是有可能出现未知的兼容性问题。]]></item>
            <item id="variable"><![CDATA[forum_guide_php_edited]]></item>
            <item id="type"><![CDATA[radio]]></item>
            <item id="value"><![CDATA[]]></item>
            <item id="extra"><![CDATA[]]></item>
        </item>
        <?php
        $id = 1;
        for ($i = 0; $i < 4; ++$i) {
            foreach ($vars as $var) {
                ?>
                <item id="<?php echo $id; ?>">
                    <item id="displayorder"><![CDATA[<?php echo $id; ?>]]></item>
                    <item id="title"><![CDATA[导读<?php echo $i + 1; ?><?php echo $var['title']; ?>]]></item>
                    <item id="description"><![CDATA[<?php if (isset($var['description'])) echo $var['description']; ?>]]></item>
                    <item id="variable"><![CDATA[view_<?php echo $i; ?>_<?php echo $var['variable']; ?>]]></item>
                    <item id="type"><![CDATA[<?php echo $var['type']; ?>]]></item>
                    <item id="value"><![CDATA[<?php if (isset($var['value'])) echo $var['value']; ?>]]></item>
                    <item id="extra"><![CDATA[<?php if (isset($var['extra'])) echo $var['extra']; ?>]]></item>
                </item>
                <?php
                ++$id;
            }
        }
        ?>
    </item>
<?php
$output = ob_get_clean();
file_put_contents('var.xml', $output);
