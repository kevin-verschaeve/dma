<?php
class TSite extends Zend_Db_Table_Abstract
{
    protected $_name = 't_site';
    protected $_primary = array('ID_COMMUNE','ID_SITE');
    
    
    public function getInfosSite($idsite)
    {
        $req = $this->select()->setIntegrityCheck(false)
                    ->from($this->_name,'*')
                    ->join('t_commune','t_commune.ID_COMMUNE=t_site.ID_COMMUNE', 'NOM_COMMUNE')
                    ->where('ID_SITE = ?', $idsite)
                ;
        return $this->_db->fetchRow($req);
    }
}