<?php
class TCommune extends Zend_Db_Table_Abstract
{
    protected $_name = 'T_COMMUNE';
    protected $_primary = array('ID_COMMUNE');
    
    /**
     * retourne les differentes communes
     */
    public function getCommunes($stat)
    {
        $req = $this->select()->setIntegrityCheck(false)
                    ->from($this->_name,'*')
                    ->join(array('s'=>'T_SITE'), 's.ID_COMMUNE=T_COMMUNE.ID_COMMUNE', array())
                ;
        if($stat) 
            $req->where('s.STAT_SITE = ?',1);
        
        return $this->fetchAll($req)->toArray();
    }
}
    