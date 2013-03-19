<?php

class FImport extends Zend_Form 
{
    public function init()
    {
        $this->setAction('/index/importer')
              ->setMethod('post')
              ->setName('Fimport');
       
        
        $fichier = new Zend_Form_Element_File('input_fichier');
        $fichier->setAttrib('enctype', 'multipart/form-data');
        $fichier->setLabel('Importer : ');
        $fichier->setDestination(TEMP_PATH);
        $fichier->setRequired(true);
        
        $submit = new Zend_Form_Element_Submit('sub_fichier');
        $submit->setLabel('Envoyer');
        
        $this->addElements(array($fichier, $submit));
        
    }
}
