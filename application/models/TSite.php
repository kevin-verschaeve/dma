<?php
class TSite extends Zend_Db_Table_Abstract
{
    protected $_name = 'T_SITE';
    protected $_primary = array('ID_COMMUNE','ID_SITE');
    
    /**
     * Met l'etat des sites recus en parametre a 0, les autres a 1
     * @param array $idSites
     */
    public function changeEtatStat($idSites)
    {
        // ceux qui n'ont pas été cochés sont mis a 0
        $where = $this->getAdapter()->quoteInto('ID_SITE NOT IN (?)', $idSites);
        $this->update(array('STAT_SITE' => 0), $where);
        
        // les autres sont mis a 1
        $where = $this->getAdapter()->quoteInto('ID_SITE IN (?)', $idSites);
        $this->update(array('STAT_SITE' => 1), $where);
    }
}