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
        $this->insert($donnees);
    }
}