<?php

class TestController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('vide');  
        try 
        { 
            $date = new Zend_Date();
            echo $date->toString('d MMMM YYYY');
            /*
            $matiere = 'VERRE';
            $retour = '';
            
            $sql = file_get_contents('dataCollecte.sql');
            $db = Zend_Registry::getInstance()->get("db");
            
            $db->beginTransaction();
            $statement = $db->prepare($sql);
            
            $params = array( 'matiere'=> $matiere, 'retour' => $retour );
            
            $statement->execute($params);
            $db->commit();
            
            
            echo $retour;exit;
            
            
            
            */
            
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