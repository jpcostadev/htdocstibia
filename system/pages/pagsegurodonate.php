<?php
global $config, $db, $account_logged, $logged;
defined('MYAAC') or die('Direct access not allowed!');
$title = 'Comprar coins com InfinitePay';
if (!$logged) { http_response_code(401); echo 'Entre na sua conta para comprar coins.'; return; }
require_once PLUGINS . 'pagseguro/config.php';
require_once PLUGINS . 'infinitepay/infinitepay.php';
$code = (string)($_POST['code'] ?? '');
$offer = $config['pagSeguro']['donates'][$code] ?? null;
if (!$offer) { warning('Pacote de coins inválido.'); return; }
infinitepayEnsureTable($db);
$accountId = (int)$account_logged->getId();
$baseCoins = (int)$offer['coins']; $extra = (int)$offer['extra'];
$double = !empty($config['pagSeguro']['doubleCoins']) && $baseCoins >= (int)$config['pagSeguro']['doubleCoinsStart'];
$coins = ($double ? $baseCoins * 2 : $baseCoins) + $extra;
$amount = (int)round(((float)$offer['value']) * 100);
$orderNsu = 'renfall-' . $accountId . '-' . bin2hex(random_bytes(12));
$db->exec("INSERT INTO `infinitepay_orders` (`order_nsu`,`account_id`,`offer_code`,`amount`,`coins`,`status`,`created_at`,`updated_at`) VALUES (" . $db->quote($orderNsu) . ",{$accountId}," . $db->quote($code) . ",{$amount},{$coins},'PENDING',NOW(),NOW())");
$payload = ['handle'=>$config['infinitePay']['handle'],'redirect_url'=>BASE_URL.'?subtopic=donate&action=final','webhook_url'=>rtrim(BASE_URL,'/').'/payments/infinitepay.php','order_nsu'=>$orderNsu,'items'=>[['quantity'=>1,'price'=>$amount,'description'=>$coins.' Renfall Coins']]];
try {
    $response = infinitepayRequest('/links', $payload);
    $checkoutUrl = $response['url'] ?? $response['checkout_url'] ?? $response['link'] ?? null;
    if (!$checkoutUrl || !filter_var($checkoutUrl, FILTER_VALIDATE_URL)) throw new RuntimeException('A InfinitePay não devolveu o link do checkout.');
    $db->exec("UPDATE `infinitepay_orders` SET `checkout_url`=".$db->quote($checkoutUrl).",`payload`=".$db->quote(json_encode($response)).",`updated_at`=NOW() WHERE `order_nsu`=".$db->quote($orderNsu));
    header('Location: '.$checkoutUrl, true, 303); exit;
} catch (Throwable $e) {
    $db->exec("UPDATE `infinitepay_orders` SET `status`='ERROR',`payload`=".$db->quote($e->getMessage()).",`updated_at`=NOW() WHERE `order_nsu`=".$db->quote($orderNsu));
    log_append('infinitepay_errors.log', date('c').' '.$orderNsu.' '.$e->getMessage());
    error('Não foi possível abrir o pagamento: '.htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
