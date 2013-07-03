<?php
class TCollecte extends Zend_Db_Table_Abstract
{
    // regle le nom de la table et la clé primaire
    protected $_name = 'T_COLLECTE';    // obligatoire car le nom de la classe ne correspond pas au nom de la table en bdd
    protected $_primary = array('DATE_COLLECTE','NO_CONTENEUR');
   
    /**
     * Retourne les sites qui ont des conteneur de $matiere
     * @param string $matiere : le nom de la matiere cherchée
     * @return array
     */
    public function getSites($matiere)
    {
        $req = $this->select()
                    ->setIntegrityCheck(false)
                    ->distinct()
                    ->from($this->_name, 'ID_SITE')
                    ->join('T_SITE', 'T_SITE.ID_SITE=T_COLLECTE.ID_SITE', array())
                    ->where('T_SITE.'.$matiere.' = ?', 1)
                    ->where('T_COLLECTE.MATIERE = ?', $matiere == 'VERRE' ? 'Verre Couleur' : $matiere)
                    ->order('T_COLLECTE.ID_SITE')
                ;
        // fetchCol car on a qu'une seule colonne 'ID_SITE', les autres fetch sont inutiles
        return $this->_db->fetchCol($req);
    }
}
