<?php

class FMatiere extends Zend_Form 
{
    public function init()
    {
        $this->setAction('/palmares/index')
              ->setMethod('post')
              ->setName('Fmatiere');
        
        $radio = new Zend_Form_Element_Radio('radioMatiere');
        $radio->setLabel('Type de matière')
              ->setMultiOptions(array(
                    'v' => 'Verre',
                    'pc' => 'Papier/Carton',
                    'cc' => 'Corps Creux'
             ))
            ->setValue('v')
            ->setSeparator('');
        $radio->setAttrib('class', 'rad');
        

        $this->addElement($radio);
    }
}