<?php

class TestController extends Zend_Controller_Action
{
    public function indexAction()
    {
        try {
            exec(APPLICATION_PATH.'/configs/Loader.bat');
            
        }catch(Exception $e)
        {
            echo $e->getMessage();
        }
        exit;
    }
    public function piAction()
    {
        echo phpinfo();exit;
    }
}