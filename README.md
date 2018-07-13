# 一个简单的PHP路由

### 使用方式
    
    // 加载
    include_once __DIR__ . '/../vendor/autoload.php';
    
    // 实例化
    $app = \jun3\router\Router::run($config = []);
    
    // 开始执行控制器
    $app->runAction();


### 默认配置
    $config = array(
        'app_name'           => '',         // 项目名称
        'module'             => '',         // 默认模块名称
        'default_controller' => 'index',    // 默认控制器
        'default_action'     => 'index',    // 默认方法
        'error_controller'   => 'error',    // 默认错误控制器
        'error_action'       => 'index',    // 默认错误方法
    };