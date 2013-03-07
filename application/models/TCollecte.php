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
    /**
     * avoir le tonnage d'un conteneur pour une periode et suivant la matiere
     * @param int $idSite : id du site ou se trouve le conteneur souhaité
     * @param string $matiere : la matiere que l'on cherche
     * @param date $dateDebut : debut de la periode pour laquelle on veut les infos
     * @param date $dateFin : fin de la periode
     * @return float le tonnage pour le conteneur pendant la periode
     */
    public function getTonnage($idSite,$matiere ='Verre Couleur', $dateDebut =null, $dateFin =null)
    {
        $req = $this->select()
                    ->from($this->_name,'sum(QTE_COLLECTE) as Tonnage')
                    ->where('ID_SITE = ?', $idSite)
                    ->where('MATIERE = ?',$matiere)
                ;
        if(!is_null($dateDebut) && !is_null($dateFin))
        {
            $req->where('DATE_COLLECTE >= ? ', $dateDebut)
                ->where('DATE_COLLECTE <= ? ', $dateFin);
        }
        echo $req->assemble();
        return $this->_db->fetchOne($req);
    }
    /**
     * retourne tous les Id differents par matiere
     * @return array de int
     */
    public function getDistinctID($matiere ='Verre Couleur')
    {
        $req = $this->select()->distinct()
                    ->from($this->_name,'ID_SITE')
                    ->where('MATIERE = ?', $matiere)
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
    
    
    
    public function test($nConteneur =null, $dateDebut =null, $dateFin =null)
    {
        // retourne les infos sur un conteneur et sa quantite collecte
        // (sans limite de temps)
        try {
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
                    ;
            // si on a specifié une periode
            if(!is_null($dateDebut) && !is_null($dateFin))
            {
                $req->where('c.DATE_COLLECTE >= ? ', $dateDebut)
                    ->where('c.DATE_COLLECTE <= ? ', $dateFin);
            }
            
            
            // si on a specifié un conteneur particulier
            if(!is_null($nConteneur))
            {   // on recupere que sur ce conteneur
                $req->where('c.NO_CONTENEUR = ?', $nConteneur);
                // on a qu'une ligne, on utilise donc fetchRow
                return $this->fetchRow($req);
            }
            else
            {   // on recupere sur tous les conteneurs et on group
                $req->group('c.NO_CONTENEUR');
                return $this->fetchAll($req)->toArray();
            }

        }
        catch (Exception $e)
        {
            echo $e->getMessage();exit;
        }
    }
}
