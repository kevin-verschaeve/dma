<?php

class TDataCollecte  extends Zend_Db_Table_Abstract
{
    protected $_name = 'T_DATA_COLLECTE';    
    // ce n'est pas la vrai clé primaire
    // mais la table n'en a pas, on en met quand meme une pour respecter les contraintes de zend
    protected $_primary = 'C_DATE';
    
    /**
     * ajoute la ligne dans la base
     */
    public function inserer($ligne)
    {
        $this->insert($ligne);
    }
    /**
     * Vide la table
     * @return nombre de lignes supprimées
     */
    public function videTable()
    {
        // condition toujours vraie pour supprimer toutes les lignes de la table
        return $this->delete('1 = 1');
    }
    public function supprime($mois, $annee) {
        $debut = '01/0'.$mois.'/'.$annee;
        $nbjour = date('t', mktime(0, 0, 0, $mois, 1, $annee));
        $fin = $nbjour.'/'.$mois.'/'.$annee;
        //echo $debut;exit;
        
        $where[] = $this->getAdapter()->quoteInto('C_DATE >= ?', $debut);
        $where[] = $this->getAdapter()->quoteInto('C_DATE <= TO_DATE(?, \'DD/MM/YYYY\')', $fin);
        $this->delete($where);
    }
    
}
