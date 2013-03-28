<?php
class TCollecte extends Zend_Db_Table_Abstract
{
    protected $_name = 'T_COLLECTE';
    protected $_primary = array('DATE_COLLECTE','NO_CONTENEUR');
   
    public function getConteneurs($matiere)
    {
        $req = $this->select()
                    ->from($this->_name, 'NO_CONTENEUR')
                    ->where('MATIERE = ?', $matiere)
                ;
        return $this->_db->fetchCol($req);
    }
    public function getMatieres()
    {
        $req = $this->select()->distinct()
                    ->from($this->_name, 'MATIERE')
                ;
        return $this->_db->fetchCol($req);
    }
    
    /**
     * recupere les informations d'un conteneur (si spécifié, pour tous sinon),
     * pour une matiere (Verre couleur par defaut)
     * pendant une periode (si pas spécifiée, ou un champs sur deux remplis => 
     * depuis le premier relevé a aujourdhui)
     * et le nombre de relevés effectués pour avoir le tonnage sur un conteneur
     * 
     * @param string $nConteneur : le numero du conteneur
     * @param string $matiere : la matiere du conteneur que l'on cherche
     * @param date $dateDebut : date de debut de periode de recherche
     * @param date $dateFin : date de fin
     * @return infos sur un ou plusieurs conteneurs
     */
    public function getInfos($nConteneur =null, $matiere ='Verre Couleur', $dateDebut =null, $dateFin =null)
    {
        $imbriquee = $this->select()->distinct()
                          ->from($this->_name,array('ID_SITE'))
                    ;
        //Zend_Debug::dump($this->fetchAll($imbriquee)->toArray());
        //echo $imbriquee->assemble();exit;

        // retourne les infos sur un conteneur et sa quantite collecte
        // (sans limite de temps)
        $req = $this->select()->setIntegrityCheck(false)
                    ->from(array('c'=>$this->_name),array('count(*) levees','SUM(QTE_COLLECTE) qte','NO_CONTENEUR', 'NOM_GROUPEMENT', 'NOM_LOCALITE', 'MATIERE', 'VOLUME'))
                    ->join(array('s'=>'t_site'),'c.ID_SITE=s.ID_SITE', '*')
                    ->where('c.ID_SITE IN (?)', new Zend_Db_Expr($imbriquee))
                    ->where('MATIERE = ?', $matiere)
                    ->where('STAT_SITE = ?', 1)
                ;
        // si on a specifié une periode
        if(!is_null($dateDebut) && !is_null($dateFin) && $dateDebut !="" && $dateFin != "")
        {
            //echo $dateDebut.'<br>'.$dateFin;exit;
            $req->where('c.DATE_COLLECTE >= ? ', $dateDebut)
                ->where('c.DATE_COLLECTE <= ? ', $dateFin);
        }
        //var_dump($dateDebut);echo '<br>';var_dump($dateFin);exit;
        // si on a specifié un conteneur particulier
        if(!is_null($nConteneur))
        {   // on recupere que sur ce conteneur
            $req->where('c.NO_CONTENEUR = ?', $nConteneur);
            //echo $req->assemble();exit;
        }
        $req->group('c.NO_CONTENEUR, 
                c.NOM_GROUPEMENT, c.NOM_LOCALITE,
                c.MATIERE, c.VOLUME, s.ID_SITE, s.ID_COMMUNE, s.LOC_SITE, 
                s.NOM_SITE, s.USED_SITE, s.STAT_SITE');
        
        if(!is_null($nConteneur))
        {
            // on a qu'une ligne, on utilise donc fetchRow (pas besoin de tri)
            $res = $this->fetchRow($req);
            // si $res contient qqch, on le retourne, sinon on retourne null
            return $res ? $res->toArray() : null;
        }
        else
        {   // on trie du plus grand tonnage au plus petit
            $req->order('qte DESC');
            //echo $req->assemble();exit;
            return $this->fetchAll($req)->toArray();
        }
    }
    public function getConteneursParCommune()
    {
        try {
        $req = $this->select()->setIntegrityCheck(false)
                    ->distinct()
                    ->from(array('c'=>$this->_name), 'NO_CONTENEUR')
                    ->join(array('co'=>'T_COMMUNE'),'c.ID_COMMUNE=co.ID_COMMUNE', 'NOM_COMMUNE')
                    ->join(array('s'=>'T_SITE'),'c.ID_SITE=s.ID_SITE', array('ID_SITE','STAT_SITE'))
                    ->order('NOM_COMMUNE DESC, NO_CONTENEUR ASc')
                ;
        
        //Zend_Debug::dump($this->fetchAll($req)->toArray());exit;
        return $this->fetchAll($req)->toArray();
        }
        catch(Exception $e)
        {
            echo $e->getMessage();exit;
        }
    }
    public function getTonnageConteneur($idCommune)
    {
        try {
        
        $req = $this->select()->setIntegrityCheck(false)
                    ->from(array('c'=>$this->_name),array('SUM(QTE_COLLECTE) qte','NO_CONTENEUR'))
                    ->join(array('p'=>'T_PRESTATAIRE'),'p.NO_CONTENEUR=c.NO_CONTENEUR',array('NOM_EMPLACEMENT'))
                    ->where('c.ID_COMMUNE = ?', $idCommune)
                ;
        
        $req->group('c.NO_CONTENEUR, c.NOM_LOCALITE, NOM_EMPLACEMENT');
        
         // on trie du plus grand tonnage au plus petit
            $req->order('qte DESC');
            return $this->fetchAll($req)->toArray();
        }
        catch(Exception $e)
        {
            echo $e->getMessage();exit;
        }
    }
}
