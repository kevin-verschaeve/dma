<?php
class TSite extends Zend_Db_Table_Abstract
{
    protected $_name = 'T_SITE';
    protected $_primary = array('ID_COMMUNE','ID_SITE');
    
    /**
     * Met l'etat des sites recus en parametre a 0, les autres a 1
     * @param array $idSites
     */
    public function changeEtatStat($idSite, $etat)
    {
        $where = $this->getAdapter()->quoteInto('ID_SITE = ?', $idSite);
        $this->update(array('STAT_SITE' => $etat), $where);
    }
    
    public function getInfos($nSite =null, $matiere ='Verre Couleur', $dateDebut =null, $dateFin =null, $stat =true)
    {
        try {
        $req = $this->select()->setIntegrityCheck(false)
                    ->from(array('s'=>$this->_name),array('ID_SITE','NOM_SITE', 'LOC_SITE'))
                    ->join(array('c'=>'T_COLLECTE'),'c.ID_SITE=s.ID_SITE', array('SUM(QTE_COLLECTE) qte', 'COUNT(*) levees', 'NOM_LOCALITE'))
                    ->where('c.MATIERE = ?', $matiere)
                ;
        
        if($stat)
        {
            $req->where('s.STAT_SITE = ?', 1);
        }
        
        if(!is_null($dateDebut) && !is_null($dateFin) && $dateDebut !="" && $dateFin != "")
        {
            //echo $dateDebut.'<br>'.$dateFin;exit;
            $req->where('c.DATE_COLLECTE >= ? ', $dateDebut)
                ->where('c.DATE_COLLECTE <= ? ', $dateFin);
        }
        
        if(!is_null($nSite))
        {   // on recupere que sur ce conteneur
            $req->where('s.ID_SITE = ?', $nSite);
            //echo $req->assemble();exit;
        }
        $req->group('s.ID_SITE, s.NOM_SITE,s.LOC_SITE, c.NOM_LOCALITE');
        
        if(!is_null($nSite))
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
               
        
        
        $res = $this->fetchAll($req)->toArray();
        //Zend_Debug::dump($res);exit;
        return $res;
        }
        catch(Exception $e) {
            echo $e->getMessage();exit;
        }
    }
    public function getTonnageSite($idCommune)
    {
        try {
        
        $req = $this->select()->setIntegrityCheck(false)
                    ->from(array('s'=>$this->_name),array())
                    ->join(array('c'=>'T_COLLECTE'),'s.ID_SITE=c.ID_SITE',array('SUM(c.QTE_COLLECTE) qte'))
                    ->join(array('p'=>'T_PRESTATAIRE'),'p.NO_CONTENEUR=c.NO_CONTENEUR',array('NOM_EMPLACEMENT'))
                    ->where('s.ID_COMMUNE = ?', $idCommune)
                ;
        
        $req->group('NOM_EMPLACEMENT');
        
         // on trie du plus grand tonnage au plus petit
            $req->order('qte DESC');
            //Zend_Debug::dump($this->fetchAll($req)->toArray());exit;
            return $this->fetchAll($req)->toArray();
        }
        catch(Exception $e)
        {
            echo $e->getMessage();exit;
        }
    }
    public function getSitesCommunes()
    {
        try {
        $req = $this->select()
                    ->setIntegrityCheck(false)
                    ->distinct()
                    ->from(array('s'=>$this->_name), array('NOM_SITE', 'LOC_SITE','ID_SITE','STAT_SITE'))
                    ->join(array('co'=>'T_COMMUNE'),'s.ID_COMMUNE=co.ID_COMMUNE', 'NOM_COMMUNE')
                    ->order('NOM_COMMUNE DESC, ID_SITE ASc')
                ;
        //Zend_Debug::dump($this->fetchAll($req)->toArray());exit;
        return $this->fetchAll($req)->toArray();
        }
        catch(Exception $e)
        {
            echo $e->getMessage();exit;
        }
    }
}