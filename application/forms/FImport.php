<?php

class FImport extends Zend_Form 
{
    public function init()
    {
        $this->setAction('/index/importer')
              ->setMethod('post')
              ->setName('Fimport');
         $this->setDecorators(array(
             'File',
             'FormElements',
             array('Form',array('class'=>'zend_form')),
         ));
        $fichier = new Zend_Form_Element_File('input_fichier');
        $fichier->setAttrib('enctype', 'multipart/form-data');
        $fichier->setLabel('Importer : ');
        $fichier->setAttrib('style', 'width:300px;');
        $fichier->setRequired(true);
        
        $submit = new Zend_Form_Element_Submit('sub_fichier');
        $submit->setLabel('Envoyer');
        $submit->setAttrib('class', 'bt_submit');
        
        $this->addElements(array($fichier, $submit));
        
    }
}
