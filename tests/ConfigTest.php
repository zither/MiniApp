<?php

require_once 'src/MiniApp.php';

class ConfigTest extends PHPUnit_Framework_TestCase
{
    public function testConfigSet()
    {
        \MiniApp\Config::set('test', 'TEST');
        $this->assertEquals(\MiniApp\Config::get('test'), 'TEST');
    }

    public function testConfigAppend()
    {
        \MiniApp\Config::set('test', array('first'));
        \MiniApp\Config::append('test', 'second');
        $this->assertEquals(\MiniApp\Config::get('test'), array('first', 'second'));
        \MiniApp\Config::set(array('test' => 'Yes'));
        $this->assertEquals(\MiniApp\Config::get('test'), 'Yes');
        \MiniApp\Config::append('somenotdefined', '123');
        $this->assertEquals(\MiniApp\Config::get('somenotdefined'), '123');
    }
}
