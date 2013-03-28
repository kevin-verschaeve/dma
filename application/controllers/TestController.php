<?php

class TestController extends Zend_Controller_Action
{
    public function indexAction()
    {
        try 
        {
        
            $layout = Zend_Layout::getMvcInstance();
            $layout->setLayout('vide');
            $tcommune = new TCommune;
            $communes = $tcommune->getCommunes();

            $lesCommunes = array();
            foreach($communes as $uneCommune)
            {
                $lesCommunes[$uneCommune['ID_COMMUNE']] = $uneCommune['NOM_COMMUNE'];
            }
            $fcommunes = new FCommune($lesCommunes);

            $this->view->fcommunes = $fcommunes;
            
            
            /* */
        }
        catch(Exception $e)
        {
            echo $e->getMessage();exit;
        }
        
    }
    public function retestAction()
    {
        $this->_helper->actionStack('header', 'index', 'default', array());
        $id = $this->getRequest()->getParam('sel_commune', false);
       
        $tcommune = new TCommune;
        $communes = $tcommune->getCommunes();

        $lesCommunes = array();
        foreach($communes as $uneCommune)
        {
            $lesCommunes[$uneCommune['ID_COMMUNE']] = $uneCommune['NOM_COMMUNE'];
        }
        $fcommunes = new FCommune($lesCommunes);

        $this->view->fcommunes = $fcommunes;
       
        if($id != false)
        {
            $tc = new TCollecte;
            $TparConteneur = $tc->getTonnageConteneur($id);
            $this->view->TparConteneur = $TparConteneur;
        }
        
    }
    
    public function piAction()
    {
        echo phpinfo();exit;
    }
}