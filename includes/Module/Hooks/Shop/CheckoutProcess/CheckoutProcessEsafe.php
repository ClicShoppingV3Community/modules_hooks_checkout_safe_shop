<?php
/**
 *
 *  @copyright 2008 - https://www.clicshopping.org
 *  @Brand : ClicShopping(Tm) at Inpi all right Reserved
 *  @Licence GPL 2 & MIT
 *  @licence MIT - Portion of osCommerce 2.4
 *  @Info : https://www.clicshopping.org/forum/trademark/
 *
 */

  namespace ClicShopping\OM\Module\Hooks\Shop\CheckoutProcess;

  use ClicShopping\OM\Registry;
  use ClicShopping\OM\CLICSHOPPING;

  class CheckoutProcessEsafe {

    protected $url;

    public function __construct() {
      $CLICSHOPPING_Customer = Registry::get('Customer');

      if (!$CLICSHOPPING_Customer->isLoggedOn()) {
        CLICSHOPPING::redirect(null, 'Account&LogIn');
      }

      $this->url ='https://api.safe.shop/v1/invites';
    }

    private static function getOrderInfo() {
      $CLICSHOPPING_Db = Registry::get('Db');
      $CLICSHOPPING_Customer = Registry::get('Customer');

      $QLastOrder = $CLICSHOPPING_Db->prepare('select orders_id,
                                                      orders_status,
                                                      orders_status_invoice,
                                                      customers_name,
                                                      date_purchased,
                                                      customers_email_address,
                                                      customers_telephone,
                                                      customers_city,
                                                      customers_country,
                                                      customers_id
                                                from :table_orders
                                                where customers_id = :customers_id
                                                order by orders_id desc
                                                limit 1
                                               ');

      $QLastOrder->bindInt(':customers_id', $CLICSHOPPING_Customer->getID());
      $QLastOrder->execute();

      return $QLastOrder->fetch();
    }

    public function execute() {
      $CLICSHOPPING_Language = Registry::get('Language');

      if (isset($_GET['Checkout']) && isset($_GET['Process'])) {
        if (defined('MODULE_HEADER_TAGS_ESAFE_STATUS') && MODULE_HEADER_TAGS_ESAFE_STATUS == 'True') {
          if ( defined('MODULE_HEADER_TAGS_ESAFE_API_SECRETKEY') && defined('MODULE_HEADER_TAGS_ESAFE_SITE_API_CLIENTID') && !empty(MODULE_HEADER_TAGS_ESAFE_API_SECRETKEY) && !empty(MODULE_HEADER_TAGS_ESAFE_SITE_API_CLIENTID)) {
            $language_code = $CLICSHOPPING_Language->getCode();

            $info_last_order = static::getOrderInfo();

            if (!is_null($info_last_order['orders_id'])) {
              $name= explode(' ', $info_last_order['customers_name']);

              $postdata = [
                            'client_secret'=> MODULE_HEADER_TAGS_ESAFE_API_SECRETKEY,
                            'client_id'=> MODULE_HEADER_TAGS_ESAFE_SITE_API_CLIENTID,
                            'grant_type'=>'client_credentials',
                            'scope'=>'invites_readwrite',
                          ];
              $cl = curl_init('https://api.safe.shop/v1/oauth/token');

              curl_setopt_array($cl, [
                                        CURLOPT_POST           => true,
                                        CURLOPT_POSTFIELDS     => $postdata,
                                        CURLOPT_RETURNTRANSFER => true
                                      ]
                                );

              $content = curl_exec($cl);
              curl_close($cl);

              $json   = json_decode($content);
              $atoken = $json->access_token;

              $data = [
                        'send_datetime' => $info_last_order['date_purchased'],
                        'email' => $info_last_order['customers_email_address'],
                        'firstname' => $name[0],
                        'lastname' => $name[1],
                        'telephone' => $info_last_order['customers_telephone'],
                        'gender' => '',
                        'order_id' => $info_last_order['orders_id'],
                        'language' => $language_code,
                        'city' => $info_last_order['customers_city'],
                        'country' => $info_last_order['customers_country'],
                        'segment' => '',
                        'channel' => 'ClicShopping',
                        'customer_id' => $info_last_order['customers_id']
                      ];

              $data_string = json_encode($data);

              $headers = ['Content-Type: application/json',
                           sprintf('Authorization: Bearer %s', $atoken)
                         ];

              $ch = curl_init();
              curl_setopt($ch, CURLOPT_URL, $this->url);
              curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
              curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
              curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

              $result = curl_exec($ch);

              echo $result;
            }
          }
        }
      }
    }
  }

