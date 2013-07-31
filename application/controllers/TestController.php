<?php

class TestController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('vide');  
        try {
           $c = $i = 0;
           $h = fopen('Avril2.csv', 'r');
           if($h) {
               while($l = fgetcsv($h, 0, ';')) {
                   //var_dump($l);
                   $i++;
                   $c += str_replace(',', '.', $l[10]);
                   echo $l[10].' + ';
               }
               echo '<br>'.$c;
           }
           else {
               echo 'pas ouvert';
           }
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