<?php

require_once 'src/MiniApp.php';
\MiniApp\MiniApp::autoloadRegister();

class RouteTest extends PHPUnit_Framework_TestCase
{
    public function testDispatch()
    {
        $this->expectOutputString('Hello,MiniApp!');
        $app = new \MiniApp\MiniApp(array('controller_path' => './tests/data/'));
        $app->route('/testcontroller/(MiniApp)', array('testcontroller', 'home'));
        $app->mock(array('PATH_INFO' => '/testcontroller/MiniApp'));
        $app->route->dispatch();
    }

    public function testDispatchWithNoAction()
    {
        $this->expectOutputString('Hello,MiniApp!');
        $app = new \MiniApp\MiniApp(array('controller_path' => './tests/data/'));
        $app->mock(array('PATH_INFO' => '/testcontroller'));
        $app->route->dispatch();
    }
}
