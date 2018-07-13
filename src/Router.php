<?php

namespace jun3\router;

/**
 * @uses     Router
 * @package  jun\router
 * @version  2018年07月13日
 * @author   Jun <zhoujun@kzl.com.cn>
 * @license  PHP Version 7.1.x {@link [图片]http://www.php.net/license/3_0.txt}
 */
class Router
{
    private static $config = [];
    private static $_obj = null;
    private static $_ds = null;
    private $_controller_path = null;
    protected $app_name = 'app';
    protected $module = '';
    protected $controller = 'index';
    protected $action = 'index';
    protected $errorController = 'error';
    protected $errorAction = 'index';

    public function __construct(array $config = [])
    {
        // 系统分隔符
        if (is_null(self::$_ds)) {
            self::$_ds = DIRECTORY_SEPARATOR;
        }

        // 加载配置文件内容
        $configFile = require_once __DIR__ . self::$_ds . 'config.php';

        // 载入配置
        self::$config = $config = array_merge($configFile, $config);

        // 默认控制器
        if (isset($config['default_controller'])) {
            $this->controller = $config['default_controller'];
        }

        // 默认action
        if (isset($config['default_action'])) {
            $this->action = $config['default_action'];
        }

        if (is_null($this->_controller_path)) {
            // 项目默认名称
            $this->app_name = 'app';

            // 配置设置项目名称
            if (isset($config['app'])) {
                $this->app_name = $config['app'];
            }

            // 常量设置项目名称
            if (defined('APP_NAME')) {
                $this->app_name = APP_NAME;
            }

            // 项目根目录
            $path = dirname($_SERVER['DOCUMENT_ROOT']);

            // 设置控制器目录
            $this->_controller_path = $path . self::$_ds . $this->app_name . self::$_ds . 'controllers' . self::$_ds;
        }
    }

    /**
     * 设置控制器 && 绑定参数
     *
     * @param array $pathArr
     */
    private function setPath(array $pathArr = [])
    {
        foreach ($pathArr as $key => $item) {
            $result = self::moduleExist($item);
            if ($result == false) {
                break;
            }
            unset($pathArr[$key]);
        }

        // 重置开始位置
        $pathArr = array_values($pathArr);

        // 设置控制器
        if (isset($pathArr[0]) && !empty($pathArr[0])) {
            $this->controller = $pathArr[0];
            unset($pathArr[0]);
        }

        // 设置action
        if (isset($pathArr[1]) && !empty($pathArr[1])) {
            $this->action = $pathArr[1];
            unset($pathArr[1]);
        }

        $pathArr = array_values($pathArr);          // 获取参数

        $i = 0;                                     // 设置开始值
        while ($i < count($pathArr)) {
            $n = $i + 1;                            // 值得key
            if (!isset($pathArr[$n])) {             // 参数值不存在直接结束
                break;
            }
            $key        = $pathArr[$i];             // 获取参数key
            $value      = $pathArr[$n];             // 获取参数值
            $_GET[$key] = $_REQUEST[$key] = $value; // 设置参数
            $i          += 2;
        }
    }

    /**
     * 检测module是否存在
     *
     * @param string $moduleName
     *
     * @return bool
     */
    public function moduleExist(string $moduleName = ''): bool
    {
        $modulePath = $this->_controller_path . $moduleName;
        if (empty($moduleName)) {
            return false;
        }

        if (is_dir($modulePath)) {
            $this->module           .= $moduleName . self::$_ds;
            $this->_controller_path = $modulePath . self::$_ds;

            return true;
        }

        return false;
    }

    /**
     * 开始运行控制器
     *
     * @return mixed
     * @throws \Exception
     */
    public function runAction()
    {
        //
        $defaultControllerName = $controlName = ucfirst(strtolower($this->controller));
        // 设置控制器
        $defaultControllerPath = $controlPath = self::$_ds . $this->app_name . self::$_ds . 'controllers' . self::$_ds . $this->module;
        $controlPath           = str_replace(self::$_ds, '\\', $controlPath);

        // 设置错误控制器
        if (!class_exists($controlPath . $controlName)) {
            $controlName = $this->errorController;
        }

        // 抛出找不到控制错误异常
        if (!class_exists($controlPath . $controlName)) {
            $controlPath = '\\jun3\\router\\';
            $controlName = 'Error';
        }

        // 转义
        $catl       = $controlPath . $controlName;
        $controller = new $catl();
        $oldAction  = $action = strtolower($this->action);

        // 设置错误方法
        if (!method_exists($controller, $action)) {
            $action = strtolower($this->errorAction);
        }

        // 抛出方法不存在错误
        if (!method_exists($controller, $action)) {
            throw new \Exception($oldAction . " 方法不存在");
        }

        // 开始清理输出缓存
        ob_clean();
        $params = $_GET;
        if ($controlName == 'Error') {
            $params = [$defaultControllerName, $defaultControllerPath];
        }

        $result = call_user_func_array([$controller, $action], $params);

        return $result;
    }

    /**
     * 初始化路由
     *
     * @param array $config
     *
     * @return mixed
     * @throws \Exception
     */
    public static function run(array $config = [])
    {
        if (is_null(self::$_obj)) {
            self::$_obj = new self($config);
        }

        $uriPath   = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : $_SERVER['PATH_INFO'];
        $uri       = trim($uriPath, '/');
        $uri       = explode('?', $uri);                    //
        $routerArr = explode('/', $uri[0]);
        self::$_obj->setPath($routerArr);

        // 移除多余的请求参数
        if (isset($_GET[$uriPath])) {
            unset($_GET[$uriPath]);
            unset($_REQUEST[$uriPath]);
        }

        // 返回路由对象
        return self::$_obj;

        // 开始执行
        //        return self::$_obj->runAction();
    }
}