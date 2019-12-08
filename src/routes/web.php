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


    // Render Twig template in route
    $app->get('/card-manager', function ($request, $response, $args) {

        // $_GET
        $data = payment_patient(1, false);

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

        $list_cards = [];
        $cards = $list_cards;

        $args['data']= $data;
        $args['list_cards']= [];
        $args['showForm']= [];
        $args['hasError']= [];
        $args['cards']= [];
        $args['gett'] = $_GET;
        $args['is_valid_hash'] = true;
        
        return $this->view->render($response, 'card-manager.html', $args);
    })->setName('profile');

    $app->get("/vn_client/{patient_id}", function(Request $request, Response $response, $arg) {
        return $this->view->render($response, 'client-card-manager.html', $arg);
    })->setName('profile');

    function visanet_vars() {
        return (object)[
            'version' => '0.1',
            'name' => 'Visanet e-commerce',
            'org_dev'=> '1snn5n9w',
            'org_live'=> 'k8vif92e',
            'secret_key' => '7bf10c540f08438e8ec4f9e3d2959c8a9af5cb18e69c474c85be66a4fc4be4bb713257dd94c64f66b3e7a93401a719d6908bd73dc3cc45e1a2a97b36978c4244cfc05243c3424b529b326dedb6c5ecc192fae525d24143529907928d17fe1e836dddfde958dc47e889da876608861dafb89cef95133146fe87dec264283d605a',
            'access_key'=> '8456a8f5ecb83ba4a25ec239d6ae0a71',
            'profile_id'=> '47EC6C53-9B6F-47AA-89AA-70CE446D13AD',
            'transaction_uuid'=>  uniqid(),
            'signed_date_time'=> gmdate("Y-m-d\TH:i:s\Z"),
            'merchant_id' => 'visanetdr_000000430807001',
            'transaction_key' => '/QPGAXqTWDmA633HMdjImXLShlw9epYy0O6cS8MBXOlNwK8rOKBJy26cRT7Bk09euVTMS1r+mAQVznGOMXEzzugEIA8z+DhJ/fw4co7wNnFf1y03iYTgT6gfo9D+072lf2oALc1rmztg65t747QXTPZpbMkCplh9ZODo+4YCDIFifPQIe1XRBjOarhwAGHUIFSfdM33zFlciXrshxUyvesfAg0Twxx/B0RUSjuJ2jOS4nxyq4hDPU9UC7wSwoJMB3tQNOaUUWkQkdKm+/IdCwCZgSkFcyGVNhySwyHqXh/oIm/mz1ud2KrZ29/l9PlOg3GglO/UceZMcdxXekjfe5w==',
        ];
    }

    function payment_patient($patient_id, $order=false) {
        // $app_info = $this->getappinfo($app_id);            
        // $template['has_orders'] = $this->apphasorders($app_id);
        // print_r($app_info);die();
        define('MERCHANT_ID', visanet_vars()->merchant_id);
        // define('MERCHANT_ID', visanet_vars()->merchant_id);
        // print_r(MERCHANT_ID);die();

        // DF TEST: 1snn5n9w, LIVE: k8vif92e
         define('DF_ORG_ID', visanet_vars()->org_live);
        //  define('DF_ORG_ID', visanet_vars()->org_dev);
        //  define('DF_ORG_ID', visanet_vars()->org_dev);
        session_start( ); 
        $sess_id  = session_id();
        // print_r($sess_id);die('32');
        $df_param = 'org_id=' . DF_ORG_ID . '&amp;session_id=' . MERCHANT_ID . $sess_id;



        $template['is_valid_hash'] = true;
        $template["page"] = "Template-modal-payment/add_card_modal";
        $url = 'https://megusta.do';
        // $url = 'http://'.$_SERVER[HTTP_HOST].'/Webservice/payment_patient/'.$patient_id."/".$app_id."/".$amount;
        // $template['list_cards'] = $this->vn_patient_get_preferred($patient_id);
        $template['list_cards'] =[];
   
        $template['appointment_info'] = [];
        
        $template['amount'] = 1000;
        if(!empty($template['list_cards'])) {
            
            $template['vn_fields'] = $this->vn_export_fields($sess_id, $app_info->doctor_id, $patient_id, 'patient', $amount, 'sale', false, $url, $app_id, $template['list_cards']->id, $order);
        } else {
            $template['createAndSale'] = true;
            $template['vn_fields'] = vn_export_fields($sess_id, 1, 1, 'patient', 1000, 'create_payment_token,sale', false, $url, 0, 0);
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
    function vn_export_fields($sess_id = null, $doctor_id = null, $id, $user_type, $amount,$transaction_type, $is_recurring = false, $url_return_web, $appointment_id = '', $card_id = '', $order='') {
        /**
         *  @TODO: create user validation
         */
        // $transaction_type = 'create_payment_token,sale';// create_payment_token,sale,authorization
        $reference_number = $id.time();
        $currency = 'DOP';
        $payment_method = 'card';
        
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
        //    if ($doctor_id) {
        //         $doctor = (object)[];//$this->Doctor_Model->get_doctor_data($doctor_id);
        //         $access_key = $doctor->vn_access_key;
        //         $profile_id = $doctor->vn_profile_id;
        //         $transaction_uuid = $transaction_uuid;
        //         $signed_date_time = $signed_date_time;
        //         $secret_key  = $doctor->vn_secret_key;

        //         // print_r($doctor);die();
        //     } else {
        //         if ($user_type == 'doctor') {
        //             $doctor = $this->Doctor_Model->get_doctor_data($id);
        //         }
        //     }


        $append_info = get_billing_info_by_type($id, $user_type);
        $payment_token = '';
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
            'order' => $order
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
            // $user = $this->webservice_model->get_patient($id);

            $data = [
                'bill_to_forename' => "Josue",
                'bill_to_surname' => "Grullon",
                'bill_to_email' => "josuegrullon@gmail.com",
                'bill_to_phone' => '8294023393',
                'bill_to_address_line1' =>"LAs mercedes #20",
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
        // item_0_tax_amount
        $unsigned = "line_item_count,item_0_name,item_0_sku,item_0_unit_price,item_0_quantity,customer_ip_address,device_fingerprint_id,merchant_defined_data27,merchant_defined_data29,merchant_defined_data30,merchant_defined_data1,merchant_defined_data2,merchant_defined_data3,merchant_defined_data4,merchant_defined_data28";
        $signed = "access_key,profile_id,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,transaction_type,reference_number,amount,currency,payment_method,bill_to_forename,bill_to_surname,bill_to_email,bill_to_phone,bill_to_address_line1,bill_to_address_city,bill_to_address_state,bill_to_address_country,bill_to_address_postal_code";

        if ($data['payment_token'] == '') {
            $unsigned .= ',card_type,card_number,card_expiry_date,card_cvn';
        } else {
            $signed .= ',payment_token';
        }

        $cedula = '40220770107';// commerce identity
        // print_r($data);die();
        $merchant_defined_data2 = 'buscamed.do';
        // print_r($data);die();
        if ($data['value'] == 'patient') {
            // DOC INFO
            // $user = $this->webservice_model->get_patient($data['key']);
            // print_r($user);die();
            $cedula = '40220770108';// user identity
            $merchant_defined_data2 = "megusta.do";

            $item_0_name = "Pago de cita";
            $item_0_sku = "SKU".$data['key'].'_'.$sess_id;
            $item_0_unit_price = $data['amount']; 
            // $item_0_tax_amount = "144"; 
            $item_0_quantity = "1";
            if (trim($data['card_id']) == '') {
                $tokenizada = 'TOKENIZACION NO';
            } else {
                $tokenizada = 'TOKENIZACION YES';
            }
            
            if ($data['type'] == 'create_payment_token') {
                $tokenizada = 'TOKENIZACION NO';
                $merchant_defined_data2 = 'buscamed.do';
            }
          
        } else {
            $item_0_name = "Inscripcion de recurrencia";
            $item_0_sku = "SKU".$data['key'].'_'.$sess_id;
            $item_0_unit_price = '0'; 
            // $item_0_tax_amount = "135"; 
            $item_0_quantity = "1";
            $tokenizada = 'TOKENIZACION NO';
        }
        // item_#_tax_amount
            //             <input type="hidden" name="customer_ip_address" value=" @$_SERVER['REMOTE_ADDR'] ">
            //             <input type="hidden" name="item_0_unit_price" value="10.00" />
            //             <input type="hidden" name="item_0_quantity" value="100" />

            //             <input type="hidden" name="line_item_count" value="2" />
            //    1        <input type="hidden" name="item_0_sku" value="sku001" />
            //             <input type="hidden" name="item_0_code" value="KFLTFDIV" />
            //             <input type="hidden" name="item_0_name" value="KFLTFDIV" />
            //             <input type="hidden" name="item_0_quantity" value="100" />
            //             <input type="hidden" name="item_0_unit_price" value="5.72" />
                    
            //             <input type="hidden" name="item_0_sku" value="sku002" />
            //             <input type="hidden" name="item_0_code" value="KFLTFD70" />
            //             <input type="hidden" name="item_0_name" value="KFLTFD70" />

            // i. Nombre de ítem o servicio vendido item_0_name
            // j. 1KU de ítem (identificador del producto) item_0_sku
            // k. Precio unitario del producto item_0_unit_price
            // l. Impuesto (si maneja impuestos por ítem) item_0_tax_amount
            // m. Cantidad del producto item_0_quantity
            // n. Ip del comprador     customer_ip_address


        $fields =  [
            'line_item_count' => '1',
            'item_0_name' => $item_0_name,
            'item_0_sku' => $item_0_sku,
            'item_0_unit_price' => $item_0_unit_price,
            // 'item_0_tax_amount' => $item_0_tax_amount,
            'item_0_quantity' => $item_0_quantity,
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
           
            // 'merchant_defined_data1' => $data['key'], // KEY:doctor|patient -> redefined to: retail (valor mandatorio)
            // 'merchant_defined_data2' => $data['value'],// Nombre del comercio que recibe el pago: buscamed.do, cuando sea de un paciente a un doctor es el nombre del doctor
            // 'merchant_defined_data3' => $data['url_return_web'], // redefined to web
            // 'merchant_defined_data4' => $data['appointment_id'],// doctor_id
            // 'merchant_defined_data28' => $data['card_id'],
            // 'merchant_defined_data27' => $data['card_id'],//TOKENIZACION YES|TOKENIZACION NO -> cuando es con token yes, cuando es una compra sin token no
            // 'merchant_defined_data29' => $data['card_id'],//tipo de documento: cedula|pasaporte
            // 'merchant_defined_data30' => $data['card_id'],//valor del documento
            // // redefinicion

            'merchant_defined_data1' => 'retail', // KEY:doctor|patient -> redefined to: retail (valor mandatorio)
            'merchant_defined_data2' => $merchant_defined_data2 ,// Nombre del comercio que recibe el pago: buscamed.do, cuando sea de un paciente a un doctor es el nombre del doctor
            'merchant_defined_data3' => 'web', // redefined to web
            'merchant_defined_data4' => $data['key'],// doctor_id
            'merchant_defined_data28' => $data['key'].'::'.$data['value'].'::'.$data['url_return_web']."::". $data['appointment_id']."::".$data['card_id']."::".$data['order'],
            'merchant_defined_data27' => $tokenizada,//TOKENIZACION YES|TOKENIZACION NO -> cuando es con token yes, cuando es una compra sin token no
            'merchant_defined_data29' => 'cedula',//tipo de documento: cedula|pasaporte
            'merchant_defined_data30' => $cedula,//valor del documento,
            'device_fingerprint_id' =>  $sess_id,



        ];

        if ($data['payment_token'] != '') {
            $fields = array_merge (['payment_token' => $data['payment_token']], $fields );
        }

        return $fields;
    }

?>