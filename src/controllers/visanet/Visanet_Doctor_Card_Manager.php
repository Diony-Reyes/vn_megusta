<?php 

/**
 * Visanet 
 */
trait Visanet_Doctor_Card_Manager {

    /**
     *  Get doctor cards
     *
     * @param [type] $doctor_id
     * @return void
     */
    public function  visanet_get_card($doctor_id) {
        $card = $this->Doctor_Cards_Model->get_card($doctor_id);

        return $this->jsonResponse(  [
            'card' => [
            'type' =>  $type = array_search ($card->visanet_card_type , $this->gateway($doctor_id)->card_types) ,
            'subscription_id' => $card->visanet_subscription_id,
            'sum_number' => $card->visanet_card_hash,
            ],
            'doctor' => $card
        ]);
    }


    /**
     * Internal: Delete doctor card local and visanet subscription
     *
     * @param [type] $doctor_id
     * @return void
     */
    private function delete_doctor_card($doctor_id) {

        $card = $this->Doctor_Cards_Model->get_card($doctor_id);
        $c = $this->gateway($doctor_id, true);
        if ($card && trim($card->visanet_subscription_id) != '') {
            try {
                $c->reference_code( time() );
                $c->delete_subscription($card->visanet_subscription_id);
                $this->Doctor_Cards_Model->delete_card($doctor_id);
            } catch ( Exception $e ) {
                // return $this->jsonResponse([
                //     'error' => $e->getCode() . ': ' . $e->getMessage() . '<br/>' . PHP_EOL
                // ]);
            }
        }

        return true;

    }

    /**
     * Delete doctor card
     *
     * @return void
     */
    public function  visanet_delete_card($doctor_id) {
       
        $this->delete_doctor_card($doctor_id);

        $card = $this->Doctor_Cards_Model->get_card($doctor_id);
        $type = array_search ($card->visanet_card_type , $this->gateway($doctor_id)->card_types);
        $type = !$type ? "--": $type;
        return $this->jsonResponse([
            'card' => [
                'type' =>   $type,
                'subscription_id' => $card->visanet_subscription_id,
                'sum_number' => $card->visanet_card_hash,
            ],
            'doctor' => $card,
            'deleted' => $card ? 'success': 'already deleted',
            'message' => ''
        ]);
    }


    /**
     * Get visanet card types
     *
     * @return void
     */
    public function visanet_get_card_types($doctor_id) {
        $data = $this->gateway($doctor_id);
        return $this->jsonResponse($data->card_types);
    }

    /** 
     * Create doctor card
     *
     * @return void
     */
    public function  dead__visanet_create_token() {

        // if (!$_POST) {
        //     return $this->jsonResponse([
        //         'error' => 'Method dont allowed'
        //     ]);
        // }
        // $c = $this->gateway();
        
        // $data = $_POST;

        // if (!isset($data['doctor_id'])) {
        //     return $this->jsonResponse([
        //         'error' => 'Missing doctor_id'
        //     ]);
        // }
        
        // $number = $data['number'];
        // $expiration_month = $data['expiration_month'];
        // $expiration_year = $data['expiration_year'];
        // $cvn_code = $data['cvn_code'];
        // $card_type = $c->card_type( $data['number']);//$data['card_type'];
        // $doctor_id = $data['doctor_id'];

        // $this->delete_doctor_card($doctor_id);

        // $c->card( $number, $expiration_month, $expiration_year, $cvn_code, $card_type )
        // ->bill_to([
        //     'firstName' => $data['billing_firstName'],
        //     'lastName' => $data['billing_lastName'],
        //     'street1' => $data['billing_street1'],
        //     'city' => $data['billing_city'],
        //     'state' => $data['billing_state'],
        //     'postalCode' => $data['billing_postalCode'],
        //     'country' => $data['billing_country'],
        //     'email' => $data['billing_email'],
        // ]);

        // $tomorrow = new DateTime('tomorrow');

        // $c->recurring( array(
        //     'frequency'        => 'monthly',
        //     'amount'           => '750.00', // Amount to redefine based on plan 
        //     'currency'         => 'DOP',
        //     'startDate'        => $this->bill_date($doctorEndTrial),
        // ));
        
        // $c->reference_code( time() );
        // try {
        //     // $subscription = $c->create_subscription();
        //     // /////
        //         $c->reference_code( time() );
        //         $subscription = $c->recurring_subscription();
        //     ////

        //     if ( !is_numeric( $card_type ) ) {
        //         $card_type = $c->card_types[ $card_type ];
        //     }
           
        //     $this->Doctor_Cards_Model->add_doctor_card($doctor_id, [
        //         'visanet_subscription_id' => $subscription->paySubscriptionCreateReply->subscriptionID,	
        //         'visanet_card_hash' => $this->getCardHash($data['number']),	
        //         'visanet_card_type' => $card_type
        //     ]);

        //     $card = $this->Doctor_Cards_Model->get_card($doctor_id);
        //     $type = array_search ($card->visanet_card_type , $this->gateway()->card_types);
        //     $type = !$type ? "--": $type;
            
        //     return $this->jsonResponse([
        //         'data' => [
        //             'subscription' => $subscription->paySubscriptionCreateReply->subscriptionID,
        //             'request' => $c->request,
        //             'response' => $c->response
        //         ],
        //         'card' => [
        //             'type' =>   $type,
        //             'subscription_id' => $card->visanet_subscription_id,
        //             'sum_number' => $card->visanet_card_hash,
        //         ],
        //     ]);
        // } catch ( Exception $e ) {
        //     return $this->jsonResponse([
        //         'error' => $e->getCode() . ': ' . $e->getMessage() . '<br/>' . PHP_EOL
        //     ]);
        // }

    }

    private function getCardHash($var) {
        return '**** **** **** '.substr($var,-4);
    }

    /**
     * Retrive subscription info from visanet
     *
     * @param [type] $subscription_id
     * @return void
     */
    public function visanet_retrive($doctor_id, $subscription_id) {
        $c = $this->gateway($doctor_id, true);
        // print_r($c);die();
        try {

            $c->reference_code($subscription_id);
            $subscription = $c->retrieve_subscription($subscription_id);
            print_r($subscription);
        }
        catch ( Exception $e ) {
            echo $e->getCode() . ': ' . $e->getMessage() . '<br />';
        }
    }
}

?>