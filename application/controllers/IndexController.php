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
    /**
     * Recupere les informations sur un conteneur, enfonction de la matiere
     * prealablement choisie, pendant une periode de temps limitée ou non
     */
    public function tonnageajaxAction()
    {
        // recupere les parametres par leur nom, et leur met une valeur par defaut si vide
        $matiere = $this->getRequest()->getParam('sel_matiere', 'Verre Couleur');
        $ajax = $this->getRequest()->getParam('ajax', false);
        
        if($ajax)
        {
            // change le layout, pour ne pas recuperer les balises body, html...
            // seulement si on vient en ajax, car sinon on a besoin des balises
            $layout = Zend_Layout::getMvcInstance();
            $layout->setLayout('vide');

            // si !ajax : on arrive sur la page (on a pas encore effectué de requete ajax)
        }
        try{
            $tcollecte = new TCollecte;
            
            // recupere les conteneurs et les matieres
            $matieres = $tcollecte->getMatieres();
            $tabConteneur = $tcollecte->getConteneurs($matiere);
            
            // copie les valeurs dans les clés (pour les select)            
            $matieres = array_combine($matieres, $matieres);
            $tabConteneur = array_combine($tabConteneur, $tabConteneur);
            //Zend_Debug::dump($tabConteneur);exit;
            
            // cree le formulaire, on passe des parametres pour mettre des valeurs par defaut
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
                    // pour le conteneur $nConteneur gerant la matiere $matiere
                    // pendant la periode >=$dateDabut <= $dateFin
                    // si les dates sont null, la recherche ne se limitera pas dans le temps
                    $infosConteneur = $tcollecte->getInfos($nConteneur, $matiere, $dateDebut, $dateFin);
                    
                    //Zend_Debug::dump($infosConteneur);exit;
                    
                    // envoi les variables a la vue
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
        $erreur = ' ';
        
        $request = $this->getRequest();
        if($request->isPost())  // c'est une validation de formulaire
        {
            // le formulaire respecte les règles imposées
            if($form->isValid($_FILES))
            {
                // recupere les infos surle fichier uploadé
                $fichier = $form->input_fichier->getFileName();
                $infosFichier = pathinfo($fichier);                
                $dirname = $infosFichier['dirname'];
                $nomFichier = $infosFichier['basename'];
                $nomSansExt = $infosFichier['filename'];
                $extension = $infosFichier['extension'];
                //Zend_Debug::dump($infosFichier);exit;
                
                // verifie que le fichier est bien un csv
                if($extension == 'csv' || $extension == 'CSV')
                {
                   $tempNom = $_FILES['input_fichier']['tmp_name'];
                   
                   // ajoute "_Annee"  au nom de fichier pour eviter les doublons
                   $date = new Zend_Date(Zend_Date::now());
                   $annee = $date->get('yyyy');
                   $nomDate = $nomSansExt.'_'.$annee.'.'.$extension;                   
                   
                   // set le repertoire de sauvegarde des fichiers
                   $archiveDir = RESOURCE_PATH."\\archives\\$nomDate";
                   // copie le fichier sélectionné dans ce répertoire
                   $moveToArchive = move_uploaded_file($tempNom, $archiveDir);
                   
                   if($moveToArchive)  // on a reussi la copie dans archive
                   {    
                       // set le nom du repertoire ou seront mis les fichiers en temporaire
                        $tempo = $dirname.'\\'.$nomFichier;
                        // copie le fichier dans ce repertoire
                        $copyToTempo = copy($archiveDir, $tempo);
                        
                        if ($copyToTempo) // on a reussi a copier
                        {   
                             // on ouvre le fichier en lecture seule
                             $handle = fopen($tempo, "r");
                             if($handle)
                             {  // il est ouvert
                                 
                                 $nbNouvellesLignes = 0;
                                 $tdata = new TDataCollecte;
                                 
                                 // on vide la table de toutes ses lignes
                                 $nbLignesSupp = $tdata->videTable();
                                 
                                 // on parcourt le fichier ligne par ligne, avec ";" comme delimiteur
                                 while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
                                     // on créé un tableau avec 
                                     // en clé : le nom de la colonne dans la table
                                     // en valeur : la valeur a inserer pour cette colonne
                                     // $data recupere la ligne pointée par le curseur
                                     $ligne = array(
                                         'C_NOMGRPGRP' => $data[0],
                                         'C_NOMGROUPEMENT' => $data[1],
                                         'C_FILLER1' => $data[2],
                                         'C_MATIERE' => $data[3],
                                         'C_INSEE' => $data[4],
                                         'C_COMMUNE' => $data[5],
                                         'C_LOCALITE' => $data[6],
                                         'C_EMPLACEMENT' => $data[7],
                                         'C_CONTENEUR' => $data[8],
                                         'C_PATE' => $data[9],
                                         'C_VOLUME' => $data[10],
                                         'C_COLLECTE' => $data[11],
                                         'C_FILLER2' => $data[12],
                                         'C_FILLER3' => $data[13],
                                         'C_DATE' => $data[14],
                                         'C_HEURE' => $data[15]
                                     );
                                     
                                     // on appelle la fonction qui réalise l'insert dans le modele
                                     $tdata->inserer($ligne);
                                     
                                     // compte le nombre de lignes
                                     $nbNouvellesLignes++;
                                 }
                                 // on ferme le fichier
                                 fclose($handle);      
                                 
                                 // supprime le fichier temporaire
                                 unlink($tempo);
                                 $erreur = false;
                                 
                                 $this->view->nbLignesSupp = $nbLignesSupp;
                                 $this->view->nbNouvellesLignes = $nbNouvellesLignes;
                             } else { $erreur = 'Erreur lors de la lecture du fichier'; }
                         } else { $erreur = 'Erreur lors de la copie du fichier'; }
                   } else { $erreur = 'Erreur lors de la copie du fichier'; }
                }
                else
                {   // mauvaise extension
                    $erreur = 'Les fichiers .'.$extension.' ne sont pas pris en compte.
                        <br>Seuls les fichiers .csv sont acceptés';
                }  
            }
        }        
        $this->view->erreur = $erreur;
        $this->view->formFichier = $form;
    }
}

