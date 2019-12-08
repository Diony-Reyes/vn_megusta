<?php 

/**
 * Visanet security HMAC
 */
trait Visanet_Security {


    // public $mode = "development"; // production|development

    public $dev = 'development';
    public $prod = 'production';

    public function getMode() {
        return getenv('VISANET_MODE');
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function redirectUriBuscamed() {
        $url = "http://localhost/webservice/vn_catch_payment";
        if ($this->getMode() == $this->prod) {
            $url = "https://buscamed.do/webservice/vn_catch_payment";
        }
        return $url;
    }

    private function vault() {
        // prod
        $credentials = (object)[
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
        if ($this->getMode() == $this->prod) {
            $credentials = (object)[
                'merchant_id' => 'visanetdr_000000430807001',
                'profile_id'=> 'E27A421D-A3E5-4F25-83A0-76D7600E5C7A',
                'org_dev'=> 'k8vif92e',
                // 'org_dev'=> '1snn5n9w',
                'org_live'=> 'k8vif92e',
                'secret_key' => '1677ee713b3840edb71d7209404d0a82d154d27fabde43e1b90b252f7adc7d906bf612026f8f4419bb57c3959b25a84233417f3112524eea92d20a2f9c81ad09120783c33ce04b7fa9568fda6725d620f06c230a48364c8b8ac137b11d898e4f43edb2aa0c264d5c8369e815c97a541e5c35add83faa492aa544273a6f42efd8',
                'access_key'=> '4cf48e39d47f36abaf8bf59e0c71ea0d',
                'transaction_uuid'=>  uniqid(),
                'signed_date_time'=> gmdate("Y-m-d\TH:i:s\Z"),
                'transaction_key' => 'sQLVxgYZCZxIVtt1ULtZ5yhrKvcRy6ibrKIgpF7nw5gcL1VYvuQ58HbbHnaAwqD7sZMmSGlDbjI/WS5Y3C9MR2jig787y0rLaDvVDfO+igYfHpDyVR9mSXBbAHOfxYytx5mwb0JXoIFkdxSawD9ZcvGqTtumTR+TtPUxHCZ2U1WIhZiLuF8os2iGLSTU6XXcWXcqa/0cev7NrOuUkNl06D8xelHjma88KVyOrPKurwYPIT5I/k6D0GEUdK0BCP/iYgUmMlSAJet1nDPRaKZtECcrv2jpKWwZZl4+LDJcf8jCG09PrcaVQVPDDfbLAB+xJjD44TziucvxhIUuTdMP4g=='
            ];
        }
        return $credentials;
        // dev
        // return (object)[
        //     'org_dev'=> '1snn5n9w',
        //     'org_live'=> 'k8vif92e',
        //     'secret_key' => '7bf10c540f08438e8ec4f9e3d2959c8a9af5cb18e69c474c85be66a4fc4be4bb713257dd94c64f66b3e7a93401a719d6908bd73dc3cc45e1a2a97b36978c4244cfc05243c3424b529b326dedb6c5ecc192fae525d24143529907928d17fe1e836dddfde958dc47e889da876608861dafb89cef95133146fe87dec264283d605a',
        //     'access_key'=> '8456a8f5ecb83ba4a25ec239d6ae0a71',
        //     'profile_id'=> '47EC6C53-9B6F-47AA-89AA-70CE446D13AD',
        //     'transaction_uuid'=>  uniqid(),
        //     'signed_date_time'=> gmdate("Y-m-d\TH:i:s\Z"),
        //     'merchant_id' => 'visanetdr_000000430807001',
        //     'transaction_key' => '/QPGAXqTWDmA633HMdjImXLShlw9epYy0O6cS8MBXOlNwK8rOKBJy26cRT7Bk09euVTMS1r+mAQVznGOMXEzzugEIA8z+DhJ/fw4co7wNnFf1y03iYTgT6gfo9D+072lf2oALc1rmztg65t747QXTPZpbMkCplh9ZODo+4YCDIFifPQIe1XRBjOarhwAGHUIFSfdM33zFlciXrshxUyvesfAg0Twxx/B0RUSjuJ2jOS4nxyq4hDPU9UC7wSwoJMB3tQNOaUUWkQkdKm+/IdCwCZgSkFcyGVNhySwyHqXh/oIm/mz1ud2KrZ29/l9PlOg3GglO/UceZMcdxXekjfe5w==',
            
        // ];

        // 'sub_1' => (object)[
            //     'transaction_uuid'=>  uniqid(),
            //     'merchant_id' => 'visanetdr_000000423794001',
            //     'profile_id' => 'E5452525-FAEB-433A-A3C6-0991DD343B71',
            //     'access_key' => '2429d21e3938349a8b2a0b5f759d9c4a',
            //     'secret_key' => 'afdf2f2cc9cd487a881c3d18868ca1e6a9147e572a4a477b905630d44d199e4786238103ab834824a1c0307422af6d3d53ef684e60cc40d49a7d7c21259354ff18ed225c4ef44177bb25846c18d64dbd4955e8e052a641a09fbddd75f13319146eac3149d5fa404b942ad787e88055483c3fce7fb98b41c58ab59b40faac971d',
            //     'transaction_key' => '7cXgLACwIetjVKihg7Zgmi3ks4htudbxHgNAvaJErKBiHSYx1SVDAOCm4c+pIUyYy70vsp8DkMpGPnkHA5q3IuNyMJvBKMnlnECITfCbZHeIBQ+4CcaYteOGnkNO0n45dBdAVroW5JOKdDn1ahI/sAZwQtcfG7tterWG2M/Ew2m0h7ZOtUTxFgJnHtp7R5KMqg1GHwgN5/hkDloNqS3M/0kBVY/jQKdEnCDI2m0xYCIlm3agp/VW9UX2cSS95wZNN3sZHuTBEJMnCQ7a05DF9Vrtbpct5RhB6Ks6uIesDZGUlxjPUUfPafv7WeuVssLcl0wVteULRNW0r4As6kfuxA==',

            // ],
            // 'sub_2' => (object)[
            //     'transaction_uuid'=>  uniqid(),
            //     'signed_date_time'=> gmdate("Y-m-d\TH:i:s\Z"),
            //     'merchant_id' => 'visanetdr_000000423932001',
            //     'profile_id' => 'A50CCD1C-36F9-4963-9D8D-E9641F77A533',
            //     'access_key' => '7ac50551161d3492b67cbaacecfb1d59',
            //     'secret_key' => '612e41ad1d4d481ba8c9dfff819a60108095f124d82e4ffc843a398643dad39bda2b585c5293405a8cd933a89524e3e040e1e16bd5b54b52aaf9ee1b7660b6d86a617ebddf7249bea37387fa40cbf3ff6c6e317a0b9f4ee0ae52f8cfb7167f8794f119d634ca490290e71ef1ad33d52f59f78330428547bbb24a53ea93f131f1',
            //     'transaction_key' => 'NyNlCXw7y3jIRy6YZG8OxzdJpgRSJ3Wr0gKazPuTfXfHnsIHBD7AXryMPExSv+Hbu8fdzsgmkLUbtC/gz4gcFU6fHLFnIS0Z3SgH8xl9e/QlISLSo0u1u63yQcqMy+OK9+H5zAvKuvgr2IPj0hS2fcLORSxESBoULUSWFmHUkqHVP6368nt8pkrpO7VH8Cr3sEjvCiQKxczANN39B0mZDsl5VPlQxhBoa7kpbbcxPlK1jcyWLoBvOSQ0PXtHpXJTK0s2YDIFkL4MfrqsjTBuUTRPFEtaMGjAZzqm5BdVN2tWeDyOT5t/4k714w+vlFViZTbB/djlMG1DreK3me2uYg==',

    }

    private function sign ($params, $secretKey) {
      return $this->signData($this->buildDataToSign($params), $secretKey);
    }
    
    private function signData($data, $secretKey) {
        return base64_encode(hash_hmac('sha256', $data, $secretKey, true));
    }
    
    private function buildDataToSign($params) {
            $signedFieldNames = explode(",",$params["signed_field_names"]);
            foreach ($signedFieldNames as $field) {
               $dataToSign[] = $field . "=" . $params[$field];
            }
            return $this->commaSeparate($dataToSign);
    }
    
    private function commaSeparate ($dataToSign) {
        return implode(",",$dataToSign);
    }


    public function encrypt_t($password, $text){
        return base64_encode($text);
        // return openssl_encrypt($text,"idea",$password);
    }

    public function decrypt_t($password, $encrypted_text){
        return (base64_decode($encrypted_text));
        // return (openssl_decrypt($encrypted_text,"idea",$password));
    }


}

?>