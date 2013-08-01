<?php
class TCollecte extends Zend_Db_Table_Abstract
{
    // regle le nom de la table et la clé primaire
    protected $_name = 'T_COLLECTE';    // obligatoire car le nom de la classe ne correspond pas au nom de la table en bdd
    protected $_primary = array('DATE_COLLECTE','NO_CONTENEUR');
   
    
    public function getLignes($mois, $annee) {        
        $date_import = $this->getDateImport($mois, $annee);
        
        $req = $this->select()
                    ->setIntegrityCheck(false)
                    ->from($this->_name, array('*'))
                    ->where('DATE_IMPORT = ? ', $date_import)
                    ->order('DATE_COLLECTE ASC')
                ;
        return $this->fetchAll($req)->toArray();        
    }
    public function supprime($mois, $annee) {
        $date_import = $this->getDateImport($mois, $annee);
        
        $where = $this->getAdapter()->quoteInto('DATE_IMPORT = ? ', $date_import);
        return $this->delete($where);
    }
    
    private function getDateImport($mois, $annee) {
        $debut = '01/'.$mois.'/'.$annee;
        $nbjour = date('t', mktime(0, 0, 0, $mois, 1, $annee));
        $fin = $nbjour.'/'.$mois.'/'.$annee;
        
        $req = $this->select()
                    ->distinct()
                    ->from($this->_name, 'DATE_IMPORT')
                    ->where('DATE_COLLECTE BETWEEN \''.$debut.'\' AND \''.$fin.'\' ')
                ;
        return $this->_db->fetchOne($req);
    }
}
