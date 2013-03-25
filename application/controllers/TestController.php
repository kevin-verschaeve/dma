<?php

class TestController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('vide'); 
    }
    public function piAction()
    {
        echo phpinfo();exit;
    }
}