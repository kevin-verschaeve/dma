<?php

class TestController extends Zend_Controller_Action
{
    public function indexAction()
    {
        try {     
            exit;
            
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