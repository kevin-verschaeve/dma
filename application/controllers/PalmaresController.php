<?php

class PalmaresController  extends Zend_Controller_Action
{
    public function indexAction()
    {
        $this->_helper->actionStack('header', 'index', 'default', array());
        
        // instancie un nouveau formulaire de choix de matiere et l'envoi a la vue
        $fmatiere = new FMatiere;
        $this->view->formMatiere = $fmatiere;
        //Zend_Debug::dump($infosSite);
        
        $this->_helper->actionStack('infos', 'palmares');        
        
    }
    public function infosAction()
    {
        try {
        // recupere le bouton radio selectionné
        $matiere = $this->getRequest()->getParam('radio', 'Verre Couleur');
        $ajax = $this->getRequest()->getParam('ajax', false);

        if($ajax)
        {
            // change le layout, pour ne pas recuperer les balises body, html...
            // seulement si on vient en ajax, car sinon on a besoin des balises
            $layout = Zend_Layout::getMvcInstance();
            $layout->setLayout('vide'); 
        }

        // instancie les tables que l'on aura besoin
        $tcollecte = new TCollecte;

        $infosConteneur = $tcollecte->getInfos(null ,$matiere);
        //Zend_Debug::dump($infosConteneur);exit;
        // on envoi les variables a la vue
        $this->view->infosConteneur = $infosConteneur;
        $this->view->ajax = $ajax;
        }catch(Exception $e)
        {
            echo $e->getMessage();exit;
        }
    }
    public function infosperiodeAction()
    {
        try {
        $this->_helper->actionStack('header', 'index', 'default', array());
        
        // instancie un nouveau formulaire de choix de matiere et l'envoi a la vue
        $fmatiere = new FMatiere(true);
        //Zend_Debug::dump($infosSite);
        $request = $this->getRequest();
        
        if($request->isPost())
        {
            if($fmatiere->isValid($_POST))
            {
                $matiere = $this->getRequest()->getParam('radioMatiere', 'Verre Couleur');
                $dateDebut = $request->getParam('dateDebut', null);
                $dateFin = $request->getParam('dateFin', null);
                
                $tcollecte = new TCollecte;
                $infosConteneur = $tcollecte->getInfos(null ,$matiere, $dateDebut, $dateFin);
                
                $this->view->infosConteneur = $infosConteneur;
                $this->view->send = true;
            }
        }
        }
        catch(Exception $e)
        {
            echo $e->getMessage();exit;
        }
        $this->view->formMatiere = $fmatiere;
    }
}

