<?php
/**
* 2007-2022 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2022 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Generalmodule extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'generalmodule';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Marko Kask';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('My General Module');
        $this->description = $this->l('General Module For Learning Purposes!');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        Configuration::updateValue('GENERALMODULE_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('rightColumn') &&
            $this->registerHook('leftColumn') &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader');
    }

    public function uninstall()
    {
        Configuration::deleteByName('GENERALMODULE_LIVE_MODE');

        return parent::uninstall();
    }

    public function getContent()
    {
        if (((bool)Tools::isSubmit('submitGeneralmoduleModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitGeneralmoduleModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'GENERALMODULE_HEADING',
                        'label' => $this->l('Heading'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'GENERALMODULE_CONTENT',
                        'label' => $this->l('Content'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'GENERALMODULE_COLOR',
                        'label' => $this->l('Color'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'GENERALMODULE_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'GENERALMODULE_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'GENERALMODULE_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    protected function getConfigFormValues()
    {
        return array(
            'GENERALMODULE_LIVE_MODE' => Configuration::get('GENERALMODULE_LIVE_MODE', true),
            'GENERALMODULE_ACCOUNT_EMAIL' => Configuration::get('GENERALMODULE_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'GENERALMODULE_ACCOUNT_PASSWORD' => Configuration::get('GENERALMODULE_ACCOUNT_PASSWORD', null),
            'GENERALMODULE_HEADING' => Configuration::get('GENERALMODULE_HEADING', true),
            'GENERALMODULE_CONTENT' => Configuration::get('GENERALMODULE_CONTENT', true),
            'GENERALMODULE_COLOR' => Configuration::get('GENERALMODULE_COLOR', true),
        );
    }

    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    public function hookHeader($params)
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');

        $this->context->smarty->assign([
            'generalmodule_name' => Configuration::get('GENERALMODULE_NAME'),
            'generalmodule_link' => $this->context->link->getModuleLink('generalmodule','display'),
            'generalmodule_heading' => Configuration::get('GENERALMODULE_HEADING', true),
            'generalmodule_content' => Configuration::get('GENERALMODULE_CONTENT', true),
            'generalmodule_color' => Configuration::get('GENERALMODULE_COLOR', true)
        ]);

        return $this->display(__FILE__, 'generalmodule.tpl');
    }

    public function hookDisplayRightColumn($params){
        $this->context->smarty->assign([
            'generalmodule_name' => Configuration::get('GENERALMODULE_NAME'),
            'generalmodule_link' => $this->context->link->getModuleLink('generalmodule','display'),
            'generalmodule_heading' => Configuration::get('GENERALMODULE_HEADING', true),
            'generalmodule_content' => Configuration::get('GENERALMODULE_CONTENT', true),
            'generalmodule_color' => Configuration::get('GENERALMODULE_COLOR', true)
        ]);

        return $this->display(__FILE__, 'generalmodule.tpl');
    }

    public function hookDisplayLeftColumn($params)
    {
        return $this->hookDisplayRightColumn($params);
    }

}
