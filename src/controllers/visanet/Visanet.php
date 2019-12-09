<?php 

require 'classes/CyberSource/CyberSource.php';
require 'classes/CyberSource/Reporting.php';
require 'Visanet_Payment_Manager.php';
require 'Visanet_Form.php';
require 'Visanet_Card_Manager.php';
require 'Visanet_Security.php';

/**
 * Visanet 
 */
trait Visanet {

    use Visanet_Security;
    use Visanet_Payment_Manager;
    use Visanet_Form;
    use Visanet_Card_Manager;

    private function gateway($doctor_id = null, $buscamed = false) {
        $doctor = $this->get_doctor_data($doctor_id);
        //  `vn_merchant_id` TEXT NULL AFTER `visanet_card_type`, 
        //  `vn_profile_id` TEXT NULL AFTER `vn_merchant_id`, 
        //  `vn_access_key` TEXT NULL AFTER `vn_profile_id`, 
        //  `vn_secret_key` TEXT NULL AFTER `vn_access_key`, 
        //  `vn_soap_key` TEXT NULL AFTER `vn_secret_key`, 
        //  `vn_is_enabled` INT NOT NULL DEFAULT '0' AFTER `vn_soap_key`, 
        //  `vn_is_active` INT NOT NULL DEFAULT '0' AFTER `vn_is_enabled`;
        $username = '';
        $password = '';
        if ($buscamed) {
            // $c = CyberSource\CyberSource::factory( $this->vault()->merchant_id, $this->vault()->transaction_key, CyberSource\CyberSource::ENV_LIVE);
            $c = CyberSource\CyberSource::factory( $this->vault()->merchant_id, $this->vault()->transaction_key, CyberSource\CyberSource::ENV_TEST);
        } else {
            // $c = CyberSource\CyberSource::factory( $doctor->vn_merchant_id, $doctor->vn_soap_key, CyberSource\CyberSource::ENV_LIVE);
            $c = CyberSource\CyberSource::factory( $doctor->vn_merchant_id, $doctor->vn_soap_key, CyberSource\CyberSource::ENV_TEST);
        }
        $c->default_currency = 'DOP';
        return $c;
    }
}

?>