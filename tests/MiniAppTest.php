<?php

require_once 'src/MiniApp.php';

class MiniAppTest extends PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $app = new \MiniApp\MiniApp();
        $this->assertInstanceOf('\\MiniApp\\MiniApp', \MiniApp\MiniApp::instance());
        $this->assertSame(\MiniApp\MiniApp::instance(), \MiniApp\MiniApp::instance());
    }

    public function testConstruct()
    {
        $app = new \MiniApp\MiniApp();
        $this->assertInstanceOf('\\MiniApp\\Template', $app->template);
        $this->assertInstanceOf('\\MiniApp\\Route', $app->route);
        $this->assertInstanceOf('\\PDO', $app->db);
    }

    public function testRoute()
    {
        $app = new \MiniApp\MiniApp();
        $app->route('test/123', array('user', 'say'));
        $this->assertEquals(\MiniApp\Config::get('routes'), array(
                        array('test/123' => array(
                                'user', 
                                'say'
                            )))
                );
    }

    public function testAddNamespace()
    {
        $app = new \MiniApp\MiniApp();
        $app->addNamespace('MiniApp', 'vendor/MiniApp/lib');
        $this->assertArrayHasKey('MiniApp\\', \MiniApp\MiniApp::$prefixes);
        $this->assertEquals(\MiniApp\MiniApp::$prefixes['MiniApp\\'], array('vendor/MiniApp/lib/'));
        $app->addNamespace('Test', 'vendor/test/sub');
        $app->addNamespace('Test', 'vendor/test', true);
        $this->assertEquals(array_shift(\MiniApp\MiniApp::$prefixes['Test\\']), 'vendor/test/');
    }

    public function testConfig()
    {
        $config = array('test' => 'Yes');
        $app = new \MiniApp\MiniApp($config);
        $this->assertEquals($app->config('test'), 'Yes');
        $app->config('test', 'No');
        $this->assertEquals($app->config('test'), 'No');
        $app->config(array('array_config' => true));
        $this->assertTrue($app->config('array_config'));
        $this->assertEquals($app->config('something_not_defined'), null);
    }

    public function testDisplayVeiw()
    {
        $this->expectOutputString("<p>Hello</p>\n");
        $app = new \MiniApp\MiniApp();
        $app->config('view_path', './tests/data/');
        $app->displayView('template');
    }

    public function testMock()
    {
        $env = array('REQUEST_METHOD' => 'PUT','PATH_INFO' => '/testcontroller/index');
        $app = new \MiniApp\MiniApp();
        $app->mock($env);
        $this->assertEquals('PUT', $app->route->env['REQUEST_METHOD']);
        $this->assertEquals('/testcontroller/index', $app->route->env['PATH_INFO']);
    }

    public function testRun()
    {
        $this->expectOutputString('Hello,MiniApp!');
        \MiniApp\MiniApp::autoloadRegister();
        $env = array('REQUEST_METHOD' => 'GET','PATH_INFO' => '/testcontroller/home/MiniApp');
        $app = new \MiniApp\MiniApp(array('controller_path'=> './tests/data/'));
        $app->mock($env);
        $app->run();
    }

    public function testAutoload()
    {
        $bool = \MiniApp\MiniApp::autoload('Class\\Not\\Defined');
        $this->assertTrue(!$bool);
    }
}
