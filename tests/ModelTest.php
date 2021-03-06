<?php

require_once 'Bootstrap.php';
\MiniApp\MiniApp::autoloadRegister();

class ModelTest extends PHPUnit_Framework_TestCase
{
    public function testDb()
    {
        $app = new \MiniApp\MiniApp(array('model_path' => __DIR__ . '/data/'));
        $BaseModel = new \MiniApp\BaseModel();
        $this->assertInstanceOf('\\PDO', $BaseModel->db);
        $model = new \MiniApp\Model\AppTestModel();
        $this->assertInstanceOf('\\PDO', $model->db);
    }
}
