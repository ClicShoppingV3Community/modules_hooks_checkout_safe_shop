<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @licence MIT - Portion of osCommerce 2.4
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */

  use ClicShopping\OM\HTML;
  use ClicShopping\OM\Registry;
  use ClicShopping\OM\CLICSHOPPING;
  use ClicShopping\OM\HTTP;

  class ht_esafe
  {
    public $code;
    public $group;
    public $title;
    public $description;
    public $sort_order;
    public $enabled = false;

    public function __construct()
    {
      $this->code = get_class($this);
      $this->group = basename(__DIR__);
      $this->title = CLICSHOPPING::getDef('module_header_tags_esafe_title');
      $this->description = CLICSHOPPING::getDef('module_header_tags_esafe_description');

      if (defined('MODULE_HEADER_TAGS_ESAFE_STATUS')) {
        $this->sort_order = MODULE_HEADER_TAGS_ESAFE_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_ESAFE_STATUS == 'True');
      }
    }

    public function execute()
    {

      $CLICSHOPPING_Template = Registry::get('Template');

      if (defined('MODULE_HEADER_TAGS_ESAFE_API_SECRETKEY') && defined('MODULE_HEADER_TAGS_ESAFE_SITE_API_CLIENTID') && !empty(MODULE_HEADER_TAGS_ESAFE_API_SECRETKEY) && !empty(MODULE_HEADER_TAGS_ESAFE_SITE_API_CLIENTID)) {

        $footer_tag = '<!-- Safe.shop start -->';
        $footer_tag .= '<script src="https://merchant.safe.shop/widget.js"></script>';
        $footer_tag .= '<!-- Safe.shop stop -->';

        $CLICSHOPPING_Template->addBlock($footer_tag, 'footer_scripts');
      }
    }

    public function isEnabled()
    {
      return $this->enabled;
    }

    public function check()
    {
      return defined('MODULE_HEADER_TAGS_ESAFE_STATUS');
    }

    public function install()
    {
      $CLICSHOPPING_Db = Registry::get('Db');


      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Do you want to install this module ?',
          'configuration_key' => 'MODULE_HEADER_TAGS_ESAFE_STATUS',
          'configuration_value' => 'True',
          'configuration_description' => 'Do you want to install this module ?',
          'configuration_group_id' => '6',
          'sort_order' => '1',
          'set_function' => 'clic_cfg_set_boolean_value(array(\'True\', \'False\'))',
          'date_added' => 'now()'
        ]
      );

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Api Client ID',
          'configuration_key' => 'MODULE_HEADER_TAGS_ESAFE_SITE_API_CLIENTID',
          'configuration_value' => '',
          'configuration_description' => 'Api Client ID safe.shop',
          'configuration_group_id' => '6',
          'sort_order' => '2',
          'set_function' => '',
          'date_added' => 'now()'
        ]
      );

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Api Secret Key',
          'configuration_key' => 'MODULE_HEADER_TAGS_ESAFE_API_SECRETKEY',
          'configuration_value' => '',
          'configuration_description' => 'Api Secret Key safe.shop',
          'configuration_group_id' => '6',
          'sort_order' => '3',
          'set_function' => '',
          'date_added' => 'now()'
        ]
      );

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Sort Order',
          'configuration_key' => 'MODULE_HEADER_TAGS_ESAFE_SORT_ORDER',
          'configuration_value' => '150',
          'configuration_description' => 'Sort order. Lowest is displayed in first',
          'configuration_group_id' => '6',
          'sort_order' => '130',
          'set_function' => '',
          'date_added' => 'now()'
        ]
      );
    }

    public function remove()
    {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }

    public function keys()
    {
      return array('MODULE_HEADER_TAGS_ESAFE_STATUS',
        'MODULE_HEADER_TAGS_ESAFE_SITE_API_CLIENTID',
        'MODULE_HEADER_TAGS_ESAFE_API_SECRETKEY',
        'MODULE_HEADER_TAGS_ESAFE_SORT_ORDER'
      );
    }
  }
