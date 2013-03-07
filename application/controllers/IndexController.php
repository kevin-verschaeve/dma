<?php

class IndexController extends Zend_Controller_Action
{
    public function indexAction()
    {
        // appelle l'action header du controller index
        // peut lui passer des parametres dans le tableau
        $this->_helper->actionStack('header', 'index', 'default', array());
    }
    public function headerAction()
    {
        // appelle l'action footer et affiche le contenu du header
        $this->_helper->actionStack('footer', 'index', 'default', array());
        $this->_helper->viewRenderer->setResponseSegment('header');
    }
    public function footerAction()
    {
        // affiche le footer
        $this->_helper->viewRenderer->setResponseSegment('footer');
    }
    public function tonnageAction()
    {
        // appelle le header, qui appellera le footer
        $this->_helper->actionStack('header', 'index', 'default', array());
        
        try{
            // instancie un formulaire pour la saisie du site et une date
            $form = new FSite();

            // recupere la requete
            $request = $this->getRequest();
            
            // si la requete est de type POST, on vient d'une validation du formulaire
            if($request->isPost())
            {
                // si le formulaire est valide (regles mises en places a la création du formulaire respectées)
                if($form->isValid($_POST))
                {
                    // on recupere le contenu des champs, un par un, avec l'id du champ
                    $idsite = $request->getParam('idsite');
                    $dateDebut = $request->getParam('dateDebut', null);
                    $dateFin = $request->getParam('dateFin', null);

                    // on instancie les tables que l'on aura  besoin
                    $tcollecte = new TCollecte;
                    $tsite = new TSite;
                    //$tc = $tcollecte->getCollecte();

                    // recupere les informations d'un site
                    $infosSite = $tsite->getInfosSite($idsite);
                    // recupere le tonnage d'un site
                    // on peut ajouter 3 parametres
                    //  - $matiere = pour selectionner la matiere que l'on veut
                    //  - $dateDebut et $dateFin = pour faire la requete sur une periode limitée
                    $tonnage = $tcollecte->getTonnage($idsite);

                    //Zend_Debug::dump($tc);
                    //Zend_Debug::dump($infosSite);

                    // envoi toutes les variables a la vue
                    $this->view->dateDebut = $dateDebut;
                    $this->view->dateFin = $dateFin;
                    $this->view->infosSite = $infosSite;
                    $this->view->tonnage = $tonnage;
                    $this->view->send = true;
                }
            } 
        }catch(Exception $e)
        {
            echo $e->getMessage();exit;
        }
        // envoi le formulaire a la vue
        $this->view->form = $form;
    }
    public function trucAction()
    {
        $tco = new TCollecte;
        Zend_Debug::dump($tco->test());
        exit;
    }
}

