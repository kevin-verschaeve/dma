<?php
class TCollecte extends Zend_Db_Table_Abstract
{
    protected $_name = 't_collecte';
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
                    ->from(array('c'=>$this->_name),array('SUM(QTE_COLLECTE) as qte','NO_CONTENEUR', 'NOM_GROUPEMENT', 'NOM_LOCALITE', 'MATIERE', 'VOLUME'))
                    ->join(array('s'=>'t_site'),'c.ID_SITE=s.ID_SITE', '*')
                    ->join(array('co'=>'t_commune'),'co.ID_COMMUNE=s.ID_COMMUNE', 'NOM_COMMUNE')
                    ->where('c.ID_SITE IN (?)', new Zend_Db_Expr($imbriquee))
                    ->where('MATIERE = ?', $matiere)
                ;
        // si on a specifié une periode
        if(!is_null($dateDebut) && !is_null($dateFin) && $dateDebut !=null && $dateFin != null)
        {
            $req->where('c.DATE_COLLECTE >= ? ', $dateDebut)
                ->where('c.DATE_COLLECTE <= ? ', $dateFin);
        }

        // si on a specifié un conteneur particulier
        if(!is_null($nConteneur))
        {   // on recupere que sur ce conteneur
            $req->where('c.NO_CONTENEUR = ?', $nConteneur);
            // on a qu'une ligne, on utilise donc fetchRow
            //echo $req->assemble();exit;
            return $this->fetchRow($req)->toArray();
        }
        else
        {   // on recupere sur tous les conteneurs et on group
            $req->group('c.NO_CONTENEUR')
                ->order('qte DESC');
            return $this->fetchAll($req)->toArray();
        }
    }
}
