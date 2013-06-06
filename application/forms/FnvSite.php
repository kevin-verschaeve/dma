<?php

class FnvSite extends Zend_Form
{
    private $communes;
    
    public function __construct() {
        
        $tcommune = new TCommune;
        $coms = $tcommune->getCommunes(false);
        $lesCommunes = array();
        foreach ($coms as $uneCommune)
        {
            $lesCommunes[$uneCommune['ID_COMMUNE']] = $uneCommune['NOM_COMMUNE'];
        }
        $this->communes = $lesCommunes;
        
        parent::__construct();
    }

    public function init() 
    {
        $view = Zend_Layout::getMvcInstance()->getView();
        
        $this->setAction($view->baseUrl('index/nouveausite'))
             ->setMethod('post')
             ->setName('fnvsite');
        
        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'div', 'id'=>'divfnvsite')),
            array('Form',array('class'=>'zend_form')), // ajoute la class zend_form au formulaire
        ));
        
        $nSite = new Zend_Form_Element_Text('nsite');
        $nSite->setLabel('Attribuer un n° de site : ')
              ->addValidator('digits')
              ->addValidator('greaterThan',false, array('min'=>0))
              ->setRequired(true)
                ;
        
        $nConteneur = new Zend_Form_Element_Text('nconteneur');
        $nConteneur->setLabel('N° du conteneur (prestataire) : ')
                   ->setRequired(true)
                ;
        
        $commune = new Zend_Form_Element_Select('commune');
        $commune->setLabel('Commune : ');
        $commune->addMultiOptions($this->communes);
        
        $adresse = new Zend_Form_Element_Text('adresse');
        $adresse->setLabel('Adresse : ');
        
        $complement = new Zend_Form_Element_Text('complement');
        $complement->setLabel('Complement : ');
        
        $matiere = new Zend_Form_Element_MultiCheckbox('matieres');
        $matiere->setLabel('Type de matière : ')
              ->setMultiOptions(array(
                    'VERRE' => 'Verre',
                    'CORPS_PLATS' => 'Corps Plats',
                    'CORPS_CREUX' => 'Corps Creux'
                ))
            ->setValue('VERRE')
            ->setSeparator('&nbsp;&nbsp;');
        
        $submit = new Zend_Form_Element_Submit('sub_nvsite');
        $submit->setLabel('Ajouter');
        
        $this->addElements(array(
                    $nSite,
                    $nConteneur,
                    $commune,
                    $adresse,
                    $complement,
                    $matiere,
                    $submit
                ));
        
        $this->setElementDecorators(array(
            array('ViewHelper'),
            array('Errors', array('tag' => 'div', 'class' => 'erreurValid')),
            array('Label', array('tag' => 'p')),
            array('HtmlTag', array('tag' => 'div', 'class' => 'blocnvsite'))
         ));
        
    }
}