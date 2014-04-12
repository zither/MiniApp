<?php

require_once 'src/MiniApp.php';
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
        $app = new \MiniApp\MiniApp(array('model_path' => './tests/data/'));
        $controller = new \MiniApp\BaseController();
        $this->assertInstanceOf('\\MiniApp\\BaseModel', $controller->model('AppTestModel'));
        $this->assertSame($controller->model('AppTestModel'), $controller->model('AppTestModel'));
    }
}
