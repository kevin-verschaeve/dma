<?php
class TCollecte extends Zend_Db_Table_Abstract
{
    protected $_name = 'T_COLLECTE';
    protected $_primary = array('DATE_COLLECTE','NO_CONTENEUR');
   
    public function getSites($matiere)
    {
        $req = $this->select()
                    ->distinct()
                    ->from($this->_name, 'ID_SITE')
                    ->where('MATIERE = ?', $matiere)
                    ->order('ID_SITE')
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
}
