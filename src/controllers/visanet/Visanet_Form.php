<?php 

/**
 * Visanet FORM
 */
trait Visanet_Form {

    private function internal_save_vn_response_log($response) {
        // Connect to db to save it
        return true;
    }
    public function sendConfirmationEmail($data){
        Mailer::sendPaymentAppointmentConfirmation($data);
    }

    private function process481($data) {
        $d = $data['req_merchant_defined_data28']; // TENTATIVO <- Reservar el merchant defined data
        // REFERENCE: $data['key'].':'.$data['value'].':'.$data['url_return_web'].":". $data['appointment_id'].":".$data['card_id'],
        $info = explode('::', $d);

        $id   = $info[0];
        $type = $info[1];
        $url_web_return = $info[2];
        $appointment_id = $info[3];
        $card_id = $info[4];
        $req_transaction_type = $data['req_transaction_type'];
        if ($type == 'doctor') {
            $c = $this->gateway($id, true);
        } else {
            $doc_id = $this->getappinfo($appointment_id)->doctor_id;
            $c = $this->gateway($doc_id);
        }
        // print_r($data);
        // die();
       
        
        $request_id = $data['transaction_id'];
        $amount     = $data['req_amount'];
        $reference     = $data['req_reference_number'];
        // perform the reversal
        try{
            $c->reverse_authorization( $request_id, $amount, $reference );

        } catch (Exception $e){
            
        }
        
    }

    public function process481Forced() {
       
        // if ($type == 'doctor') {
            $c = $this->gateway(1, true);
          
        
        // print_r($data);die();
        // 5684120808466402304005
        // "750.00",
        // 11568412073
        $request_id = '5684120808466402304005';
        $amount     = "750.00";
        $reference     = '11568412073';
        // perform the reversal
        print_r($c->reverse_authorization( $request_id, $amount, $reference ));
        die();
        // die();
    }

    
    public function vn_manage_response_payment($data) {
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
        
       
    //   print_r($data);die();
        if ($data['reason_code'] == '481') {
           
            $this->process481($data);
      
        }
        if ($data['decision'] == 'ACCEPT') {
           
          
            if($type == 'doctor') {
               
               
                if ($this->Doctor_Cards_Model->get_card($id)) {
                  
                    $this->delete_doctor_card($id);
                }
                $this->Doctor_Cards_Model->add_doctor_card($id, [
                    'visanet_subscription_id' => $data['payment_token'],	
                    'visanet_card_hash' => $data['req_card_number'],	
                    'visanet_card_type' => $data['req_card_type']
                ]);
            }
         
            if ($type == 'patient') {
                
                // if transaction type includes create_payment_token
                if (strpos( $data['req_transaction_type'] ,'create_payment_token') !== false) {
                    $cards = $this->VN_Patient_Cards_Model->get_cards($id);
                    $preferred = 0;
                    if (!$cards) {
                        $preferred = 1;
                    }
                    $card_id = $this->VN_Patient_Cards_Model->add_card([
                        "patient_id" => $id,
                        'subscription_id' => $data['payment_token'],	
                        'card_hash' => $data['req_card_number'],	
                        'preferred' => $preferred,
                        'type' => $data['req_card_type']
                    ]);
                }

                if (strpos( $data['req_transaction_type'] ,'sale') !== false) {
                    $orders = null;
                    $doctor_id = $this->Webservice_model->get_appointment_by_id($appointment_id)->doctor_id;

                    if($order === 'order') {
                        $orders_res = $this->VN_Patient_Transactions_Model->pay_order($id, $appointment_id);

                        foreach($orders_res['orders'] as $order) {
                            $orders .= $order->id.",";
                        }

                        // $doctor_id = $orders_res['orders'][0]->doctor_id;
                        $orders = substr($orders, 0, -1); //remove the las comma
                    }

                    // if transaction type includes sale
                    $transaction_id = $this->VN_Patient_Transactions_Model->register_transaction([
                        'status' => $data['decision'],
                        'appointment_id' => $appointment_id,	
                        'orders' => $orders,
                        'doctor_id' => $doctor_id,
                        'method_payment' => 'card',
                        'card_id' => $card_id,	
                        'amount' => $data['req_amount'],	
                        'patient_id' => $id,
                        'source' => '',
                        'transaction_info' => json_encode($data)
                    ]);
                    $this->VN_Patient_Transactions_Model->pay_appointment($appointment_id);
                    $this->sendConfirmationEmail([
                        'transaction_id' => $data['transaction_id'],
                        'appointment' => $this->getappinfo($appointment_id),	
                        'card_hash' => $data['req_card_number'],	
                        'amount' => 'DOP $'.$data['req_amount'],	
                    ]);
                }
            }
        }
 
        // Include redirect_uri field in response
        return (object)[
            'redirect_uri' => $url_web_return
        ];
    }

    /**
     * Catch cybersource response on payment actions
     *
     * @return void
     */
    public function vn_catch_payment() {
        // Save request in log
        $this->internal_save_vn_response_log($_REQUEST);
        // Get response details and do actions
        $response_info = $this->vn_manage_response_payment($_REQUEST);
        header("location:". $response_info->redirect_uri."?".http_build_query($_REQUEST));
        // print_r($_REQUEST);
        // exit(0);
    }


    /**
     * Catch cybersource response on payment actions
     *  Used for submerchants
     *
     * @return void
     */
    public function vn_catch_payment_diff_merchant() {
       
        // Save request in log
        $this->internal_save_vn_response_log($_REQUEST);
        
        // Get response details and do actions
        $response_info = $this->vn_manage_response_payment($_REQUEST);
       
        header("location:". $response_info->redirect_uri."?".http_build_query($_REQUEST));
        // print_r($_REQUEST);
        // exit(0);
    }

    private function vn_form_factory($data, $access_key, $profile_id, $transaction_uuid, $signed_date_time, $sess_id) {
        // item_0_tax_amount
        $unsigned = "line_item_count,item_0_name,item_0_sku,item_0_unit_price,item_0_quantity,customer_ip_address,device_fingerprint_id,merchant_defined_data27,merchant_defined_data29,merchant_defined_data30,merchant_defined_data1,merchant_defined_data2,merchant_defined_data3,merchant_defined_data4,merchant_defined_data28";
        $signed = "access_key,profile_id,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,transaction_type,reference_number,amount,currency,payment_method,bill_to_forename,bill_to_surname,bill_to_email,bill_to_phone,bill_to_address_line1,bill_to_address_city,bill_to_address_state,bill_to_address_country,bill_to_address_postal_code";
        if ($data['payment_token'] == '') {
            $unsigned .= ',card_type,card_number,card_expiry_date,card_cvn';
        } else {
            $signed .= ',payment_token';
        }
        $cedula = $data['doctor_data']->identity;
        // print_r($data);die();
        $merchant_defined_data2 = 'buscamed.do';
        // print_r($data);die();
        if ($data['value'] == 'patient') {
            // DOC INFO
            $user = $this->webservice_model->get_patient($data['key']);
            // print_r($user);die();
            $cedula = $user->identity;
            $merchant_defined_data2 = $data['doctor_data']->doctor_firstname. " ".$data['doctor_data']->doctor_lastname;

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

    /**
     * Get required based on type: patient or doctor
     *
     * @param [type] $id
     * @param [type] $type
     * @return void
     */
    private function get_billing_info_by_type($id, $type) {
        if ($type == 'doctor') {
            $user = $this->webservice_model->getDoctor($id)[0];
            $data = [
                'bill_to_forename' => $user->doctor_firstname,
                'bill_to_surname' => $user->doctor_lastname,
                'bill_to_email' => $user->email,
                'bill_to_phone' => $user->phone,
                'bill_to_address_line1' =>substr($user->doctor_office_address, 0, 40),
                'bill_to_address_city' => $user->doctor_office_city,
                'bill_to_address_state' => 'SD',
                'bill_to_address_country' => 'DO',
                'bill_to_address_postal_code' => $user->doctor_office_zip,
            ];
        } else { // patient
           
            $user = $this->webservice_model->get_patient($id);

            $data = [
                'bill_to_forename' => $user->patient_firstname,
                'bill_to_surname' => $user->patient_lastname,
                'bill_to_email' => $user->email,
                'bill_to_phone' => $user->phone,
                'bill_to_address_line1' =>substr($user->address, 0, 40),
                'bill_to_address_city' => 'Santo Domingo',
                'bill_to_address_state' => 'SD',
                'bill_to_address_country' => 'DO',
                'bill_to_address_postal_code' => $user->zip,
            ];
        }

        $data['bill_to_address_line1'] = trim($data['bill_to_address_line1']) == ''?"Calle Paseo de los Locutores":$data['bill_to_address_line1'];
        $data['bill_to_phone'] = trim($data['bill_to_phone']) == ''?"18093634261":$data['bill_to_phone'];
        $data['bill_to_address_city'] = trim($data['bill_to_address_city']) == ''?"Santo Domingo":$data['bill_to_address_city'];
        
        return $data;
    }   
    
    /**
     * Check if doctor has expired trial
     *
     * @param [type] $id
     * @return void
     */
    public function doctorEndTrial($id) {
        $doc =  $this->webservice_model->getDoctor($id);
        $created = $doc[0]->start_trial;
        $date_now = new DateTime();
        $date2    = new DateTime($created);
       
       if ($date_now >= $date2) {
           return true;
       }
       return false;
    }

    /**
     * Calculate bill date based on 30 grace days plus the next payment day diff.
     * The next payment date will be always XXXX-XX-03
     *
     * @return Date
     */
    public function bill_date($doctorEndTrial = false) 
    {
        if ($doctorEndTrial) {
            $monthLater = date('Y-m-d');
        } else {
            $monthLater = date('Y-m-d', strtotime("+1 Months"));
        }
        
        $month = date('m', strtotime($monthLater));

        // $monthLater = date('Y-m-d', strtotime("+1 Months"));
        // $month = date('m', strtotime($monthLater));
        $day2 =  date('Y-'.str_pad($month,2,'0',STR_PAD_LEFT).'-03');
        if ( $monthLater > $day2) {
            $month+=1;
             return date('Y'.str_pad($month,2,'0',STR_PAD_LEFT).'03');
        } else {
            return date('Y'.str_pad($month,2,'0',STR_PAD_LEFT).'03');
        }
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
    public function vn_export_fields($sess_id = null, $doctor_id = null, $id, $user_type, $amount,$transaction_type, $is_recurring = false, $url_return_web, $appointment_id = '', $card_id = '', $order='') {
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

        $access_key = $this->vault()->access_key;
        $profile_id = $this->vault()->profile_id;
        $transaction_uuid = $this->vault()->transaction_uuid;
        $signed_date_time = $this->vault()->signed_date_time;
        $secret_key  = $this->vault()->secret_key;
        $doctor = [];
       if ($doctor_id) {
            $doctor = $this->Doctor_Model->get_doctor_data($doctor_id);
            $access_key = $doctor->vn_access_key;
            $profile_id = $doctor->vn_profile_id;
            $transaction_uuid = $transaction_uuid;
            $signed_date_time = $signed_date_time;
            $secret_key  = $doctor->vn_secret_key;

            // print_r($doctor);die();
        } else {
            if ($user_type == 'doctor') {
                $doctor = $this->Doctor_Model->get_doctor_data($id);
            }
        }


        $append_info = $this->get_billing_info_by_type($id, $user_type);
        $payment_token = '';
        if ($card_id  != '') {
            $card = $this->VN_Patient_Cards_Model->get_card($card_id);
            $payment_token =  $card->subscription_id;
        }



        // This can be injected via POST
        $form = $this->vn_form_factory(array_merge([
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


        if ($is_recurring) {
            $form['signed_field_names'] = $form['signed_field_names'].',recurring_frequency,recurring_start_date,recurring_amount';
            $doctorEndTrial = $this->doctorEndTrial($id);
            $bill = $this->bill_date($doctorEndTrial);
            $recurrent_fields = [
                'recurring_frequency' => "monthly", //(weekly, monthly)
                // 'recurring_start_date' => '20191002',
                'recurring_start_date' => $bill,
                'recurring_amount' => $amount
            ];
            $form = array_merge($form, $recurrent_fields);
        }


        // print_r($form);die();
        
        $fields = "";
        foreach($form as $name => $value) {
            $fields .= "<input type='hidden' id='" . $name . "' name='" . $name . "' value='" . $value . "'/>";
        }
        $fields .= "<input type='hidden' id='signature' name='signature' value='" . $this->sign($form ,$secret_key) . "'/>";
        return $fields;
    }

   // doctor recurrent
    public function vn_testing_view() {
        define('MERCHANT_ID', 'visanetdr_000000430807001');

        // DF TEST: 1snn5n9w, LIVE: k8vif92e
        define('DF_ORG_ID', '1snn5n9w');
        $sess_id  = session_id();
        $df_param = 'org_id=' . DF_ORG_ID . '&amp;session_id=' . MERCHANT_ID . $sess_id;

        echo  '<form id="payment_confirmation" action="https://testsecureacceptance.cybersource.com/silent/pay" method="post"/>';
        echo ' <span>card_type:</span><input type="text" value="001" name="card_type"><br/>';
        echo ' <span>card_number:</span><input type="text" value="4111111111111111" name="card_number"><br/>';
        echo ' <span>card_expiry_date:</span><input type="text" value="11-2020" name="card_expiry_date"><br/>';
        echo ' <input type="submit" id="submit" value="Confirm "/>';
        // echo $this->vn_export_fields('2', 'patient', '101.00', 'sale', false, 'https://buscamed.do/bla bla bla');
        // echo $this->vn_export_fields('2', 'patient', '101.00', 'create_payment_token,sale', false, 'https://buscamed.do/bla bla bla', '1');
        echo $this->vn_export_fields($sess_id, null, '1', 'doctor', '751.00', 'create_payment_token', true, 'https://buscamed.do');
        echo ' </form>';
       
       echo ' <p style="background:url(https://h.online-metrix.net/fp/clear.png?'.$df_param.'&amp;m=1)"></p>';
       echo '<img src="https://h.online-metrix.net/fp/clear.png?'.$df_param.'&amp;m=2" width="1" height="1" />';
        exit(0);
    }

    public function vn_testing_view_token() {
        define('MERCHANT_ID', 'visanetdr_000000430807001');

        // DF TEST: 1snn5n9w, LIVE: k8vif92e
        define('DF_ORG_ID', '1snn5n9w');
        $sess_id  = session_id();
        $df_param = 'org_id=' . DF_ORG_ID . '&amp;session_id=' . MERCHANT_ID . $sess_id;

        echo  '<form id="payment_confirmation" action="https://testsecureacceptance.cybersource.com/silent/pay" method="post"/>';
        // echo ' <span>card_type:</span><input type="text" value="001" name="card_type"><br/>';
        // echo ' <span>card_number:</span><input type="text" value="4111111111111111" name="card_number"><br/>';
        // echo ' <span>card_expiry_date:</span><input type="text" value="11-2020" name="card_expiry_date"><br/>';
        echo ' <input type="submit" id="submit" value="Confirm "/>';
        // echo $this->vn_export_fields(null, '12', 'patient', '104.00', 'create_payment_token', false, 'https://buscamed.do/bla bla bla');
        echo $this->vn_export_fields($sess_id, 4,'2', 'patient', '101.00', 'sale', false, 'https://buscamed.do/bla bla bla', '14', '36'); // token en buscamed pago con merchant 1
        // echo $this->vn_export_fields(1, '2', 'patient', '101.00', 'create_payment_token', false, 'https://buscamed.do/bla bla bla', '1');
        // echo  $this->vn_export_fields(null, '1', 'doctor', '750.00', 'create_payment_token', true, 'https://buscamed.do');
        echo ' </form>';
        echo ' <p style="background:url(https://h.online-metrix.net/fp/clear.png?'.$df_param.'&amp;m=1)"></p>';
        echo '<img src="https://h.online-metrix.net/fp/clear.png?'.$df_param.'&amp;m=2" width="1" height="1" />';
         exit(0);
    }

    public function vn_view_create_token() {
        echo  '<form id="payment_confirmation" action="https://testsecureacceptance.cybersource.com/silent/pay" method="post"/>';
        echo ' <span>card_type:</span><input type="text" value="001" name="card_type"><br/>';
        echo ' <span>card_number:</span><input type="text" value="4111111111111111" name="card_number"><br/>';
        echo ' <span>card_expiry_date:</span><input type="text" value="11-2020" name="card_expiry_date"><br/>';
        echo ' <input type="submit" id="submit" value="Confirm "/>';
        echo $this->vn_export_fields(1, '25', 'patient', '104.00', 'create_payment_token', false, 'https://buscamed.do/bla bla bla');
        echo ' </form>';
        exit(0);
    }
    public function vn_view_create_token_sale() {
        echo  '<form id="payment_confirmation" action="https://testsecureacceptance.cybersource.com/silent/pay" method="post"/>';
        echo ' <span>card_type:</span><input type="text" value="001" name="card_type"><br/>';
        echo ' <span>card_number:</span><input type="text" value="4111111111111111" name="card_number"><br/>';
        echo ' <span>card_expiry_date:</span><input type="text" value="11-2020" name="card_expiry_date"><br/>';
        echo ' <input type="submit" id="submit" value="Confirm "/>';
        echo $this->vn_export_fields(1, '12', 'patient', '120.00', 'create_payment_token,sale', false, 'https://buscamed.do/bla bla bla', '1');
        echo ' </form>';
        exit(0);
    }
    public function vn_view_pay_with_token() {
        echo  '<form id="payment_confirmation" action="https://testsecureacceptance.cybersource.com/silent/pay" method="post"/>';
        echo ' <input type="submit" id="submit" value="Confirm "/>';
        echo $this->vn_export_fields(1, '12', 'patient', '70.00', 'sale', false, 'https://buscamed.do/bla bla bla', '1','19');
        echo ' </form>';
        exit(0);
    }
    
    public function getappinfo($app) {
       return  $this->webservice_model->get_appointment_by_id_related($app);
    }
    public function apphasorders($app) {
        return $this->webservice_model->app_has_order($app);
    }

    /**
     * @TODO: Accept doctor or patient id dynamicly
     *
     * @return void
     */
    public function  vn_form($id, $type, $amount) {
        return $this->jsonResponse([
            // 'fields' => $this->vn_export_fields($id, $type, $amount)
        ]);
    }

    public function test_encryption() {
       $encrypt =  $this->encrypt_t("diony", '32:200.00');
       print_r($encrypt);
       
       $decrypt = $this->decrypt_t("diony", 'MjY2NDo4MDAuMDA6MTU2NzQ1MDcxOQ==');
       print_r(' DEC: '.$decrypt);

    }


    public function get_payment_hash($app_id, $amount) {
        $hash = ($this->encrypt_t("diony", $app_id.':'.$amount.":".time()));
        // save it
        // print_r($hash);
        return $hash;
    }
}

?>