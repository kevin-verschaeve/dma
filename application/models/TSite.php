<?php
class TSite extends Zend_Db_Table_Abstract
{
    protected $_name = 'T_SITE';
    protected $_primary = array('ID_COMMUNE','ID_SITE');
}