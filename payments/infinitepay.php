<?php
global $config,$db;
require_once '../common.php'; require_once SYSTEM.'functions.php'; require_once SYSTEM.'init.php'; require_once PLUGINS.'infinitepay/infinitepay.php';
header('Content-Type: application/json; charset=utf-8');
try {
    if($_SERVER['REQUEST_METHOD']!=='POST') throw new RuntimeException('Método inválido');
    $event=json_decode(file_get_contents('php://input'),true); if(!is_array($event)) throw new RuntimeException('JSON inválido');
    $orderNsu=(string)($event['order_nsu']??''); $transactionNsu=(string)($event['transaction_nsu']??''); $slug=(string)($event['invoice_slug']??$event['slug']??'');
    if($orderNsu===''||$transactionNsu===''||$slug==='') throw new RuntimeException('Identificadores ausentes');
    infinitepayEnsureTable($db); $db->beginTransaction();
    $order=$db->query("SELECT * FROM `infinitepay_orders` WHERE `order_nsu`=".$db->quote($orderNsu)." FOR UPDATE")->fetch(); if(!$order) throw new RuntimeException('Pedido não encontrado');
    if((int)$order['delivered']===1){$db->commit();echo json_encode(['success'=>true,'message'=>null]);exit;}
    $check=infinitepayRequest('/payment_check',['handle'=>$config['infinitePay']['handle'],'order_nsu'=>$orderNsu,'transaction_nsu'=>$transactionNsu,'slug'=>$slug]);
    if(empty($check['paid'])||(int)($check['amount']??0)!==(int)$order['amount']) throw new RuntimeException('Pagamento não confirmado ou valor divergente');
    $accountId=(int)$order['account_id']; $coins=(int)$order['coins']; $field=$config['infinitePay']['donation_type']; if(!in_array($field,['coins','coins_transferable'],true)) throw new RuntimeException('Campo de coins inválido');
    $db->exec("UPDATE `accounts` SET `{$field}`=`{$field}`+{$coins} WHERE `id`={$accountId}");
    $db->exec("UPDATE `infinitepay_orders` SET `status`='PAID',`transaction_nsu`=".$db->quote($transactionNsu).",`invoice_slug`=".$db->quote($slug).",`payload`=".$db->quote(json_encode(['webhook'=>$event,'check'=>$check])).",`delivered`=1,`updated_at`=NOW() WHERE `id`=".(int)$order['id']);
    $db->exec("INSERT INTO `coins_transactions` (`account_id`,`type`,`amount`,`description`,`timestamp`,`coin_type`) VALUES ({$accountId},1,{$coins},'InfinitePay',NOW(),3)");
    $db->commit(); echo json_encode(['success'=>true,'message'=>null]);
} catch(Throwable $e){if($db&&$db->inTransaction())$db->rollBack();log_append('infinitepay_webhook_errors.log',date('c').' '.$e->getMessage());http_response_code(400);echo json_encode(['success'=>false,'message'=>$e->getMessage()]);}
