<?php 
require 'Visanet_Doctor_Card_Manager.php';
require 'Visanet_Patient_Card_Manager.php';
/**
 * Visanet 
 */
trait Visanet_Card_Manager {
    use Visanet_Doctor_Card_Manager;
    use Visanet_Patient_Card_Manager;
}

?>