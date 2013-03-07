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
            $radio = $this->getRequest()->getParam('radio', 'v');
            $ajax = $this->getRequest()->getParam('ajax', false);
            
            if($ajax)
            {
                // change le layout, pour ne pas recuperer les balises body, html...
                // seulement si on vient en ajax, car sinon on a besoin des balises
                $layout = Zend_Layout::getMvcInstance();
                $layout->setLayout('vide'); 
            }
            
            // regle le nom correct de la matiere
            if($radio == 'v')
            {
                $matiere = 'Verre Couleur';
            }
            elseif($radio == 'pc')
            {
                $matiere = 'Papier/Carton';
            }
            else
            {
                $matiere = 'Corps Creux';
            }

            // instancie les tables que l'on aura besoin
            $tcollecte = new TCollecte;
            $tsite = new TSite;

            // recupere tous les ids de sites
            $ids = $tcollecte->getDistinctID($matiere);
            
            $infosConteneur = array();
            $tonnage = array();
            $releves = array();
            
            $infosConteneur = $tcollecte->test();
            // pour chaque site, on get ses infos, son tonnage, et le nombre de relevés
            /*foreach($ids as $idConteneur)
            {
                $infosConteneur[$idConteneur] = $tsite->getInfosSite($idConteneur);
                $tonnage[$idConteneur] = $tcollecte->getTonnage($idConteneur, $matiere);
                $releves[$idConteneur] = $tcollecte->getNbReleves($idConteneur);
            }*/
            Zend_Debug::dump($infosConteneur);exit;
            // on envoi les variables a la vue
            $this->view->infosSite = $infosConteneur;
            $this->view->tonnage = $tonnage;
            $this->view->releves = $releves;
            $this->view->ajax = $ajax;
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
        }
        //$this->_helper->viewRenderer->setResponseSegment('infos');
    }
    
}

