<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
   
 

    // $app = new Slim\App($config);
    // $container = $app->getContainer();


 
    $app->get("/vn_catch_payment", function(Request $request, Response $response, $arg) {
        echo "IM IN THE MATRIX";
        print_r($_REQUEST);die();
   
        return $response->write($result);
    });
    $app->post("/vncallback", function(Request $request, Response $response, $arg) {
        echo "IM IN THE MATRIX BUT INNN";
        print_r($_REQUEST);
        $response_info  =  vn_manage_response_payment($_REQUEST);
        header("location:". $response_info->redirect_uri."?".http_build_query($_REQUEST));
        
        return $response->write($result);
    });



    // Render Twig template in route
    $app->get('/card-manager/{patient_id}/{order_id}', function ($request, $response, $args) {
                
        // print_r($_REQUEST);die();
        $patient_id = $args['patient_id'];
        $order_id = $args['order_id'];
        $count_cards = count(( new Webservice())->get_cards($patient_id));
        $create_token = false;
       
        if($count_cards <= 0 || isset($_GET['add_card']) && !empty($_GET['add_card'])) {
            $cards = [];
        } else {
            $cards = ( new Webservice())->get_preferred($patient_id);
        }

        if(isset($_GET['add_card']) && !empty($_GET['add_card'])) $create_token = true;
        
        $link = "";
        if(isset($_GET['reason_code']) && !empty($_GET['reason_code']) && $_GET['reason_code'] == '481') {
            $link = "https://megusta.do/webservice/card-manager/".$patient_id.'/'.$order_id;
        }
        if(isset($_GET['req_transaction_type']) && !empty($_GET['req_transaction_type']) && $_GET['req_transaction_type'] == 'create_payment_token') {
            $link = "https://megusta.do/webservice/vn_client/".$patient_id;
        }

        $amount = @(new Webservice())->getamountbyorder($order_id)->amount;
        $amount = !!$amount ? $amount : "0.00";

        // print_r($amount);die();
        // $_GET
        // print_r($d);die();
        $data = payment_patient($patient_id, $cards, $create_token, $order_id, $amount);
 
        // die();
        $showForm = true;
        $hasError = false;
        $info = "";
        if (isset($_GET['req_merchant_defined_data28'])) {
            $info = explode('::', $_GET['req_merchant_defined_data28']);
        }
        
        if($_GET) {
            if( isset($_GET['req_merchant_defined_data1'])) {
                $showForm = false;
            
                if ($_GET['decision'] != 'ACCEPT') {
                
                    $showForm = true;
                    $hasError = true;
                
                }
            }
        }
        $list_cards =   $cards;
        $cards = $list_cards;

        $args['data']= $data;
        $args['list_cards']=$list_cards;
        $args['showForm']= [];
        $args['hasError']= [];
        $args['cards']= [];
        $args['link']= $link;
        $args['amount']= number_format($amount,2);
        $args['gett'] = $_GET;
        $args['is_valid_hash'] = true;
        
        return $this->view->render($response, 'card-manager.html', $args);
    })->setName('profile');

    $app->get("/vn_client/{patient_id}", function(Request $request, Response $response, $arg) {
        return $this->view->render($response, 'client-card-manager.html', $arg);
    })->setName('profile');
    $app->get("/vn_client/{patient_id}/{time}", function(Request $request, Response $response, $arg) {
        return $this->view->render($response, 'client-card-manager-parent.html', $arg);
    })->setName('profile');

    function process481($data) {

     
        $d = $data['req_merchant_defined_data28']; // TENTATIVO <- Reservar el merchant defined data
        // REFERENCE: $data['key'].':'.$data['value'].':'.$data['url_return_web'].":". $data['appointment_id'].":".$data['card_id'],
        $info = explode('::', $d);
       
        $id   = $info[0];
        $type = $info[1];
        $url_web_return = $info[2];
        $appointment_id = $info[3];
        $card = $info[4];
        $order = $info[5];
        $req_transaction_type = $data['req_transaction_type'];
        $c = (new Webservice())->gateway(null, true);
        
        
        
        $request_id = $data['transaction_id'];
        $amount     = $data['req_amount'];
        $reference     = $data['req_reference_number'];
        // print_r($order);die();
        // perform the reversal
        try{
            $c->reverse_authorization( $request_id, $amount, $reference );

        } catch (Exception $e){
            
        }
        // print_r($c);die();
        
    }

     function getredemptionCode($length=10)
	{
		list($usec, $sec) = explode(' ', microtime());
  		mt_srand((float) $sec + ((float) $usec * 100000));
  		$min = str_pad('1',$length,'0') * 1;
  		$max = str_pad('9', $length, '9') * 1;
  		
  		// $exists = 1;
		// while($exists)
		// {
		// 	$code = mt_rand($min, $max);
		// 	$exists = MDealCoupon::model()->exists('redemptionCode=?',array($code));
		// }
  		return $code = mt_rand($min, $max);;
	}
	
    function vn_manage_response_payment($data) {
        // print_r($data);
        $d = $data['req_merchant_defined_data28']; // TENTATIVO <- Reservar el merchant defined data
        // REFERENCE: $data['key'].':'.$data['value'].':'.$data['url_return_web'].":". $data['appointment_id'].":".$data['card_id'],
        $info = explode('::', $d);

        $id   = $info[0];
        $type = $info[1];
        $url_web_return = $info[2];
        $appointment_id = $info[3];
        $card_id = $info[4];
        $order = $info[5];
        $req_transaction_type = $data['req_transaction_type'];
        
        if ($data['reason_code'] == '481') {
            process481($data);
            // die('481');
        }
        if ($data['decision'] == 'ACCEPT') {
            if ($type == 'patient') {
                
                // if transaction type includes create_payment_token
                if (strpos( $data['req_transaction_type'] ,'create_payment_token') !== false) {
                    $cards = (new Webservice())->get_cards($id);
                   
                    $preferred = 0;
                    if (!$cards) {
                        $preferred = 1;
                    }
                    $card_id =  (new Webservice())->add_card_c([
                        "patient_id" => $id,
                        'subscription_id' => $data['payment_token'],	
                        'card_hash' => $data['req_card_number'],	
                        'preferred' => $preferred,
                        'type' => $data['req_card_type']
                    ]);
                  
                }

                if (strpos( $data['req_transaction_type'] ,'sale') !== false) {
                   
                    (new Webservice())->validPayment($order, $data['transaction_id']);
                   
                   
                    $items = (new Webservice())->getoreritems($order);
        

                    foreach ($items as $key => $value) {
                        $now = date('Y-m-d H:i:s',time());      
                        $hash =  (new Webservice())->generateHash();
                        $redemptionCode = getredemptionCode(10);
                        $created =$now;
                        $orderId =$order;
                        $userId =$id;
                        (new Webservice())->generateCoupon([
                            'orderId' => $orderId,
                            'dealId' => $value->itemId,
                            'userId' => $userId,
                            'status' => '1',
                            'userStatus' => '1',
                            'hash' => $hash,
                            'redemptionCode' => $redemptionCode,
                            'created' => $created,
        
                        ]);
                        (new Webservice())->updatePurchaseCounter($value->itemId);
                        
                    }
                    //clean a
                    session_start();  
                    unset($_SESSION['payment.cart']);
                    

                    // if transaction type includes sale
                    // $transaction_id = $this->VN_Patient_Transactions_Model->register_transaction([
                    //     'status' => $data['decision'],
                    //     'appointment_id' => $appointment_id,	
                    //     'orders' => $orders,
                    //     'doctor_id' => $doctor_id,
                    //     'method_payment' => 'card',
                    //     'card_id' => $card_id,	
                    //     'amount' => $data['req_amount'],	
                    //     'patient_id' => $id,
                    //     'source' => '',
                    //     'transaction_info' => json_encode($data)
                    // ]);
                    // $this->VN_Patient_Transactions_Model->pay_appointment($appointment_id);
                    // $this->sendConfirmationEmail([
                    //     'transaction_id' => $data['transaction_id'],
                    //     'appointment' => $this->getappinfo($appointment_id),	
                    //     'card_hash' => $data['req_card_number'],	
                    //     'amount' => 'DOP $'.$data['req_amount'],	
                    // ]);
                }
            }
        }
 
        // Include redirect_uri field in response
        return (object)[
            'redirect_uri' => $url_web_return
        ];
    }


    function visanet_vars() {
        return (object)[
            'version' => '0.1',
            'name' => 'Visanet e-commerce',
            'org_dev'=> '1snn5n9w',
            'org_live'=> 'k8vif92e',
            'secret_key' => 'a2f5de821ca849418cc151b5ee43fbddb1642ebd7c9c4bb2909452f5614aedaafa74a1b7abdd49ec824b22f1ce16aa5d4c6a5faa90254c479dd582dd62bc3d30a6e59f0351d949dc9eb05dcbee3e9dcb734884e2fc734a7787157e1306057f3bc8b31d267e5346139562ca503510faefbc5dea5ae11d48e598c891279772120e',
            'access_key'=> 'c542e1a45b0c33fc991190fd22d5fd79',
            'profile_id'=> 'B0397FE6-D1B7-4C00-BBE1-CAA86EA92566',
            'transaction_uuid'=>  uniqid(),
            'signed_date_time'=> gmdate("Y-m-d\TH:i:s\Z"),
            'merchant_id' => 'visanetdr_000000431651001',
            'transaction_key' => 'Xkj7wkZGeSQ2Dty1laqgk6xia9VLmM+jH1zv3z+TS6hSBB9B9vhwXGNdi5lCm+Ha5gMakj3FbzJl4RpZQFfI8l0kleXaYX+OUcIEloD2nzERkKS57uJR39Nky6OMhAmmLg4/w7N4ntOafTt3Aksrz7pcMVvCz+9v8jZ/K7MitC+2EGaT7ScL0TWDQ/k2Lc1nSxfZlxE2H2xWK1dYtYbMXKA2SwbkHl3IZp0Xxplz3oIczO+OiwRUasYKcW0RBDDPDmWasmXoLe6QKWwQTM9xCiDqnkSTG5tXXjNx9MwQZ0RfG1JlYlXjmgC2JRkPTSiWwvzusVUiUsp3yNC/6nFLkA==',
        ];
    }

    function payment_patient($patient_id, $list_cards, $create_token = false, $order_id, $amount) {
      


        // $app_info = $this->getappinfo($app_id);            
        // $template['has_orders'] = $this->apphasorders($app_id);
        // print_r($app_info);die();
        define('MERCHANT_ID', visanet_vars()->merchant_id);
        // define('MERCHANT_ID', visanet_vars()->merchant_id);
        // print_r(MERCHANT_ID);die();

        // DF TEST: 1snn5n9w, LIVE: k8vif92e
        //  define('DF_ORG_ID', visanet_vars()->org_live);
         define('DF_ORG_ID', visanet_vars()->org_dev);
        //  define('DF_ORG_ID', visanet_vars()->org_dev);
        session_start( ); 
        $sess_id  = session_id();
        // print_r($sess_id);die('32');
        $df_param = 'org_id=' . DF_ORG_ID . '&amp;session_id=' . MERCHANT_ID . $sess_id;



        $template['is_valid_hash'] = true;
        $template["page"] = "Template-modal-payment/add_card_modal";
        $url = 'https://megusta.do/webservice/card-manager/'.$patient_id.'/'.$order_id;
        // $url = 'http://'.$_SERVER[HTTP_HOST].'/Webservice/payment_patient/'.$patient_id."/".$app_id."/".$amount;
        // $template['list_cards'] = $this->vn_patient_get_preferred($patient_id);
        $template['list_cards'] =$list_cards;
   
        $template['appointment_info'] = [];
        
        if ($create_token) {
        
            $url = 'https://megusta.do/webservice/card-manager/'.$patient_id.'/0';
            $template['vn_fields'] = vn_export_fields($sess_id, 1, $patient_id, 'patient', '0.00', 'create_payment_token', false, $url, 0, 0, false, []);
        } else {
                if(!empty($list_cards)) {
                    
                    // $template['vn_fields'] = $this->vn_export_fields($sess_id, $app_info->doctor_id, $patient_id, 'patient', $amount, 'sale', false, $url, $app_id, $template['list_cards']->id, $order);
                    $template['vn_fields'] = vn_export_fields($sess_id, 1, $patient_id, 'patient', $amount, 'sale', false, $url, 0, 0, $order_id,  $list_cards);
                } else {
                    $template['createAndSale'] = true;
                    $template['vn_fields'] = vn_export_fields($sess_id, 1, $patient_id, 'patient', $amount, 'create_payment_token,sale', false, $url, 0, 0, $order_id,  $list_cards);
                }

        }

        $template['vn_append'] = ' <p style="background:url(https://h.online-metrix.net/fp/clear.png?'.$df_param.'&amp;m=1)"></p><img src="https://h.online-metrix.net/fp/clear.png?'.$df_param.'&amp;m=2" width="1" height="1" />';
        
        return $template;
        // $this->load->view/('Template-modal-payment/modal_payment', $template);
    }


    /**
     * Undocumented function
     * SE AGREGO DOCTORID COMO PRIMER PARAMETRO
     * @param [type] $id
     * @param [type] $user_type => doctor|patient
     * @param [type] $amount
     * @param [type] $transaction_type: create_payment_token|authorization|sale
     * @param [type] $is_recurring: set up recurrency, When is recurring, the amount field is the recurrent amount
     * @return void
    */
    function vn_export_fields(
        $sess_id = null, 
        $doctor_id = null, 
        $id, 
        $user_type = 'patient', 
        $amount,
        $transaction_type, 
        $is_recurring = false, 
        $url_return_web, 
        $appointment_id = '', 
        $card_id = '', 
        // $order='', 
        $order_id = false, 
        $list_cards ) {
        
        // print_r($list_cards);die();
        /**
         *  @TODO: create user validation
         */
        // $transaction_type = 'create_payment_token,sale';// create_payment_token,sale,authorization
        $reference_number = $id.time();
        $currency = 'DOP';
        $payment_method = 'card';

        // print_r($transaction_type);die();
        
        //  `vn_merchant_id` TEXT NULL AFTER `visanet_card_type`, 
        //  `vn_profile_id` TEXT NULL AFTER `vn_merchant_id`, 
        //  `vn_access_key` TEXT NULL AFTER `vn_profile_id`, 
        //  `vn_secret_key` TEXT NULL AFTER `vn_access_key`, 
        //  `vn_soap_key` TEXT NULL AFTER `vn_secret_key`, 
        //  `vn_is_enabled` INT NOT NULL DEFAULT '0' AFTER `vn_soap_key`, 
        //  `vn_is_active` INT NOT NULL DEFAULT '0' AFTER `vn_is_enabled`;

        $access_key = visanet_vars()->access_key;
        $profile_id = visanet_vars()->profile_id;
        $transaction_uuid = visanet_vars()->transaction_uuid;
        $signed_date_time = visanet_vars()->signed_date_time;
        $secret_key  = visanet_vars()->secret_key;
        $doctor = [];


        $append_info = get_billing_info_by_type($id, $user_type);
        // print_r($list_cards[0]->subscription_id);die();
        if (empty($list_cards)) {
            $payment_token = '';
        } else {
            $payment_token = $list_cards[0]->subscription_id;

        }
        if ($card_id  != '') {
            $card = $this->VN_Patient_Cards_Model->get_card($card_id);
            $payment_token =  $card->subscription_id;
        }

        // This can be injected via POST
        $form = vn_form_factory(array_merge([
            'type' =>$transaction_type,
            'reference_number' => $reference_number,
            'amount' => $amount,
            'currency' => $currency,
            'payment_method' => $payment_method,
            'key' => $id,
            'value' => $user_type,
            'url_return_web' => $url_return_web,
            'appointment_id' => $appointment_id,
            'card_id' => $card_id,
            'payment_token' => $payment_token,
            'doctor_data' => $doctor,
            'order' => $order_id
        ], $append_info), $access_key, $profile_id, $transaction_uuid, $signed_date_time, $sess_id);
        
        $fields = "";
        foreach($form as $name => $value) {
            $fields .= "<input type='hidden' id='" . $name . "' name='" . $name . "' value='" . $value . "'/>";
        }
        $fields .= "<input type='hidden' id='signature' name='signature' value='" . sign($form ,$secret_key) . "'/>";
        return $fields;
    }


    function buildDataToSign($params) {
        $signedFieldNames = explode(",",$params["signed_field_names"]);

        foreach ($signedFieldNames as $field) {
           $dataToSign[] = $field . "=" . $params[$field];
        }

        return commaSeparate($dataToSign);
    }

    function commaSeparate ($dataToSign) {
        return implode(",",$dataToSign);
    }
    function sign ($params, $secretKey) {
        return signData(buildDataToSign($params), $secretKey);
    }
      
    function signData($data, $secretKey) {
        return base64_encode(hash_hmac('sha256', $data, $secretKey, true));
    }

    function get_billing_info_by_type($id, $type) {
        if ($type == 'doctor') {
            // $user = $this->webservice_model->getDoctor($id)[0];
            $data = [
                'bill_to_forename' => 'Megusta.do',
                'bill_to_surname' => "Rodriguez",
                'bill_to_email' => 'josuegrullon@gmail.com',
                'bill_to_phone' => '8092229989',
                'bill_to_address_line1' =>'CDonc dsocisiod',
                'bill_to_address_city' => 'Santo Domingo',
                'bill_to_address_state' => 'SD',
                'bill_to_address_country' => 'DO',
                'bill_to_address_postal_code' => "",
            ];
        } else { // patient
            $user = ( new Webservice())->get_client_info($id);
            
            $data = [
                'bill_to_forename' => ucfirst(strtolower($user->firstName)),
                'bill_to_surname' => ucfirst(strtolower($user->lastName)),
                'bill_to_email' => $user->email,
                'bill_to_phone' => $user->phone,
                'bill_to_address_line1' => 'C/ Paseo de Los Locutores, #45',
                'bill_to_address_city' => 'Santo Domingo',
                'bill_to_address_state' => 'SD',
                'bill_to_address_country' => 'DO',
                'bill_to_address_postal_code' => '',
            ];
        }

        $data['bill_to_address_line1'] = trim($data['bill_to_address_line1']) == ''?"Calle Paseo de los Locutores":$data['bill_to_address_line1'];
        $data['bill_to_phone'] = trim($data['bill_to_phone']) == ''?"18093634261":$data['bill_to_phone'];
        $data['bill_to_address_city'] = trim($data['bill_to_address_city']) == ''?"Santo Domingo":$data['bill_to_address_city'];
        
        return $data;
    } 
    function vn_form_factory($data, $access_key, $profile_id, $transaction_uuid, $signed_date_time, $sess_id) {

        $d_items = (new Webservice())->getoreritems_with_deals($data['order']);
        $items = [];
        foreach ($d_items as $k => $v) {
            $items['item_'.$k.'_name' ] =  substr($v->value, 0, 40).'..';
            $items['item_'.$k.'_sku'] = "SKU". $v->id. $data['key'].'_'.$sess_id;
            $items['item_'.$k.'_unit_price'] = $v->price;
            $items['item_'.$k.'_quantity'] = $v->quantity;
            $items['item_'.$k.'_code'] =  $v->id;

        }

        $items_to_sign = implode(',', array_keys($items));

        $comma = count($items) > 0 ? ",": "";
      $lineitemcount = '';
        if (count($d_items) > 0 ) {
            $lineitemcount = "line_item_count,";
        }
        $unsigned = $items_to_sign.$comma.$lineitemcount."customer_ip_address,device_fingerprint_id,merchant_defined_data27,merchant_defined_data1,merchant_defined_data2,merchant_defined_data3,merchant_defined_data4,merchant_defined_data28";
        $signed = "access_key,profile_id,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,transaction_type,reference_number,amount,currency,payment_method,bill_to_forename,bill_to_surname,bill_to_email,bill_to_phone,bill_to_address_line1,bill_to_address_city,bill_to_address_state,bill_to_address_country,bill_to_address_postal_code";

        if ($data['payment_token'] == '') {
            $unsigned .= ',card_type,card_number,card_expiry_date,card_cvn';
        } else {
            $signed .= ',payment_token';
        }
        // $cedula = '40220770107';// commerce identity
        $merchant_defined_data2 = 'megusta.do';

        // 'line_item_count' => '1',
        // 'item_0_name' => $item_0_name,
        // 'item_0_sku' => $item_0_sku,
        // 'item_0_unit_price' => $item_0_unit_price,
        // 'item_0_quantity' => $item_0_quantity,
      

        // $item_0_name = "Pago de cita";
        // $item_0_sku = "SKU".$data['key'].'_'.$sess_id;
        // $item_0_unit_price = $data['amount']; 
        // // $item_0_tax_amount = "144"; 
        // $item_0_quantity = "1";
        

        if ($data['payment_token'] != '') {
            $tokenizada = 'TOKENIZACION YES';
        } else {
            $tokenizada = 'TOKENIZACION NO';
        }
        
        
   
        $fields =  [
           
            // 'line_item_count' => count($d_items),
            // 'item_0_name' => $item_0_name,
            // 'item_0_sku' => $item_0_sku,
            // 'item_0_unit_price' => $item_0_unit_price,
            // 'item_0_quantity' => $item_0_quantity,
            'customer_ip_address' => @$_SERVER['REMOTE_ADDR'],
            'access_key'=> $access_key,
            'profile_id'=> $profile_id,
            'transaction_uuid'=>  $transaction_uuid,
            'signed_field_names'=> $signed,
            'unsigned_field_names'=> $unsigned,
            'signed_date_time'=> $signed_date_time,
            'locale'=> "en",
            'transaction_type'=> $data['type'],
            'reference_number'=> $data['reference_number'],
            'amount'=> $data['amount'],
            'currency'=> $data['currency'],
            'payment_method'=> $data['payment_method'],
            'bill_to_forename'=> $data['bill_to_forename'],//"John",
            'bill_to_surname'=> $data['bill_to_surname'],//"Doe",
            'bill_to_email'=> $data['bill_to_email'],//"null@cybersource.com",
            'bill_to_phone'=> $data['bill_to_phone'],//"02890888888",
            'bill_to_address_line1'=> $data['bill_to_address_line1'],//"1 Card Lane", // no debe exeder 40 chars
            'bill_to_address_city'=> $data['bill_to_address_city'],//"My City",
            'bill_to_address_state'=> $data['bill_to_address_state'],//"CA",
            'bill_to_address_country'=> $data['bill_to_address_country'],//"DO",
            'bill_to_address_postal_code'=> $data['bill_to_address_postal_code'],//"94043",
            'merchant_defined_data1' => 'retail', // KEY:doctor|patient -> redefined to: retail (valor mandatorio)
            'merchant_defined_data2' => $merchant_defined_data2 ,// Nombre del comercio que recibe el pago: megusta.do, cuando sea de un paciente a un doctor es el nombre del doctor
            'merchant_defined_data3' => 'web', // redefined to web
            'merchant_defined_data4' => $data['key'],// doctor_id
            'merchant_defined_data28' => $data['key'].'::'.$data['value'].'::'.$data['url_return_web']."::". $data['appointment_id']."::".$data['card_id']."::".$data['order'],
            'merchant_defined_data27' => $tokenizada,//TOKENIZACION YES|TOKENIZACION NO -> cuando es con token yes, cuando es una compra sin token no
            // 'merchant_defined_data29' => 'cedula',//tipo de documento: cedula|pasaporte // no se envia cuando no tiene cedula
            // 'merchant_defined_data30' => $cedula,//valor del documento,// no se envia cuando no tiene cedula
            'device_fingerprint_id' =>  $sess_id,



        ];

        if ($data['payment_token'] != '') {
            $fields = array_merge (['payment_token' => $data['payment_token']], $fields );
        }

        if (count($d_items) > 0 ) {
            $fields = array_merge([  'line_item_count' => (string)count($d_items)], $fields);
        }

        $fields = array_merge($items, $fields);
        // print_r($fields);die();
        return $fields;
    }

?>