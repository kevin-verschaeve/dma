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
        // recupere les parametres par leur nom, et leur met une valeur par defaut si vide
        $matiere = $this->getRequest()->getParam('sel_matiere', 'VERRE');
        $ajax = $this->getRequest()->getParam('ajax', false);
        
        if($ajax)
        {
            // change le layout, pour ne pas recuperer les balises body, html...
            // seulement si on vient en ajax, car sinon on a besoin des balises
            $layout = Zend_Layout::getMvcInstance();
            $layout->setLayout('vide');

            // si !ajax : on arrive sur la page (on a pas encore effectué de requete ajax)
        }
        $tsite = new TSite();
        
        $tabSite = $tsite->getSites($matiere);
        // copie les valeurs dans les clés (pour les select)   
        $tabSite = array_combine($tabSite, $tabSite);
        
        $matieres = array(
                'VERRE' => 'Verre',
                'CORPS_PLATS' => 'Corps Plats',
                'CORPS_CREUX' => 'Corps Creux'
            );
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
        
        $erreur = null;
        $mode = '';
        $form = new FImport;
        
        $request = $this->getRequest();
        if($request->isPost())  // c'est une validation de formulaire
        {               
            // le formulaire respecte les règles imposées
            if($form->isValid($_FILES))
            {
                $matiere = $request->getParam('radioMatiere');

                // recupere les infos surle fichier uploadé
                $fichier = $form->input_fichier->getFileName();
                $infosFichier = pathinfo($fichier);                
                $dirname = $infosFichier['dirname'];
                $nomFichier = $infosFichier['basename'];
                $nomSansExt = $infosFichier['filename'];
                $extension = $infosFichier['extension'];
                $tempNom = $_FILES['input_fichier']['tmp_name'];
                //Zend_Debug::dump($infosFichier);exit;

                // verifie que le fichier est bien un csv
                // strtolower au cas ou l'extension est  CSV
                if(strtolower($extension) == 'csv')
                {
                    if(isset($_POST['sub_fichier'])) 
                    {
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
                            //$tempo = RESOURCE_PATH.'\\temp\\'.$nomFichier;
                            $tempo = $dirname.'\\'.$nomFichier;
                            // copie le fichier dans ce repertoire
                            $copyToTempo = copy($archiveDir, $tempo);
                            chmod($tempo, 0777);

                            if ($copyToTempo) // on a reussi a copier
                            {  
                                //echo $tempo;exit;
                                 // on ouvre le fichier en lecture seule
                                 $handle = fopen($tempo, "r");
                                 if($handle)  // il est ouvert
                                 {      
                                     $nbNouvellesLignes = $total = 0;
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
                                            if($matiere == 'Verre')
                                            {
                                                $ligne = array(
                                                    //'C_NOMGRPGRP' => $data[0],
                                                    'C_NOMGRPGRP' => '',
                                                    'C_NOMGROUPEMENT' => $data[0],
                                                    'C_FILLER1' => $data[1],
                                                    'C_MATIERE' => $data[2],
                                                    'C_INSEE' => $data[3],
                                                    'C_COMMUNE' => $data[4],
                                                    'C_LOCALITE' => $data[5],
                                                    'C_EMPLACEMENT' => $data[6],
                                                    'C_CONTENEUR' => $data[7],
                                                    'C_PATE' => $data[8],
                                                    'C_VOLUME' => $data[9],
                                                    'C_COLLECTE' => $data[10],
                                                    'C_FILLER2' => $data[11],
                                                    'C_FILLER3' => $data[12],
                                                    'C_DATE' => $data[13],
                                                    'C_HEURE' => $data[14]
                                                );
                                                $total += str_replace(',', '.', $data[10]);
                                            }
                                            else
                                            {
                                                $ligne = array(
                                                    'C_DATE' => $data[0],
                                                    'C_HEURE' => '00:00',
                                                    'ID_SITE' => $data[1],
                                                    'C_LOCALITE' => $data[3],
                                                    'C_MATIERE' => $matiere,
                                                    'C_COLLECTE' => $data[4],
                                                    'C_NOMGROUPEMENT' => 'ST QUENTIN',
                                                    'C_PATE' => $data[1],
                                                    'C_VOLUME' => $data[5]
                                                );
                                                $total += $data[5];
                                                //Zend_Debug::dump($ligne);exit;                                            
                                            }
                                            // on appelle la fonction qui réalise l'insert dans le modele
                                            $tdata->inserer($ligne);
                                            // compte le nombre de lignes
                                            $nbNouvellesLignes++;
                                            $erreur = false;
                                        }
                                     }
                                     catch(Exception $e)
                                     {
                                         $erreur = 'Fichier incorrect ! Assurez vous
                                             que vous importez le fichier correspondant à la matière sélectionnée
                                             et que celui ci est conforme.';
                                     }
                                     // on ferme le fichier
                                     fclose($handle);
                                     // supprime le fichier temporaire
                                     unlink($tempo);

                                     // suivant la matiere, la requete n'est pas la même
                                     if($matiere == 'Verre')
                                     {
                                         $sql = "insert into T_COLLECTE
                                            select to_DATE (a.C_DATE || ' ' ||a.C_HEURE||':00', 'DD/MM/YYYY HH24:MI:SS'),
                                                   a.C_PATE,
                                                   a.C_NOMGROUPEMENT,
                                                   b.ID_COMMUNE,
                                                   a.C_LOCALITE,
                                                   b.ID_SITE,
                                                   a.C_MATIERE,
                                                   a.C_VOLUME,
                                                   to_number (a.C_COLLECTE),
                                                   to_date('".date('d/m/Y H:i:s')."', 'DD/MM/YY HH24:MI:SS')
                                              from T_DATA_COLLECTE a, T_PRESTATAIRE b, T_SITE c
                                             where a.C_PATE = b.NO_CONTENEUR
                                                and b.ID_SITE = c.ID_SITE
                                                and c.USED_SITE = 1";
                                     }
                                     else 
                                     {
                                         $sql = "insert into T_COLLECTE
                                                    select to_DATE (a.C_DATE || ' ' ||a.C_HEURE||':00', 'DD/MM/YYYY HH24:MI:SS'),
                                                    a.C_PATE,
                                                    a.C_NOMGROUPEMENT,
                                                    c.ID_COMMUNE,
                                                    a.C_LOCALITE,
                                                    c.ID_SITE,
                                                    a.C_MATIERE,
                                                    a.C_VOLUME,
                                                    to_number (a.C_COLLECTE),
                                                    to_date('".date('d/m/Y H:i:s')."', 'DD/MM/YY HH24:MI:SS')
                                               from T_DATA_COLLECTE a, T_SITE c
                                              where a.ID_SITE = c.ID_SITE
                                                 and c.USED_SITE = 1";
                                     }

                                     // agrégation des données dans les autres tables 
                                     $db = Zend_Registry::getInstance()->get("db");
                                     $db->beginTransaction();
                                     $statement = $db->prepare($sql);
                                     $statement->execute();
                                     $db->commit();   

                                     $this->view->nbLignesSupp = $nbLignesSupp;
                                     $this->view->nbNouvellesLignes = $nbNouvellesLignes;
                                     $this->view->total = $total;
                                     
                                     $mode = 'envoi';
                                     
                                 } else { $erreur = 'Erreur lors de la lecture du fichier'; }
                             } else { $erreur = 'Erreur lors de la copie du fichier (dans temp)'; }
                       } else { $erreur = 'Erreur lors de la copie du fichier (dans archives)'; }
                    } 
                    elseif(isset($_POST['bt_verif'])) 
                    {
                        $tempo = $dirname.'\\'.$nomFichier;
                        $upToTempo = move_uploaded_file($tempNom, $tempo);

                        if ($upToTempo) // on a reussi a copier
                        {  
                             // on ouvre le fichier en lecture seule
                             $handle = fopen($tempo, "r");
                             if($handle)  // il est ouvert
                             {    
                                 $tprest = new TPrestataire;
                                 $failure = false;
                                 $err_fail = $absents = $tot_par_insee =  array();
                                 $nbLignes = 0;
                                 $anc_insee = '';
                                 
                                 if($matiere == 'Verre')
                                 {
                                    while (($ligne = fgetcsv($handle, 0, ";")) !== FALSE) {
                                        $nbLignes++;

                                        if(!ctype_alpha(str_replace(' ', '', $ligne[0]))) {
                                            $failure = true;
                                            $err_fail[$nbLignes][] = 'La première colonne doit être du texte';
                                        }
                                        if(empty($ligne[0])) {
                                            $failure = true;
                                            $err_fail[$nbLignes][] = 'Il semble y avoir une colonne en trop en début de ligne. Vérifiez le fichier';
                                        }

                                        if(strlen($ligne[3]) > 5 || !ctype_digit($ligne[3]) ) {
                                            $failure= true;
                                            $err_fail[$nbLignes][] = 'Le numéro INSEE doit contenir 5 chiffres au maximum : '.$ligne[3];
                                        }

                                        if($ligne[3] == $anc_insee || in_array($ligne[3], $tot_par_insee)) {
                                            $tot_par_insee[$anc_insee]['tot'] += str_replace(',', '.', $ligne[10]);
                                            $tot_par_insee[$anc_insee]['nb']++;
                                        } else {
                                            $tot_par_insee[$ligne[3]]['tot'] = str_replace(',', '.', $ligne[10]);
                                            $tot_par_insee[$ligne[3]]['nb'] = 1;
                                            $anc_insee = $ligne[3];
                                        }

                                        if(!ctype_alpha(str_replace(' ', '', $ligne[4]))) {
                                            $failure = true;
                                            $err_fail[$nbLignes][] = 'Commune non valide (chiffres non acceptés) : '.$ligne[4];
                                        }
                                        if(!ctype_alpha(str_replace(' ', '', $ligne[5]))) {
                                            $failure = true;
                                            $err_fail[$nbLignes][] = 'Localité non valide (chiffres non acceptés)  : '.$ligne[5];
                                        }

                                        if(!$tprest->existe($ligne[8])) {
                                            $failure = true;
                                            $absents[] = $ligne[8];
                                        }
                                        if(!is_numeric(str_replace(',', '.', $ligne[10])) && !is_int($ligne[10])) {
                                            $failure = true;
                                            $err_fail[$nbLignes][] = 'Le tonnage relevé est incorrect : '.$ligne[10];
                                        }                                     


                                        $this->view->tot_par_insee = $tot_par_insee;
                                    }
                                } else {
                                    $tsite = new TSite;
                                    while (($ligne = fgetcsv($handle, 0, ";")) !== FALSE) {
                                        $nbLignes++;
                                        
                                        if(empty($ligne[0])) {
                                            $failure = true;
                                            $err_fail[$nbLignes][] = 'Il semble y avoir une colonne en trop en début de ligne. Vérifiez le fichier';
                                        }
                                        if(!ctype_digit($ligne[1])) {
                                            $failure = true;
                                            $err_fail[$nbLignes][] = 'ID site incorrect : '.$ligne[1];
                                        } else {
                                            if(!$tsite->existe($ligne[1])) {
                                                $failure = true;
                                                $absents[] = $ligne[1];
                                            }
                                        }
                                        if(!ctype_alpha(str_replace(' ', '', $ligne[3]))) {
                                            $failure = true;
                                            $err_fail[$nbLignes][] = 'Localité non valide (chiffres non acceptés)  : '.$ligne[3];
                                        }
                                    }
                                    
                                }
                                // fichier verfié, et pas d'erreurs
                                if(!$failure) {                    
                                   // créé le formulaire en indiquant qu'il a ete verifié
                                   $form = new FImport(true);
                                }
                                
                                $this->view->failure = $failure;
                                $this->view->err_fail = $err_fail;
                                $this->view->absents = $absents;

                                $mode = 'verif';
                                fclose($handle);
                                unlink($tempo);
                             }
                        } else {
                            $erreur = 'Erreur lors de la copie du fichier (dans temp)';
                        }
                    }
                }
                else
                {   // mauvaise extension
                    $erreur = $nomFichier.' invalide !
                         Seuls les fichiers .csv sont acceptés';
                }  
            }
            $this->view->matiere = $matiere;
        }
        $this->view->mode = $mode;
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
            // recupere le checkbox, et son état
            $idSite = $this->getRequest()->getParam('site', false);
            $etat =  $this->getRequest()->getParam('etat', false);
            
            // va changer l'etat dans la base
            $tsite->changeEtatStat($idSite, $etat);
            exit;
        }
        else 
        {
            // retourne les conteneurs de chaque commune
            $sites = $tsite->getSitesCommunes();
            //Zend_Debug::dump($sites);exit;
            $this->view->sites = $sites;
        }
    }
    /**
     * Recupere les communes et les affiches dans une liste
     * Affiche un graphique avec en valeur par defaut saint quentin, ou la valeur choisie dans la liste
     */
    public function graphiqueAction()
    {
        $this->_helper->actionStack('header', 'index', 'default', array());
        $id = $this->getRequest()->getParam('sel_communes', false);
        $matiere = $this->getRequest()->getparam('matiere', 'VERRE');

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
        $fcommunes = new FCommune($lesCommunes, $id, $matiere);

        $this->view->fcommunes = $fcommunes;
        $this->view->export = false;

        if($id)
        {
            if($id != false && (int)$id > 0)    // une ville est choisie
            {
                // on va chercher tous ses conteneur avec leur tonnages
                $TparConteneur = $tsite->getTonnageSite((int)$id, $matiere);
                
                $this->view->nomCommune = $nomCommune;
                $this->view->tonnage = $TparConteneur;
                $filtreUneCommune = true;  
                
                $this->exportation($TparConteneur, $nomCommune);
            }
        }
        else
        {
            $tParCommune = $tsite->getTonnageCommunes($matiere);
            //Zend_Debug::dump($tParCommune);exit;
            $this->view->tonnage = $tParCommune;
            
            $this->exportation($tParCommune);
        }
        $this->view->matiere = $matiere;
        $this->view->filtreUneCommune = $filtreUneCommune;
    }
    
    private function exportation($tonnage, $nomCommune = 'GLOBAL')
    {
        $nomFichier = $nomCommune.'.csv';
        $fichier = RESOURCE_PATH.'\export\\'.$nomFichier;
        $handle = fopen($fichier, 'w');
        if($handle)
        {
            foreach($tonnage as $t)
            {
                fputcsv($handle, $t, ';');
               /* foreach($t as $champs) {
                    fwrite($handle, '"'.utf8_encode($champs).'";');
                }
                fwrite($handle, "\r\n");*/
            }
            fclose($handle);
            $this->view->fichier = $nomFichier;
            $this->view->export = true;
        }
        else
        {
            echo 'Erreur lors de l\'ouverture du ficher : '.$fichier;
        }
    }
    /**
     * Ajouter un site en base
     */
    public function nouveausiteAction()
    {
        $this->_helper->actionStack('header', 'index', 'default', array());
        $idSite = $this->getRequest()->getParam('site', '');
        $nCont = $this->getRequest()->getParam('cont', '');
        
        $fnvsite = new FnvSite($idSite, $nCont);
        $request = $this->getRequest();
        
        $send = false;
        $msg = false;
        
        if($request->isPost())
        {
            if($fnvsite->isValid($_POST))
            {
                $commune = $request->getParam('commune');
                $nConteneur = $request->getParam('nconteneur');
                $adresse = $this->stripAccents($request->getParam('adresse'));
                $complement = $this->stripAccents($request->getParam('complement'));
                $nsite = $request->getParam('nsite');
                $matieres = $request->getParam('matieres');
                
                $upAdresse = strtoupper($adresse);
                // tableau pour l'ajout dans T_PRESTATAIRE
                $donneesPrestataire = array(
                    'ID_COMMUNE' => $commune,
                    'NO_CONTENEUR' => $nConteneur,
                    'NOM_EMPLACEMENT' => $upAdresse,
                    'ID_SITE' => $nsite
                );
                
                // Ajout dans la table
                $tprestataire = new TPrestataire;
                $insertPrest = $tprestataire->ajouterConteneur($donneesPrestataire);
                $donneesPrestataire['NOM_EMPLACEMENT'] = $adresse;
                
                // tableau pour l'ajout dans T_SITE
                $donneesSite = array(
                    'ID_COMMUNE' => $commune,
                    'ID_SITE' => $nsite,
                    'NOM_SITE' => $upAdresse,
                    'LOC_SITE' => strtoupper($complement),
                    'USED_SITE' => 1,
                    'STAT_SITE' => 1,
                    // initialise tous les champs à 0
                    'VERRE' => 0,
                    'CORPS_PLATS' => 0,
                    'CORPS_CREUX' => 0
                );
                // mets les champs choisis par les checkbox a 1
                foreach ($matieres as $matiere) {
                    $donneesSite[$matiere] = 1;
                }
                
                // Ajout dans la table
                $tsite = new TSite;
                $insertSite = $tsite->ajouter($donneesSite);
                
                // si l'ajout à réussi dans les deux tables
                if($insertPrest && $insertSite) {
                    $this->view->donnees = $donneesPrestataire;
                    $this->view->complement = $complement;
                    $msg = 'Insertion réussie';
                } else {
                    $msg = 'Echec lors de la création';
                }
                $send = true;
            }
        }
        $this->view->send = $send;
        $this->view->fnvsite = $fnvsite;
        $this->view->message = $msg;
    }
    public function suplignesAction() {
        $this->_helper->actionStack('header', 'index', 'default', array());
        
        $request = $this->getRequest();
        $matiere = $request->getParam('matiere', 'VERRE');
        $mois = $request->getParam('mois', date('n'));
        $annee = $request->getParam('annee', date('Y'));
        
        $send = $sup = false;
        $form = new FSupLignes($matiere, $mois, $annee);
        
        
        if($request->isPost())
        {
            if($form->isValid($_POST))
            {
                $tcollecte = new TCollecte;
                if(isset($_POST['sub_voir'])) {
                    $voir_lignes = $tcollecte->getLignes($matiere, $mois, $annee);
                    
                    $this->view->lignes = $voir_lignes;
                    $form = new FSupLignes($matiere, $mois, $annee, true);
                } elseif(isset($_POST['sub_sup'])) {
                    $nbLignesSupp = $tcollecte->supprime($matiere, $mois, $annee);
                    $sup = true;
                    $this->view->nbLignesSupp = $nbLignesSupp;
                }
            }
            $send = true;
        }
        
        $this->view->send = $send;
        $this->view->sup = $sup;
        $this->view->form = $form;
    }
    
    public function stripAccents($str, $encoding ='utf-8'){	
         // transformer les caractères accentués en entités HTML
        $str = htmlentities($str, ENT_NOQUOTES, $encoding);

        // remplacer les entités HTML pour avoir juste le premier caractères non accentués
        // Exemple : "&ecute;" => "e", "&Ecute;" => "E", "Ã " => "a" ...
        $str = preg_replace('#&([A-za-z])(?:acute|grave|cedil|circ|orn|ring|slash|th|tilde|uml);#', '\1', $str);

        // Remplacer les ligatures tel que : Œ, Æ ...
        // Exemple "Å“" => "oe"
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
        // Supprimer tout le reste
        $str = preg_replace('#&[^;]+;#', '', $str);

        return $str;
    }
}