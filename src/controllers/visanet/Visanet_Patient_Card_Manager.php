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

        $card =  $this->get_card($card_id);

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
            
            $whereClause = " WHERE id = {$card_id} AND patient_id = {$patient_id}";
            __Database::__delete("vn_patient_cards", $whereClause);
            // $this->VN_Patient_Cards_Model->delete_card($patient_id, $card_id);
        }

        return $this->jsonResponse( $this->get_cards($patient_id));
    }

    /**
     * Make patient preferred card
     *
     * @param [type] $patient_id
     * @param [type] $card_id
     * @return void
     */
    public function vc_make_preferred_card($patient_id, $card_id) {
        $whereClausePreferred = " WHERE id = {$card_id} AND patient_id = {$patient_id}";
        $whereClauseUnpreferred = " WHERE patient_id = {$patient_id}";
        $data_preferred = ['preferred' => 1];
        $data_unpreferred = ['preferred' => 0];

        __Database::__update("vn_patient_cards", $data_unpreferred, $whereClauseUnpreferred);
        __Database::__update("vn_patient_cards", $data_preferred, $whereClausePreferred);
        return $this->jsonResponse( $this->get_cards($patient_id));
    }

    /**
     * Get patient transactions
     *
     * @param [type] $patient_id
     * @return void
     */
    public function vc_patient_transactions($patient_id) {
        $this->jsonResponse($this->get_transactions($patient_id));
    }

  
}

?>