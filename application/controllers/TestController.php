<?php

class TestController extends Zend_Controller_Action
{
    public function indexAction()
    {
        try {
        $tco = new TCollecte;
        Zend_Debug::dump($tco->testDate());
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