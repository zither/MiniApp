<?php

require_once 'src/MiniApp.php';
\MiniApp\MiniApp::autoloadRegister();

class DbTest extends PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {
        $this->expectOutputString('Database connecting failed!');
        $db = new \MiniApp\Db('localhost', 'sdf', 'sdf', 'sdf');
    }
}
