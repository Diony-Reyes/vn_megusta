<?php 

/**
 * Visanet 
 */

trait Visanet_Patient_Card_Manager {
    /**
     * Get preferred card
     *
     * @param [type] $patient_id
     * @return void
     */
    public function vn_patient_get_preferred($patient_id) {
        $prefferred =  $this->get_preferred($patient_id);
        $this->jsonResponse($prefferred);
    }


    public function vn_patient_cards($patient_id) {
        $result = $this->get_cards($patient_id);
        $this->jsonResponse($result);
    }
    public function vn_patient_cards_fn($patient_id) {
        $result = $this->get_cards($patient_id);
        return $result;
    }

    /**
     * List patient cards
     *
     * @param [type] $patient_id
     * @return void
     */
    public function vc_list_cards($patient_id) {
        return $this->jsonResponse($this->get_cards($patient_id));
    }

    /**
     * Delete patient card
     *
     * @param [type] $patient_id
     * @param [type] $card_id
     * @return void
     */
    public function vc_delete_card($patient_id, $card_id) {
        
        $c = $this->gateway(null, true);
       $card =  $this->VN_Patient_Cards_Model->get_card($card_id);
        if ($card && trim($card->subscription_id) != '') {
            try {
                $c->reference_code( time() );
                $c->delete_subscription($card->subscription_id);
                // $this->VN_Patient_Cards_Model->delete_card($patient_id, $card_id);
            } catch ( Exception $e ) {
               
                // return $this->jsonResponse([
                //     'error' => $e->getCode() . ': ' . $e->getMessage() . '<br/>' . PHP_EOL
                // ]);
            }
            $this->VN_Patient_Cards_Model->delete_card($patient_id, $card_id);
        }

        return $this->jsonResponse( $this->VN_Patient_Cards_Model->get_cards($patient_id));
    }

    /**
     * Make patient preferred card
     *
     * @param [type] $patient_id
     * @param [type] $card_id
     * @return void
     */
    public function vc_make_preferred_card($patient_id, $card_id) {
        $this->VN_Patient_Cards_Model->update_massive_preferred_0($patient_id);
        $this->VN_Patient_Cards_Model->update_card($card_id, [
            'preferred' => 1
        ]);
        return $this->jsonResponse( $this->VN_Patient_Cards_Model->get_cards($patient_id));
    }

    /**
     * Get patient transactions
     *
     * @param [type] $patient_id
     * @return void
     */
    public function vc_patient_transactions($patient_id) {
        return $this->jsonResponse( $this->VN_Patient_Transactions_Model->get_transactions($patient_id));
    }

  
}

?>