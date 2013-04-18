<?php

class PalmaresController  extends Zend_Controller_Action
{
    public function indexAction()
    {
        $this->_helper->actionStack('header', 'index', 'default', array());
        
        // instancie un nouveau formulaire de choix de matiere et l'envoi a la vue
        $fmatiere = new FMatiere(false, true);
        $this->view->formMatiere = $fmatiere;
        //Zend_Debug::dump($infosSite);
        
        // appelle l'action en dessous
        $this->_helper->actionStack('infos', 'palmares'); 
    }
    /**
     * A pour but de recuperer les informations pour tous les conteneurs
     */
    public function infosAction()
    {
        // recupere le bouton radio selectionné
        $matiere = $this->getRequest()->getParam('radio', 'VERRE');
        $ajax = $this->getRequest()->getParam('ajax', false);
        $creerPdf = $this->getRequest()->getParam('creerPdf', false);

        if($ajax || $creerPdf)
        {
            // change le layout, pour ne pas recuperer les balises body, html...
            // seulement si on vient en ajax, car sinon on a besoin des balises
            $layout = Zend_Layout::getMvcInstance();
            $layout->setLayout('vide'); 
        }

        $tsite = new TSite;
        $this->view->infosSite = $tsite->getInfos(null, $matiere);
        
        // on envoi les variables a la vue
        $this->view->matiere = $matiere;
        $this->view->ajax = $ajax;
        $this->view->creerPdf = $creerPdf;
    }
    public function infosperiodeAction()
    {
        $this->_helper->actionStack('header', 'index', 'default', array());
        $matiere = $this->getRequest()->getParam('radioMatiere', 'VERRE');
        $creerPdf = $this->getRequest()->getParam('creerPdf', false);
        
        // instancie un nouveau formulaire de choix de matiere et l'envoi a la vue
        $fmatiere = new FMatiere(true);
        //Zend_Debug::dump($infosSite);
        $request = $this->getRequest();
        
        if($request->isPost())
        {
            if($fmatiere->isValid($_POST))
            {
                $dateDebut = $request->getParam('dateDebut', null);
                $dateFin = $request->getParam('dateFin', null);
                
                $tsite = new TSite;
                $infosSite = $tsite->getInfos(null ,$matiere, $dateDebut, $dateFin);

                $this->view->matiere = $matiere;
                $this->view->dateDebut = $dateDebut;
                $this->view->dateFin = $dateFin;
                $this->view->infosSite = $infosSite;
                $this->view->send = true;
            }
            else
            {
                if($creerPdf)
                {
                    $layout = Zend_Layout::getMvcInstance();
                    $layout->setLayout('vide'); 
                    
                    $dateDebut = $request->getParam('dd', null);
                    $dateFin = $request->getParam('df', null);
                    $dateDebut = str_replace('-', '/', $dateDebut);
                    $dateFin = str_replace('-', '/', $dateFin);
                    //echo $matiere.' '.$dateDebut.' '.$dateFin;exit;
                    
                    $tsite = new TSite;
                    $infosSite = $tsite->getInfos(null ,$matiere, $dateDebut, $dateFin);

                    $this->view->matiere = $matiere;
                    $this->view->dateDebut = $dateDebut;
                    $this->view->dateFin = $dateFin;
                    $this->view->infosSite = $infosSite;
                    $this->view->creerPdf = $creerPdf;
                    $this->view->send = false;
                }
            }
        }
        $this->view->formMatiere = $fmatiere;
    }
}

