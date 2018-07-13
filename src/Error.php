<?php


namespace jun3\router;


/**
 * @uses     Error
 * @version  2018年07月13日
 * @author   Jun <zhoujun@kzl.com.cn>
 * @license  PHP Version 7.1.x {@link [图片]http://www.php.net/license/3_0.txt}
 */

class Error
{
    public function index($name = '', $path = '')
    {
        echo "没有找到你的控制器：" . $name, ", 路径：" . $path;
    }
}