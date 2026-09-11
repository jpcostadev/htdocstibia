<?php
defined('MYAAC') or die('Direct access not allowed!');
$config['infinitePay']=['handle'=>'fallzdev','api_url'=>'https://api.checkout.infinitepay.io','donation_type'=>'coins_transferable'];
function infinitepayEnsureTable($db): void {
    $db->exec("CREATE TABLE IF NOT EXISTS `infinitepay_orders` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,`order_nsu` VARCHAR(80) NOT NULL,`account_id` INT NOT NULL,`offer_code` VARCHAR(32) NOT NULL,`amount` INT NOT NULL,`coins` INT NOT NULL,`status` VARCHAR(20) NOT NULL DEFAULT 'PENDING',`transaction_nsu` VARCHAR(100) NULL,`invoice_slug` VARCHAR(150) NULL,`checkout_url` TEXT NULL,`payload` MEDIUMTEXT NULL,`delivered` TINYINT(1) NOT NULL DEFAULT 0,`created_at` DATETIME NOT NULL,`updated_at` DATETIME NOT NULL,PRIMARY KEY (`id`),UNIQUE KEY `uq_infinitepay_order` (`order_nsu`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}
function infinitepayRequest(string $path,array $payload): array {
    global $config; $ch=curl_init(rtrim($config['infinitePay']['api_url'],'/').$path);
    curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_TIMEOUT=>25,CURLOPT_HTTPHEADER=>['Accept: application/json','Content-Type: application/json'],CURLOPT_POSTFIELDS=>json_encode($payload,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)]);
    $body=curl_exec($ch); $status=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); $error=curl_error($ch); curl_close($ch); $data=json_decode((string)$body,true);
    if($body===false||$status<200||$status>=300||!is_array($data)){ $message=is_array($data)?($data['message']??$data['error']??json_encode($data)):($error?:(string)$body); throw new RuntimeException("InfinitePay HTTP {$status}: {$message}"); }
    return $data;
}
