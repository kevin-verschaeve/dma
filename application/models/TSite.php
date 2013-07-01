<?php
class TSite extends Zend_Db_Table_Abstract
{
    // regle le nom de la table et la clé primaire
    protected $_name = 'T_SITE'; // obligatoire car le nom de la classe ne correspond pas au nom de la table en bdd
    protected $_primary = array('ID_COMMUNE','ID_SITE');
        
    /**
     * Regle le champ STAT_SITE du site $idSite a la valeur $etat
     * @param int $idSites  : le site a mofidier
     * @param int $etat : 0 ou 1, l'etat a regle pour le site
     */
    public function changeEtatStat($idSite, $etat)
    {
        $where = $this->getAdapter()->quoteInto('ID_SITE = ?', $idSite);
        $this->update(array('STAT_SITE' => $etat), $where);
    }
    
    /**
     * recupere les informations d'un site (si spécifié, pour tous sinon),
     * pour une matiere (Verre couleur par defaut)
     * pendant une periode (si pas spécifiée, ou un champs sur deux remplis => 
     * depuis le premier relevé a aujourdhui)
     * le nombre de relevés effectués 
     * le tonnage du ou des conteneur(s)
     * 
     * @param int $nSite : le numero du site
     * @param string $matiere : la matiere du conteneur que l'on cherche
     * @param date $dateDebut : date de debut de periode de recherche
     * @param date $dateFin : date de fin
     * @param bool $stat : prendre en compte ou non l'etat du site
     * @return infos sur un ou plusieurs site
     */
    public function getInfos($nSite =null, $matiere ='VERRE', $dateDebut =null, $dateFin =null, $stat =true)
    {
        $req = $this->select()->setIntegrityCheck(false) // pour pouvoir faire une jointure
                    ->from(array('s'=>$this->_name),array('ID_SITE','NOM_SITE', 'LOC_SITE'))
                    ->join(array('c'=>'T_COLLECTE'),'c.ID_SITE=s.ID_SITE', array('SUM(QTE_COLLECTE) qte', 'COUNT(*) levees', 'NOM_LOCALITE'))
                    ->where('s.'.$matiere.' = ?', 1)
                ;
        if($matiere == 'VERRE') {
            $matiere = 'Verre Couleur';
        }
        $req->where('c.MATIERE =?', $matiere);
        
        if($stat) {
            $req->where('s.STAT_SITE = ?', 1);
        }
        
        // on veut une periode
        if(!is_null($dateDebut) && !is_null($dateFin) && $dateDebut !="" && $dateFin != "")
        {
            // on ajoute la restriction sur la date
            $req->where('c.DATE_COLLECTE >= ? ', $dateDebut)
                ->where('c.DATE_COLLECTE <= TO_DATE(?, \'DD/MM/YYYY HH24:MI:SS\') ', $dateFin.' 23:59:59');
        }
        
        // on veut les infos sur un seul site
        if(!is_null($nSite))
        {
            // on recupere que sur ce conteneur
            $req->where('s.ID_SITE = ?', $nSite);
            //echo $req->assemble();exit;
        }
        // GROUP BY
        $req->group('s.ID_SITE, s.NOM_SITE,s.LOC_SITE, c.NOM_LOCALITE');
        
        if(!is_null($nSite))
        {
            // on a qu'une ligne, on utilise donc fetchRow (pas besoin de tri)
            $res = $this->fetchRow($req);
            // si $res contient qqch, on le retourne, sinon on retourne null
            return $res ? $res->toArray() : null;
        }
        else
        {   // on trie du plus grand tonnage au plus petit (sers pour le tableau, si on change ici le comptage dans le tableau affiché ne sera plus cohérent)
            $req->order('qte DESC');
            //echo $req->assemble();exit;
            return $this->fetchAll($req)->toArray();
        }
    }
    /**
     * Retourne le nom et le tonnage de chaque site dans la commune $idCommune
     * @param int $idCommune : la commune recherchée
     */
    public function getTonnageSite($idCommune, $matiere)
    {
        $req = $this->select()->setIntegrityCheck(false)    // pour pouvoir faire une jointure
                    ->from(array('s'=>$this->_name),array('s.NOM_SITE', 's.LOC_SITE'))
                    ->join(array('c'=>'T_COLLECTE'),'s.ID_SITE=c.ID_SITE',array('SUM(c.QTE_COLLECTE) qte, count(*) as nb'))
                    ->where('s.ID_COMMUNE = ?', $idCommune)
                    ->where('s.STAT_SITE = ?', 1)
                    ->where('s.'.$matiere.' = ?', 1)
                ;
        
        if($matiere == 'VERRE') {
            $matiere = 'Verre Couleur';
        }
        $req->where('c.MATIERE = ?', $matiere)
            ->group('s.NOM_SITE, s.LOC_SITE')
            ->order('qte DESC');
        
        $res = $this->fetchAll($req)->toArray();
        return $res;
    }
    /**
     * Le total du tonnage de tous les sites pour chaque commune
     */
    public function getTonnageCommunes($matiere)
    {
        $req = $this->select()->setIntegrityCheck(false)
                    ->from(array('s'=>$this->_name),array())
                    ->join(array('c'=>'T_COLLECTE'),'s.ID_SITE=c.ID_SITE',array('SUM(c.QTE_COLLECTE) qte'))
                    ->join(array('co'=>'T_COMMUNE'),'s.ID_COMMUNE=co.ID_COMMUNE',array('NOM_COMMUNE'))
                    ->where('STAT_SITE = ?', 1)
                    ->where('s.'.$matiere.' = ?', 1)
                ;
        if($matiere == 'VERRE') {
            $matiere = 'Verre Couleur';
        }
        $req->where('c.MATIERE = ?', $matiere)
            ->group('s.ID_COMMUNE, co.NOM_COMMUNE')
            ->order('qte DESC');
        
        return $this->fetchAll($req)->toArray();                    
    }
    /**
     * Les informations de tous les sites de toutes les communes
     */
    public function getSitesCommunes()
    {
        $req = $this->select()
                    ->setIntegrityCheck(false)  // pour pouvoir faire une jointure
                    ->distinct()
                    ->from(array('s'=>$this->_name), array('NOM_SITE', 'LOC_SITE','ID_SITE','STAT_SITE'))
                    ->join(array('co'=>'T_COMMUNE'),'s.ID_COMMUNE=co.ID_COMMUNE', 'NOM_COMMUNE')
                    ->order('NOM_COMMUNE DESC, ID_SITE ASC')
                ;
        return $this->fetchAll($req)->toArray();
     }
     /**
      * Ajouter un site
      * @param array : $donnees tableau de données a inserer (nomDuChamp => valeurDuChamp)
      */
     public function ajouter($donnees)
     {
         return $this->insert($donnees);
     }
}