<?php
class TCollecte extends Zend_Db_Table_Abstract
{
    protected $_name = 'T_COLLECTE';
    protected $_primary = array('DATE_COLLECTE','NO_CONTENEUR');
    
    public function getCollecte()
    {
        $req = $this->select()
                    ->from($this->_name,array('*'))
                ;
        return $this->fetchAll($req)->toArray();
    }
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
     * Compte le nombre de ligne pour un conteneur, equivaut au nombre de relevé
     * @param int $idsite : id du site
     * @param type $dateDebut
     * @param type $dateFin
     * @return type
     */
    public function getNbReleves($idsite, $dateDebut =null, $dateFin =null)
    {
        $req = $this->select()
                    ->from($this->_name,'count(*)')
                    ->where('ID_SITE = ?', $idsite)
                ;
        if(!is_null($dateDebut) && !is_null($dateFin))
        {
            $req->where('DATE_COLLECTE >= ? ', $dateDebut)
                ->where('DATE_COLLECTE <= ? ', $dateFin);
        }
        
        return $this->_db->fetchOne($req);
    }
    
    
    /**
     * recupere les informations d'un conteneur (si spécifié, pour tous sinon),
     * pour une matiere (Verre couleur par defaut)
     * pendant une periode (si pas spécifiée, ou un champs sur deux remplis => 
     * depuis le premier relevé a aujourdhui)
     * 
     * @param string $nConteneur : le numero du conteneur
     * @param string $matiere : la matiere du conteneur que l'on cherche
     * @param date $dateDebut : date de debut de periode de recherche
     * @param date $dateFin : date de fin
     * @return infos sur un ou plusieurs conteneurs
     */
    public function getInfos($nConteneur =null, $matiere ='Verre Couleur', $dateDebut =null, $dateFin =null)
    {
        // retourne les infos sur un conteneur et sa quantite collecte
        // (sans limite de temps)
        $imbriquee = $this->select()->distinct()
                          ->from($this->_name,array('ID_SITE'))
                    ;
        //Zend_Debug::dump($this->fetchAll($imbriquee)->toArray());
        //echo $imbriquee->assemble();exit;

        $req = $this->select()->setIntegrityCheck(false)
                    ->from(array('c'=>$this->_name),array('SUM(QTE_COLLECTE) qte','NO_CONTENEUR', 'NOM_GROUPEMENT', 'NOM_LOCALITE', 'MATIERE', 'VOLUME'))
                    ->join(array('s'=>'t_site'),'c.ID_SITE=s.ID_SITE', '*')
                    ->join(array('co'=>'t_commune'),'co.ID_COMMUNE=s.ID_COMMUNE', 'NOM_COMMUNE')
                    ->where('c.ID_SITE IN (?)', new Zend_Db_Expr($imbriquee))
                    ->where('MATIERE = ?', $matiere)
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
                s.NOM_SITE, s.USED_SITE,
                co.NOM_COMMUNE');
        
        if(!is_null($nConteneur))
        {
            // on a qu'une ligne, on utilise donc fetchRow (pas besoin de tri)
            return $this->fetchRow($req)->toArray();
        }
        else
        {   // on trie du plus grand au plus petit
            $req->order('qte DESC');
            //echo $req->assemble();exit;
            return $this->fetchAll($req)->toArray();
        }
    }
    public function testDate()
    {
        $req = $this->select()
                    ->from($this->_name,'DATE_COLLECTE')
                    ->where('DATE_COLLECTE >= ?', '22-07-2012')
                    ->where('DATE_COLLECTE <= ?', '24-07-2012')
                ;
        Return $this->fetchAll($req)->toArray();
    }
}
