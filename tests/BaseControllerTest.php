<?php

require_once 'Bootstrap.php';
\MiniApp\MiniApp::autoloadRegister();

class BaseControllerTest extends PHPUnit_Framework_TestCase
{
    public function testApp()
    {
        $controller = new \MiniApp\BaseController();
        $this->assertEquals(null, $controller->app());
        $app = new \MiniApp\MiniApp();
        $this->assertInstanceOf('\\MiniApp\\MiniApp', $controller->app());
    }

    public function testModel()
    {
        $app = new \MiniApp\MiniApp(array('model_path' => __DIR__ . '/data/'));
        $controller = new \MiniApp\BaseController();
        $this->assertInstanceOf('\\MiniApp\\BaseModel', $controller->model('AppTestModel'));
        $this->assertSame($controller->model('AppTestModel'), $controller->model('AppTestModel'));
    }

    /**
     * @runInSeparateProcess
     * @expectedException \MiniApp\StopException 
     */
    public function testRedirect()
    {
        $app = new \MiniApp\MiniApp();
        $controller = new \MiniApp\BaseController();
        $controller->redirect('/about', 301);
    }

    public function testDisplay()
    {
        $this->expectOutputString("<p>Hello</p>\n");
        $app = new \MiniApp\MiniApp(array('view_path' => __DIR__ . '/data/'));
        $controller = new \MiniApp\BaseController();
        $controller->display('template');
    }
}
