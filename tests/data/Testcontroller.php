<?php

namespace MiniApp\Controller;

class Testcontroller extends \MiniApp\BaseController 
{    
    public function indexGet()
    {
        echo "Hello,MiniApp!";
    }

    public function homeGet($name)
    {
        echo "Hello,$name!";
    }
}
