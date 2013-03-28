<?php
class TCommune extends Zend_Db_Table_Abstract
{
    protected $_name = 'T_COMMUNE';
    protected $_primary = array('ID_COMMUNE');
    
    public function getCommunes()
    {
        $req = $this->select()
                    ->from($this->_name,'*')
                ;
        return $this->fetchAll($req)->toArray();
    }
}
    