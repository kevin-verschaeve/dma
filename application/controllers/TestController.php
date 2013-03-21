<?php

class TestController extends Zend_Controller_Action
{
    public function indexAction()
    {
        try {          
            $date = new Zend_Date(Zend_Date::now());
            $annee = $date->get('yyyy');
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