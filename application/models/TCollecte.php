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
                    ->distinct()
                    ->from($this->_name, 'ID_SITE')
                    ->where('MATIERE = ?', $matiere)
                    ->order('ID_SITE')
                ;
        // fetchCol car on a qu'une seule colonne 'ID_SITE', les autres fetch sont inutiles
        return $this->_db->fetchCol($req);
    }
    /**
     * Retourne les differentes matieres que l'on retrouve en base
     * @return array
     */
    public function getMatieres()
    {
        $req = $this->select()->distinct()
                    ->from($this->_name, 'MATIERE')
                ;
        return $this->_db->fetchCol($req);
    }
}
