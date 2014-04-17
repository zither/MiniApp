<?php
/**
 * @file MiniApp.php
 * @brief Just another micro MVC framework
 * @author JonChou <ilorn.mc@gmail.com>
 * @version 0.1
 * @date 2014-04-12
 */
namespace MiniApp;

class MiniApp 
{
    public $db;
    public $template;
    public $route;
    public static $prefixes = array();
    protected static $instance = null;

    /**
     * @brief 初始化应用
     *
     * @return void
     */
    public function __construct($config = array())
    {
        if (is_array($config) && (!empty($config))) {
            Config::set($config);
        }
        date_default_timezone_set(Config::get('time_zone'));
        $this->route = new Route();
        $this->template = new Template();
        $this->db = $this->getDatabase();
        if (is_null(self::instance())) {
            self::$instance = $this;
        }
        $this->addNamespace('MiniApp\\Controller', Config::get('controller_path'));
        $this->addNamespace('MiniApp\\Model', Config::get('model_path'));
    }

    /**
     * @brief 获取数据库单例
     *
     * @return object
     */
    protected function getDatabase()
    {
        if (!$this->db instanceof \PDO) {
            $database = Config::get('database');
            $db = new Db(
                        $database['name'], 
                        $database['host'], 
                        $database['user'],
                        $database['password'],
                        $database['port']
                    );
            $this->db = $db->getDatabase();
        }
        return $this->db;
    }

    /**
     * @brief MiniApp 单例
     *
     * @return Oject or null
     */
    public static function instance()
    {
        return self::$instance !== null ? self::$instance : null;
    }

    /**
     * @brief 打印指定视图
     *
     * @param $template
     * @param $data<F11>

     *
     * @return string
     */
    public function displayView($template, $data = array())
    {
        echo $this->template->render($template, $data);
    }

    /**
     * @brief 路由重写规则，比如 $app->route('/app/123', array('app', 'index'))
     *
     * @param $url 需要重写的路由规则，支持正则表达式，比如 /app/(\d+)，捕获的组会作为参数传递
     * @param $router 包含 controller 以及 action 的数组，比如 array('app', 'index')
     *
     * @return void
     */
    public function route($url, $router)
    {
        Config::append('routes', array($url => $router));
    }

    /**
     * @brief 运行应用
     *
     * @return boolean 
     */
    public function run()
    {
        return $this->route->dispatch();
    }

    /**
     * @brief 一个基本的身份验证
     *
     * @return void
     */
    public function checkLogin()
    {
        if (!isset($_SESSION['LOGINED']) || (!$_SESSION['LOGINED'])) {
            if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
                if (
                        $_SERVER['PHP_AUTH_USER'] === Config::get('auth_user') && 
                        md5($_SERVER['PHP_AUTH_PW']) === md5(Config::get('auth_password'))
                    ) {
                    $_SESSION['LOGINED'] = true;
                } else {
                    echo "You cant login.";
                    $this->stop();
                }
            } else {
                header('WWW-Authenticate: Basic realm="App4u"');
                header('HTTP/1.1 401 Unauthorized');
                echo 'Unauthorized.';
                $this->stop();
            }
        }
        return true;
    }

    /**
     * @brief 封装一个简单的跳转函数，需要结合 return 使用
     *
     * @param $url
     * @param $responseCode
     *
     * @return redirect
     */
    public function redirect($url, $responseCode = 302)
    {
        header("Location: $url", true, $responseCode);
        $this->stop();
    }

    /**
     * @brief simple HTTP cache
     *
     * @param $second
     *
     * @return void
     */
    public function cache($second = 3600) 
    {
        header("Cache-Control: max-age=$second,must-revalidate"); 
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $lastModified = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
            $expires = $lastModified + $second;
            if ($expires > time()) {       
                header("Last-Modified: " . $_SERVER['HTTP_IF_MODIFIED_SINCE']);
                header('Expires: ' . date('r', $expires));
                header('HTTP/1.1 304 Not Modified');
                $this->stop();
            }
        }
        $currentTime = time();
        header("Last-Modified: " . date('r', $currentTime));
        header('Expires: ' . date('r', $currentTime + $second));
    }

    /**
     * @brief MiniApp 参数设置
     *
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    public function config($key, $value = null)
    {
        if (is_array($key)) {
            Config::set($key);
        } elseif (! is_null($value)) {
            Config::set($key, $value);
        } else {
            return Config::get($key);
        }
    }

    /**
     * @brief 环境模拟
     *
     * @param $settings
     *
     * @return array 
     */
    public function mock($settings = array())
    {
        return $this->route->mock($settings);
    }

    public function stop()
    {
        throw new \MiniApp\StopException();
    }

    /**
     * @brief 增加 namespace 前缀
     *
     * @param $prefix
     * @param $baseDir
     * @param $prepend
     *
     * @return void
     */
    public function addNamespace($prefix, $baseDir, $prepend = false)
    {
        $prefix = trim($prefix, '\\') . '\\';
        $baseDir = rtrim($baseDir, '/') . DIRECTORY_SEPARATOR;
        if (!isset(self::$prefixes[$prefix])) {
            self::$prefixes[$prefix] = array();
        }
        if ($prepend) {
            array_unshift(self::$prefixes[$prefix], $baseDir);
        } else {
            array_push(self::$prefixes[$prefix], $baseDir);
        }
     }

    /**
     * @brief MiniApp 的自动加载器
     *
     * @param $className
     *
     * @return void
     */
    public static function autoload($className)
    {
        $prefix = ltrim($className, '\\');
        $file = null;
        while (($pos = strrpos($prefix, '\\')) !== false) {
            $prefix = substr($className, 0, $pos + 1);
            $relativeClass = substr($className, $pos + 1);
            // 是否设置了对应的 namespace 前缀
            if (isset(self::$prefixes[$prefix])) {
                foreach (self::$prefixes[$prefix] as $baseDir) {
                    $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
                    if (file_exists($file)) {
                        return require $file;
                    }
                }
            }
            $prefix = rtrim($prefix, '\\');
        }
        return false;
    }

    /**
     * @brief 注册自动加载器
     *
     * @return void
     */
    public static function autoloadRegister()
    {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }
}

class Config 
{
    public static $container = array(
                'time_zone' => 'Asia/Shanghai',
                'routes' => array(),
                'model_path' => './model/',
                'controller_path' => './controller/',
                'view_path' => './view/',
                'auth_user' => 'user',
                'auth_password' => 'pass',
                'database' => array(
                        'host' => 'localhost',
                        'port' => '3306',
                        'name' => 'MiniApp_test',
                        'user' => 'travis',
                        'password' => ''
                    )
        );

    /**
     * @brief 获取应用设置
     *
     * @param $key 设置名称
     *
     * @return mixed
     */
    public static function get($key) 
    {
        return isset(self::$container[$key]) ? self::$container[$key] : null;
    }

    /**
     * @brief 添加应用设置
     *
     * @param $key
     * @param $value
     *
     * @return void
     */
    public static function set($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $config => $value) {
                self::$container[$config] = $value;
            }
        } else {
            self::$container[$key] = $value;
        }
    }

    /**
     * @brief 向数组类设置中追加内容
     *
     * @param $key
     * @param $value
     *
     * @return void
     */
    public static function append($key, $value)
    {
        if (isset(self::$container[$key]) && is_array(self::$container[$key])) {
            array_push(self::$container[$key], $value);
        } else {
            self::set($key, $value);
        }
    }
}

class Db 
{
    public $db;

    /**
     * @brief 初始化数据库类
     *
     * @param $db_name
     * @param $db_host
     * @param $db_user
     * @param $db_password
     * @param $db_port
     *
     * @return void
     */
    public function __construct(
                $db_name, 
                $db_host, 
                $db_user, 
                $db_password, 
                $db_port = '3306'
    )
    {
        try {
            $this->db = new \PDO(
                        "mysql:dbname=$db_name;host=$db_host;port=$db_port", 
                        $db_user, 
                        $db_password
                    );
            $this->db->exec('SET NAMES utf8');
        } catch (\PDOException $e) {
            print('Database connecting failed!');
        }
    }
    
    /**
     * @brief 获取数据库单例
     *
     * @return object
     */
    public function getDatabase()
    {
        return $this->db;
    }
}

class Route
{
    const EXT = '.php';

    public $env = array();
    public $controller = '\\MiniApp\\Controller\\Index';
    public $action = 'indexGet';
    public $actionExt = 'Get';
    public $params = null;

    /**
     * @brief 初始化环境变量
     *
     * @return void
     */
    public function __construct()
    {
        $this->env = array_merge($this->env, $_SERVER);
    }

    /**
     * @brief 路由分发
     *
     * @return response
     */
    public function dispatch()
    {
        if (isset($this->env['REQUEST_METHOD'])) {
            $this->actionExt = ucfirst(strtolower($this->env['REQUEST_METHOD']));
        }
        if (!empty($this->env['PATH_INFO']) && $this->env['PATH_INFO'] != '/') {
            // 检查是否有和当前 pathinfo 相匹配的 route
            if ( ! $this->matchRoutes($this->env['PATH_INFO'])) {
                $pathArray = explode('/', trim($this->env['PATH_INFO'], '/'));
                $this->controller = '\\MiniApp\\Controller\\' . Ucfirst(strtolower(array_shift($pathArray)));
                if (!empty($pathArray)) {
                    // 根据请求方式生成对应的 action 方法名
                    $this->action = strtolower(array_shift($pathArray)) . $this->actionExt;
                } else {
                    // 根据请求方式设置默认 action 方法
                    $this->action = 'index' . $this->actionExt; 
                }
                // 暂时只支持一个参数
                if (!empty($pathArray)) {
                    $this->params = array_shift($pathArray);
                }
            }
        }
        return $this->response();
    }

    /**
     * @brief 匹配当前路由规则
     *
     * @param $pathInfo
     *
     * @return boolean
     */
    public function matchRoutes($pathInfo)
    {
        $routes = Config::get('routes');
        if (!empty($routes)) {
            foreach ($routes as $route) {
                preg_match('#' . key($route) . '#', $pathInfo, $matches);
                if (!empty($matches)) {
                    list($controller, $action) = current($route);
                    $this->controller = '\\MiniApp\\Controller\\' . Ucfirst(strtolower($controller));
                    $this->action = strtolower($action) . $this->actionExt;
                    $params = array_slice($matches, 1);
                    $this->params = array_shift($params);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @brief 响应请求
     *
     * @return response
     */
    public function response()
    {
        try {
            $controllerReflection = new \ReflectionClass($this->controller);
            $controllerReflection->getMethod($this->action)->invoke(
                        $controllerReflection->newInstance(), 
                        $this->params
                    );  
        } catch (\ReflectionException $e) {
            $this->notFound();
        } catch (StopException $e) {
            return false;
        }
    }

    /**
     * @brief 404 not found
     *
     * @return response
     */
    public function notFound($error = '404 Not Found')
    {
        header('HTTP/1.1 404 not found');
        header('Content-Type: text/html;charset = utf-8');
        echo $error;
    }

    /**
     * @brief 环境模拟
     *
     * @param $userSettings
     *
     * @return array
     */
    public function mock($userSettings = array())
    {
        $defaults = array(
            'REQUEST_METHOD' => 'GET',
            'SCRIPT_NAME' => '',
            'PATH_INFO' => '',
            'QUERY_STRING' => '',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => 80,
            'ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'ACCEPT_LANGUAGE' => 'zh-CN;q=0.8',
            'ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.3',
            'USER_AGENT' => 'MiniApp',
            'REMOTE_ADDR' => '127.0.0.1',
        );
        $environment = array_merge($defaults, $userSettings);
        return $this->env = array_merge($this->env, $environment);
    }

}

class Template 
{
    const EXT = '.php';

    /**
     * @brief 渲染模板
     *
     * @param $template
     * @param $params
     *
     * @return string
     */
    public function render($template, $params = array())
    {
        if (file_exists($this->getPath($template))) {
            ob_start();
            extract($params, EXTR_OVERWRITE);
            include($this->getPath($template));
            return ob_get_clean();
        } else {
            throw new \Exception('View template not found!');
        }
    }

    /**
     * @brief 获取模板完整路径
     *
     * @param $template
     *
     * @return string
     */
    public function getPath($template)
    {
        return Config::get('view_path') . $template . self::EXT;
    }
}

class BaseController 
{
    public static $models = array();

    /**
     * @brief 获取 MiniApp 实例
     *
     * @return Object
     */
    public function app()
    {
        return MiniApp::instance();
    }

    /**
     * @brief 获取 $model 相对应的模型实例
     *
     * @param $model
     *
     * @return Object
     */
    public function model($model)
    {
        if (array_key_exists($model, self::$models)) {
            return self::$models[$model];
        } else {
            $modelName = "\\MiniApp\\Model\\{$model}";
            return self::$models[$model] = new $modelName;
        }
    }
}

class BaseModel 
{
    public $db = null;

    public function __construct()
    {
        $app = MiniApp::instance();
        $this->db = $app->db;
    }
}

class StopException extends \Exception {}
