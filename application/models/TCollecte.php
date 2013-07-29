<?php
class TCollecte extends Zend_Db_Table_Abstract
{
    // regle le nom de la table et la clé primaire
    protected $_name = 'T_COLLECTE';    // obligatoire car le nom de la classe ne correspond pas au nom de la table en bdd
    protected $_primary = array('DATE_COLLECTE','NO_CONTENEUR');
   
}
