<?php

class TestController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('vide');  
        try {
           $tdata = new TCollecte;
           
           echo $tdata->getLignes(1, 2013);
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
        }
        
    }
    public function testAction()
    {
        try 
        {
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
        }
    }
    
    public function piAction()
    {
        echo phpinfo();exit;
    }
}