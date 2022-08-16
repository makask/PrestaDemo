<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class MyBasicModule extends Module{

    public function __construct()
    {
        $this->name = "mybasicmodule";
        $this->tab = "front_office_features";
        $this->version = "1.0";
        $this->author = 'Marko Kask';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('My Basic Module');
        $this->description = $this->l('This is a testing module.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        //if (!Configuration::get('MYMODULE_NAME')) {
        //    $this->warning = $this->l('No name provided');
        //}
    }

    public function install()
    {
        return parent::install() && $this->registerHook('registerGDPRConsent');
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function hookdisplayFooter($params){
        $this->context->smarty->assign([
            'myparamtest' => "Some Text",
            'idcart' => $this->context->cart->id
        ]);
        return $this->display(__FILE__,'views/templates/hook/footer.tpl');
    }
}