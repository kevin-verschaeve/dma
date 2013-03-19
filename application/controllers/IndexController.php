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
        $this->_helper->actionStack('header', 'index', 'default', array());
        $this->_helper->actionStack('tonnageajax', 'index');
    }
    public function tonnageajaxAction()
    {
        $matiere = $this->getRequest()->getParam('sel_matiere', 'Verre Couleur');
        $ajax = $this->getRequest()->getParam('ajax', false);
        
        if($ajax)
        {
            // change le layout, pour ne pas recuperer les balises body, html...
            // seulement si on vient en ajax, car sinon on a besoin des balises
            $layout = Zend_Layout::getMvcInstance();
            $layout->setLayout('vide'); 
        }
        try{
            // on instancie les tables que l'on aura  besoin
            $tcollecte = new TCollecte;
            
            // recupere les conteneurs et les matieres
            $matieres = $tcollecte->getMatieres();
            $tabConteneur = $tcollecte->getConteneurs($matiere);
            
            // copie les valeurs dans les clés (pour les select)            
            $matieres = array_combine($matieres, $matieres);
            $tabConteneur = array_combine($tabConteneur, $tabConteneur);
            //Zend_Debug::dump($tabConteneur);exit;
            
            // cree le formulaire, on passe des parametres pour mettre des valeurs pas defaut
            $form = new FSite($tabConteneur, $matieres, $matiere);

            // recupere la requete
            $request = $this->getRequest();
            
            // si la requete est de type POST, on vient d'une validation du formulaire
            if($request->isPost())
            {
                // si le formulaire est valide (regles mises en places a la création du formulaire respectées)
                if($form->isValid($_POST))
                {
                    // on recupere le contenu des champs, un par un, avec l'id du champ
                    $nConteneur= $request->getParam('nConteneur');
                    $dateDebut = $request->getParam('dateDebut', null);
                    $dateFin = $request->getParam('dateFin', null);
                    
                    // recupere les informations
                    $infosConteneur = $tcollecte->getInfos($nConteneur, $matiere, $dateDebut, $dateFin);
                    
                    //Zend_Debug::dump($infosConteneur);exit;
                    
                    // envoi toutes les variables a la vue
                    $this->view->dateDebut = $dateDebut;
                    $this->view->dateFin = $dateFin;
                    $this->view->infosConteneur = $infosConteneur;
                    $this->view->send = true;
                }
            } 
        }catch(Exception $e)
        {
            echo $e->getMessage();exit;
        }
        // envoi le formulaire a la vue
        $this->view->form = $form;
        $this->view->ajax = $ajax;
    }
    public function importerAction()
    {
        $this->_helper->actionStack('header', 'index', 'default', array());
        
        $form = new FImport;
        
        $request = $this->getRequest();
        if($request->isPost())
        {
            if($form->isValid($_FILES))
            {
                $fichier = $form->input_fichier->getFileName();
                $infosFichier = pathinfo($fichier);                
                $dirname = $infosFichier['dirname'];
                $nomFichier = $infosFichier['basename'];
                $extension = $infosFichier['extension'];
                
                try {
                if($extension == 'csv' || $extension == 'CSV')
                {
                   $chemin = $dirname.'\\'.$nomFichier;
                   
                   $resultat = move_uploaded_file($_FILES['input_fichier']['tmp_name'], $chemin);
                   if ($resultat) 
                   {
                        $sqlLoader = APPLICATION_PATH ."/configs/Loader.bat";
                        $handle = fopen($sqlLoader, "w+");
                        if($handle)
                        {
                           $collecte = APPLICATION_PATH.'\configs\Collecte.ctl';
                           fwrite($handle, 'sqlldr userid=DMA/DMA@PROD10 control='.$collecte.' data='.$chemin.' errors=0');
                           
                           /*shell_*/exec($sqlLoader);
                        }
                    }
                }
                else
                {
                    echo 'Les fichiers '.$extension.' ne sont pas pris en compte.
                        <br>Seuls les fichiers .csv sont acceptés';
                }  
                    
                }
                catch(Exception $e)
                {
                    echo $e->getMessage();exit;
                }
            }
        }        
        $this->view->formFichier = $form;
    }
}

