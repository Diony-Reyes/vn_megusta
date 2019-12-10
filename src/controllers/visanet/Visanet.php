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

    public function gateway($doctor_id = null, $buscamed = false) {
 
            // $c = CyberSource\CyberSource::factory( $this->vault()->merchant_id, $this->vault()->transaction_key, CyberSource\CyberSource::ENV_LIVE);
        $c = CyberSource\CyberSource::factory( $this->vault()->merchant_id, $this->vault()->transaction_key, CyberSource\CyberSource::ENV_TEST);
        // } else {
        //     // $c = CyberSource\CyberSource::factory( $doctor->vn_merchant_id, $doctor->vn_soap_key, CyberSource\CyberSource::ENV_LIVE);
        //     $c = CyberSource\CyberSource::factory( $doctor->vn_merchant_id, $doctor->vn_soap_key, CyberSource\CyberSource::ENV_TEST);
        // }
        $c->default_currency = 'DOP';
        return $c;
    }
}

?>