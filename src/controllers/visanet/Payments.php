<?php 
    trait Payments {

        public function payment_doctor($doctor_id) {

           
            define('MERCHANT_ID', $this->vault()->merchant_id); // visanet merchant
            
            // DF TEST: 1snn5n9w, LIVE: k8vif92e
             define('DF_ORG_ID', $this->vault()->org_dev);
            $sess_id  = session_id();
            $df_param = 'org_id=' . DF_ORG_ID . '&amp;session_id=' . MERCHANT_ID . $sess_id;
            $amount = "885.00";
            $template['is_valid_hash'] = true;
            $template['doctor_is_creating_token'] = true;
            $template["page"] = "Template-modal-payment/add_card_modal";
            $url = 'https://'.$_SERVER[HTTP_HOST].'/Webservice/payment_doctor/'.$doctor_id;
            // $url = 'http://localhost/Webservice/payment_doctor/'.$doctor_id;
           $template['amount'] = $amount;
            // if($this->session->userdata('frontend_logged_in')== true){
            //     $url = 'http://localhost/Welcome#payments';
            // }
            $template['list_cards'] = [];
            $template['appointment_info'] = (object)['appispaid' => false];

            $template['vn_fields'] = $this->vn_export_fields($sess_id, null, $doctor_id, 'doctor', '885.00', 'create_payment_token', true, $url);
            $template['vn_append'] = ' <p style="background:url(https://h.online-metrix.net/fp/clear.png?'.$df_param.'&amp;m=1)"></p><img src="https://h.online-metrix.net/fp/clear.png?'.$df_param.'&amp;m=2" width="1" height="1" />';
          
            $this->load->view('Template-modal-payment/modal_payment', $template);
        }
        
        public function decrypt_hash($hash) {
            return $this->decrypt_t("diony", $hash);
        }

        public function get_hash($app_id, $amount) {
            echo json_encode($this->get_payment_hash($app_id, $amount));
            exit(0);
        }

        public function valid_hash($hash) {
            // $hash = urldecode($hash);
            $hash_decrypted = $this->decrypt_t("diony", $hash);
            // print_r($hash_decrypted);die();
            // Expiration
            $data = explode(":", $hash_decrypted);
            $created_at = $data[2];

            $date1 = $created_at;
            $date2 = time();  
              
            $diff = abs($date2 - $date1);  
              
            $minutes = floor(($diff - $years * 365*60*60*24  
                     - $months*30*60*60*24 - $days*60*60*24  
                                      - $hours*60*60)/ 60);

            if ($minutes > 20) {
                return false;
            }
            return true;
        }

        /**
         * Undocumented function
         *
         * @param [type] $hash
         * @return void
         */
        public function payment_acceptance($hash) {

            $template['is_valid_hash'] = $this->valid_hash($hash);

            // $hash = urldecode($hash);
            $decrypt = $this->decrypt_hash($hash);
            $data = explode(":", $decrypt);

            $app_id = $data[0];
            $amount = $data[1];
            $created_at = $data[2];

            $template['appointment_info'] = $this->getappinfo($app_id);
            $patient_id = $template['appointment_info']->patient_id;
            $doctor_id = $template['appointment_info']->doctor_id;
            $template["page"] = "Template-modal-payment/add_card_modal";
            $url = 'https://'.$_SERVER[HTTP_HOST].'/Webservice/payment_acceptance/'.$hash;

            $template['list_cards'] = $this->vn_patient_get_preferred($patient_id);
      
            define('MERCHANT_ID',       $template['appointment_info']->vn_merchant_id);

            // DF TEST: 1snn5n9w, LIVE: k8vif92e
             define('DF_ORG_ID', $this->vault()->org_dev);
            $sess_id  = session_id();
            $df_param = 'org_id=' . DF_ORG_ID . '&amp;session_id=' . MERCHANT_ID . $sess_id;


            if(!empty($template['list_cards'])) {
                $template['amount'] = $amount;

                $template['vn_fields'] = $this->vn_export_fields($sess_id, $doctor_id, $patient_id, 'patient', $amount, 'sale', false, $url, $app_id, $template['list_cards']->id );
            } else {
                $template['vn_fields'] = $this->vn_export_fields($sess_id, $doctor_id, $patient_id, 'patient', $amount, 'create_payment_token,sale', false, $url, $app_id);
            }

            $template['vn_append'] = ' <p style="background:url(https://h.online-metrix.net/fp/clear.png?'.$df_param.'&amp;m=1)"></p><img src="https://h.online-metrix.net/fp/clear.png?'.$df_param.'&amp;m=2" width="1" height="1" />';
            // print_r($template);die();

            $this->load->view('Template-modal-payment/modal_payment', $template);
        }

        function create_token_patient($patient_id) {
            define('MERCHANT_ID', $this->vault()->merchant_id);// Los tokens se almacenan en visanet

            // DF TEST: 1snn5n9w, LIVE: k8vif92e
             define('DF_ORG_ID', $this->vault()->org_dev);
            $sess_id  = session_id();
            $df_param = 'org_id=' . DF_ORG_ID . '&amp;session_id=' . MERCHANT_ID . $sess_id;

            $template['is_valid_hash'] = true;
            $template["page"] = "Template-modal-payment/add_card_modal";
            $url = 'https://'.$_SERVER[HTTP_HOST].'/Webservice/create_token_patient/'.$patient_id;

            $template['list_cards'] = [];
            $template['vn_fields'] = $this->vn_export_fields($sess_id, null, $patient_id, 'patient', '0.00', 'create_payment_token', false, $url);
            
            $template['vn_append'] = ' <p style="background:url(https://h.online-metrix.net/fp/clear.png?'.$df_param.'&amp;m=1)"></p><img src="https://h.online-metrix.net/fp/clear.png?'.$df_param.'&amp;m=2" width="1" height="1" />';
            $this->load->view('Template-modal-payment/modal_payment', $template);
        }

        function payment_patient($patient_id, $app_id, $amount, $order=false) {
            $app_info = $this->getappinfo($app_id);            
            $template['has_orders'] = $this->apphasorders($app_id);
            // print_r($app_info);die();
            define('MERCHANT_ID', $app_info->vn_merchant_id);
            // define('MERCHANT_ID', $this->vault()->merchant_id);
            // print_r(MERCHANT_ID);die();

            // DF TEST: 1snn5n9w, LIVE: k8vif92e
             define('DF_ORG_ID', $this->vault()->org_live);
            //  define('DF_ORG_ID', $this->vault()->org_dev);
            //  define('DF_ORG_ID', $this->vault()->org_dev);
            $sess_id  = session_id();
            $df_param = 'org_id=' . DF_ORG_ID . '&amp;session_id=' . MERCHANT_ID . $sess_id;
 


            $template['is_valid_hash'] = true;
            $template["page"] = "Template-modal-payment/add_card_modal";
            $url = 'https://'.$_SERVER[HTTP_HOST].'/Webservice/payment_patient/'.$patient_id."/".$app_id."/".$amount;
            // $url = 'http://'.$_SERVER[HTTP_HOST].'/Webservice/payment_patient/'.$patient_id."/".$app_id."/".$amount;
            $template['list_cards'] = $this->vn_patient_get_preferred($patient_id);
       
            $template['appointment_info'] = $app_info;
            
            $template['amount'] = $amount;
            if(!empty($template['list_cards'])) {
                
                $template['vn_fields'] = $this->vn_export_fields($sess_id, $app_info->doctor_id, $patient_id, 'patient', $amount, 'sale', false, $url, $app_id, $template['list_cards']->id, $order);
            } else {
                $template['createAndSale'] = true;
                $template['vn_fields'] = $this->vn_export_fields($sess_id, $app_info->doctor_id, $patient_id, 'patient', $amount, 'create_payment_token,sale', false, $url, $app_id, $order);
            }

            $template['vn_append'] = ' <p style="background:url(https://h.online-metrix.net/fp/clear.png?'.$df_param.'&amp;m=1)"></p><img src="https://h.online-metrix.net/fp/clear.png?'.$df_param.'&amp;m=2" width="1" height="1" />';
            $this->load->view('Template-modal-payment/modal_payment', $template);
        }

        function openGeneralPayModal() {
            $param['url'] = $_GET['url'];
            $this->load->view('general_pay_modal', $param);
        }

        function getAppPriceByInsurancePlan() {
            $doc_id = $_GET['doctor_id'];
            $ins_plan_id = $_GET['insurance_plan_id'];

            $result = $this->webservice_model->getAppPriceByInsurancePlan($doc_id, $ins_plan_id);

            $this->jsonResponse($result);
        }
    }
?>