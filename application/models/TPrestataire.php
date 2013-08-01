<?php

class TPrestataire  extends Zend_Db_Table_Abstract
{
    protected $_name = 'T_PRESTATAIRE';
    protected $_primary = array('ID_COMMUNE','ID_SITE');
    
    /**
     * ajoute la ligne dans la base
     */
    public function ajouterConteneur($donnees)
    {
        return $this->insert($donnees);
    }
    public function existe($nCont) {
        $req = $this->select()
                    ->from($this->_name, 'NO_CONTENEUR')
                    ->where('NO_CONTENEUR = ?', $nCont)
                ;
        $res = $this->_db->fetchOne($req);
        return $res ? true : false;
    }
}