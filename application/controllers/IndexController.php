<?php

class IndexController extends Zend_Controller_Action
{
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
        // appelle l'action header du controller index
        // peut lui passer des parametres dans le tableau
        $this->_helper->actionStack('header', 'index', 'default', array());
        $this->_helper->actionStack('tonnageajax', 'index');
    }
    /**
     * Recupere les informations sur un conteneur, en fonction de la matiere
     * prealablement choisie, pendant une periode de temps limitée ou non
     */
    public function tonnageajaxAction()
    {
        try {
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
        $tcollecte = new TCollecte;
        
        // recupere les conteneurs et les matieres
        $matieres = $tcollecte->getMatieres();
        $tabSite = $tcollecte->getSites($matiere);
        
        // copie les valeurs dans les clés (pour les select)            
        $matieres = array_combine($matieres, $matieres);
        $tabSite = array_combine($tabSite, $tabSite);
        //Zend_Debug::dump($tabConteneur);exit;

        // cree le formulaire, on passe des parametres pour mettre des valeurs par defaut
        $form = new FSite($tabSite, $matieres, $matiere);

        // recupere la requete
        $request = $this->getRequest();
        
        // si la requete est de type POST, on vient d'une validation du formulaire
        if($request->isPost())
        {
            // si le formulaire est valide (regles mises en places a la création du formulaire respectées)
            if($form->isValid($_POST))
            {
                // on recupere le contenu des champs, un par un, avec l'id du champ
                $nSite = $request->getParam('nSite');
                $dateDebut = $request->getParam('dateDebut', null);
                $dateFin = $request->getParam('dateFin', null);

                // recupere les informations
                // pour le site $nSite gerant la matiere $matiere
                // pendant la periode >=$dateDebut <= $dateFin
                // si les dates sont null, la recherche ne se limitera pas dans le temps
                $tsite = new TSite;
                $infosSite = $tsite->getInfos($nSite, $matiere, $dateDebut, $dateFin, false);

                //Zend_Debug::dump($infosSite);exit;

                // envoi les variables a la vue
                $this->view->nSite = $nSite;
                $this->view->dateDebut = $dateDebut;
                $this->view->dateFin = $dateFin;
                $this->view->infosSite = $infosSite;
                $this->view->send = true;
            }
        } 
        // envoi le formulaire a la vue
        $this->view->form = $form;
        $this->view->ajax = $ajax;
        }catch(Exception $e)
        {
            echo $e->getMessage();exit;
        }
    }
    /**
     * Creer et gere un formulaire contenant seulement un input type file
     * recupere le fichier selectionné, le copie dans le dossier archive
     * parcours chaque ligne du fichier et les insere en bdd, apres avoir vidé la table
     * Gere les erreurs s'il y en a
     */
    public function importerAction()
    {
        $this->_helper->actionStack('header', 'index', 'default', array());
        
        $form = new FImport;
        $erreur = null;
        
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
                             if($handle)  // il est ouvert
                             {      
                                 $nbNouvellesLignes = 0;
                                 $tdata = new TDataCollecte;
                                 
                                 // on vide la table de toutes ses lignes
                                 $nbLignesSupp = $tdata->videTable();
                                 
                                 try {
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
                                        $erreur = false;
                                    }
                                 }
                                 catch(Exception $e)
                                 {
                                     $erreur = 'Fichier incorrect !';
                                 }
                                 // on ferme le fichier
                                 fclose($handle);      
                                 
                                 // supprime le fichier temporaire
                                 unlink($tempo);
                                 
                                 $this->view->nbLignesSupp = $nbLignesSupp;
                                 $this->view->nbNouvellesLignes = $nbNouvellesLignes;
                             } else { $erreur = 'Erreur lors de la lecture du fichier'; }
                         } else { $erreur = 'Erreur lors de la copie du fichier (dans temp)'; }
                   } else { $erreur = 'Erreur lors de la copie du fichier (dans archives)'; }
                }
                else
                {   // mauvaise extension
                    //$erreur = 'Les fichiers .'.$extension.' ne sont pas pris en compte.'
                    $erreur = $nomFichier.' invalide !'.'
                         Seuls les fichiers .csv sont acceptés';
                }  
            }
        }        
        $this->view->erreur = $erreur;
        $this->view->formFichier = $form;
    }
    /**
     * Affiche tous les conteneurs triés par communes
     * et permet de specifier si on souhaite ou non les inclures dans les statitisques
     */
    public function changeconteneurAction()
    {
        $this->_helper->actionStack('header', 'index');
        $ajax = $this->getRequest()->getParam('ajax', false);
        
        
        $tsite = new TSite;
        
        if($ajax)
        {
            // recupere tous les checkbox cochés
            $idSite = $this->getRequest()->getParam('site', false);
            $etat =  $this->getRequest()->getParam('etat', false);
            
            //Zend_Debug::dump($idSites);exit;
            
            // va changer l'etat dans la base
            $tsite->changeEtatStat($idSite, $etat);
            exit;
        }
        else 
        {
            // retourne les conteneur de chaque commune
            $sites = $tsite->getSitesCommunes();
            //Zend_Debug::dump($sites);exit;
            $this->view->sites = $sites;
        }
    }
    /**
     * Recupere les commune et les affiches dans une liste
     * Affiche un graphique avec en valeur par defaut saint quentin, ou la valeur choisie dans la liste
     */
    public function graphiqueAction()
    {
        $this->_helper->actionStack('header', 'index', 'default', array());
        $id = $this->getRequest()->getParam('commune', false);

        $filtreUneCommune = false;
        
        $tsite = new TSite;
        $tcommune = new TCommune;
        // recupere les liste des communes, pour le select
        $communes = $tcommune->getCommunes(true);
        $nomCommune = '';

        $lesCommunes = array();
        $lesCommunes[0] = '-- Toutes les communes --';
        foreach($communes as $uneCommune)
        {
            $lesCommunes[$uneCommune['ID_COMMUNE']] = $uneCommune['NOM_COMMUNE'];

            // si l'id en cours est celui de la commune cherchée, on sauvegarde le nom
            if((int)$id == $uneCommune['ID_COMMUNE'])
            {
                $nomCommune = $uneCommune['NOM_COMMUNE'];
            }
        }
        // créé le formulaire
        $fcommunes = new FCommune($lesCommunes, $id);

        $this->view->fcommunes = $fcommunes;

        if($id)
        {
            if($id != false && (int)$id > 0)    // une ville est choisie
            {
                // on va chercher tous ses conteneur avec leur tonnages
                $TparConteneur = $tsite->getTonnageSite((int)$id);
                //Zend_Debug::dump($TparConteneur);exit;
                $this->view->nomCommune = $nomCommune;
                $this->view->tonnage = $TparConteneur;
            }
            else 
            {
                $this->view->erreur = 'La ville choisie n\'existe pas !';
            }
            $filtreUneCommune = true;
        }
        else
        {
            $tParCommune = $tsite->getTonnageCommunes();
            //Zend_Debug::dump($tParCommune);exit;
            $this->view->tonnage = $tParCommune;
        }
        $this->view->filtreUneCommune = $filtreUneCommune;
    }
    /**
     * Ajouter un site en base
     */
    public function nouveausiteAction()
    {
        $this->_helper->actionStack('header', 'index', 'default', array());
        
        $fnvsite = new FnvSite;
        $request = $this->getRequest();
        
        $send = false;
        $msg = false;
        
        if($request->isPost())
        {
            if($fnvsite->isValid($_POST))
            {
                $commune = $request->getParam('commune');
                $nConteneur = $request->getParam('nconteneur');
                $adresse = $request->getParam('adresse');
                $complement = $request->getParam('complement');
                $nsite = $request->getParam('nsite');
                
                $donneesPrestataire = array(
                    'ID_COMMUNE' => $commune,
                    'NO_CONTENEUR' => $nConteneur,
                    'NOM_EMPLACEMENT' => $adresse,
                    'ID_SITE' => $nsite
                );
                
                $tprestataire = new TPrestataire;
                $insertPrest = $tprestataire->ajouterConteneur($donneesPrestataire);
                
                $donneesSite = array(
                    'ID_COMMUNE' => $commune,
                    'ID_SITE' => $nsite,
                    'NOM_SITE' => $adresse,
                    'LOC_SITE' => $complement,
                    'USED_SITE' => 1,
                    'STAT_SITE' => 1
                );
                
                $tsite = new TSite;
                $insertSite = $tsite->ajouter($donneesSite);
                
                
                if($insertPrest && $insertSite)
                {
                    $this->view->donnees = $donneesPrestataire;
                    $this->view->complement = $complement;
                    $msg = 'Insertion réussie';
                }
                else
                {
                    $msg = 'Echec lors de la création';
                }
                $send = true;
            }
        }
        $this->view->send = $send;
        $this->view->fnvsite = $fnvsite;
        $this->view->message = $msg;
    }
}

