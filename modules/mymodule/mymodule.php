<?php
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

if(!defined('_PS_VERSION_')){
    exit;
}

class Mymodule extends Module implements WidgetInterface
{
    public function __construct()
    {
        $this->name = 'mymodule';
        $this->tab = 'front_office_features';
        $this->version = '2.1.1';
        $this->author = 'Marko Kask';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.7.1.0', 'max' => _PS_VERSION_);

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Udemy module for Prestashop 1.7');
        $this->description = $this->l('A module created for the purpose of Udemy course');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall');

        if(!Configuration::get('MYMODULE_NAME')){
            $this->warning = $this->l('No name provided');
        }
    }
    /**
     * @return bool
     * @throws PrestaShopException
     * */
    public function install()
    {
        /**
         * create new Hook
         */
        //$hook = new Hook();
        //$hook->name = 'displayAtSpecificPlace';
        //$hook->title = 'The name of your hook';
        //$hook->description = 'This is a custom hook!';
        //$hook->add(); // return true on success

        // Check that Multistore feature is enabled, and if so, set the current context
        // to all shops on this installation of Prestashop.
        if (Shop::isFeatureActive()){
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        // Check that the module parent class is installed.
        // Check that the module can be attached to the leftColumn hook.
        // Check thet the module can be attached to the header hook.
        // Create the MYMODULE_NAME configuration setting, setting its value to "my friend"
        if(!parent::install() ||
            !$this->registerHook('displayAtSpecificPlace') ||
            !$this->registerHook('rightColumn') ||
            !$this->registerHook('header') ||
            !Configuration::updateValue('MYMODULE_NAME', 'my friend')
        ) {
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        // Storing a serialized array.
        Configuration::updateValue('MYMODULE_SETTINGS', serialize(array(true, true, false)));

        // Retrieving the array.
        $configuration_array = unserialize(Configuration::get('MYMODULE_SETTINGS'));

        if(!parent::uninstall() ||
            // database remove table, remove physical file ...
            !Configuration::deleteByName('MYMODULE_NAME')){
            return false;
        }
        return true;
    }

    // {widget name = "mymodule"}
    public function hookHeader($params){
        //print_r($params);
        //return "Hello from " . Configuration::get('MYMODULE_NAME');

    }

    // Hook::exec("mymodule");
    public function renderWidget($hookName, array $configuration)
    {
        return "Hello from " . Configuration::get('MYMODULE_NAME');
    }

    public function getWidgetVariables($hookName, array $configuration)
    {
        // TODO: Implement getWidgetVariables() method.
    }

    /**
     * Get configuration page
     */
    public function getContent(){
        $message = "";
        $configuration = Configuration::get('MYMODULE_NAME');

        if(Tools::getValue('MYMODULE_NAME')){
            if(Validate::isGenericName(Tools::getValue('MYMODULE_NAME'))) {
                if (Configuration::updateValue('MYMODULE_NAME', Tools::getValue('MYMODULE_NAME'))){
                    $configuration = Tools::getValue('MYMODULE_NAME');
                    $message = $this->displayConfirmation("All went well");
                }else{
                    $message = $this->displayError("Something went wrong");
                }
            }else{
                echo "Something went wrong with the input value";
            }
        }
        return $message . $this->displayForm();


        //$confVar = Configuration::get('MYMODULE_NAME');

    }

    public function displayForm(){

        // Init fields form array
        $fieldsForm[0]['form'] = [
            'legend' => [
                'title' => $this->l('Settings of my module'),
            ],
            'input' => [
                'type' => 'text',
                'label' => $this->l('Configuration value'),
                'name' => 'MYMODULE_NAME',
                'size' => 20,
                'required' => true
            ],
            'submit' => [
                'title' => $this->l('Save the changes'),
                'class' => 'btn btn-default pull-right'
            ]
        ];

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        // Load current value
        $helper->fields_value['MYMODULE_NAME'] = Configuration::get('MYMODULE_NAME');
        return $helper->generateForm($fieldsForm);
    }
}