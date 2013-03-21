<?php

class TDataCollecte  extends Zend_Db_Table_Abstract
{
    protected $_name = 'T_DATA_COLLECTE';
    
    // ce n'est pas la vrai clé primaire
    // mais la table n'en a pas, on en met quand meme une pour respecter les contraintes de zend
    protected $_primary = 'C_DATE';
    
    public function inserer($ligne)
    {
        $this->insert($ligne);
    }
    public function videTable()
    {
        return $this->delete('1=1');
    }
}
