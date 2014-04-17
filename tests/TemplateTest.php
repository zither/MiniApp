<?php

require_once 'Bootstrap.php';

class TemplateTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        \MiniApp\Config::set('view_path', __DIR__ . '/data/');
    }

    public function testGetPath()
    {
        $template  = new \MiniApp\Template();
        $this->assertEquals($template->getPath('template'), __DIR__ . '/data/template.php');
    }
    
    /**
     * @expectedException \Exception
     */
    public function testRender()
    {
        $template = new \MiniApp\Template();
        $this->assertEquals($template->render('template'), "<p>Hello</p>\n");
        $world = 'world';
        $this->assertEquals(
                    $template->render('template_with_vars', array('world' => 'world')), 
                    "<p>Hello,world</p>\n"
                );
        $template->render('somenotdefined'); 
    }
}
