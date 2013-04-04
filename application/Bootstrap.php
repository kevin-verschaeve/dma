<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    public function run()
    {
        Zend_Registry::set('config', new Zend_Config($this->getOptions()));
        parent::run();
    }
    // recupere les infos de application.ini, et les met dans le registre
    protected function _initConfig()
    {
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/application.ini', APPLICATION_ENV);
        Zend_Registry::set('config', $this->_config);
        Zend_Registry::set('env', APPLICATION_ENV);
    }
    // regle la bdd et la connexion avec le .ini
    protected function _initDB()
    {
        $db = Zend_Db::factory($this->_config->db); 
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
        Zend_Registry::set('db', $db);
    }
    protected function _initLoader() 
    {
        // autoloader, permet de charger toutes les classes que l'on crééra (models, forms..)
        require_once 'Zend/Loader/Autoloader.php';
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->setFallbackAutoloader(true);
    }
    // permet la traduction en francais des messages d'erreur
    public function _initTranslator()
    {
        $translator = new Zend_Translate(array(
                'adapter' => 'array',
                'content' => APPLICATION_PATH. '\..\resources\languages',
                'locale'  => 'fr',
                'scan' => Zend_Translate::LOCALE_DIRECTORY
            )
        );
        Zend_Validate_Abstract::setDefaultTranslator($translator);
    }
}

