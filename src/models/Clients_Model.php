<?php 
    trait Clients_Model {
        public function get_client($id) {
            $connect = self::connectorDB();

            $sql = "SELECT firstName, lastName, email, phone
                    FROM cpnc_User
                    WHERE id = {$id}";
            $result = $connect->query($sql);

            return  $result->fetch(PDO::FETCH_LAZY);
        }
        public function getamountbyorder($orderid) {
            $connect = self::connectorDB();

            $sql = "SELECT amount
                    FROM cpnc_PaymentOrder
                    WHERE id = {$orderid}";
            $result = $connect->query($sql);

            return  $result->fetch(PDO::FETCH_LAZY);
        }
       
        public function add_card_c($data) {
            $connect = self::connectorDB();
            __Database::__insert('vn_patient_cards', $data);
            return  true;
        }
       
        public function generateCoupon($data) {
            $connect = self::connectorDB();
            __Database::__insert('cpnc_DealCoupon', $data);
            return  true;
        }
       
        public static function getoreritems($order_id) {
            $connect = self::connectorDB();

            $sql = "SELECT * FROM cpnc_PaymentOrderItem where orderId = {$order_id}";
            $result = $connect->query($sql);
            // return [];
            return  $result->fetchAll(PDO::FETCH_OBJ);
        }
        public static function getoreritems_with_deals($order_id) {
            $connect = self::connectorDB();

            if ($order_id == 0) {
                return [];
            } else {
                $sql = "SELECT * 
                    FROM cpnc_PaymentOrderItem 
                    inner join cpnc_Deal on cpnc_PaymentOrderItem.itemId = cpnc_Deal.id
                    inner join cpnc_DealI18N on cpnc_PaymentOrderItem.itemId = cpnc_DealI18N.dealId and cpnc_DealI18N.name = 'name'  
                    where orderId = {$order_id};";
                $result = $connect->query($sql);
                // return [];
                return  $result->fetchAll(PDO::FETCH_OBJ);

            }
        }


        public function validPayment($order, $auth_code) {
            $connect = self::connectorDB();
            __Database::__update('cpnc_PaymentOrder', [
                'status' => '2',
                'custom' => $auth_code
            ],  " id = {$order}");

            // $sql = "UPDATE `cpnc_PaymentOrder` SET `status` = '2' WHERE `cpnc_PaymentOrder`.`id` = {$order};";
            // $result = $connect->query($sql);
            return  true;
            return  $result->fetch(PDO::FETCH_LAZY);
        }

       
        public function updatePurchaseCounter($deal_id) {
            $connect = self::connectorDB();
            $sql = "UPDATE `cpnc_DealStats` SET `bought` = (COALESCE(bought, 0) + 1) WHERE `cpnc_DealStats`.`id` = {$deal_id}";
            $result = $connect->query($sql);
            return  true;
        }
    }
?>