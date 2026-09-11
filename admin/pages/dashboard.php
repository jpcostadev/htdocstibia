<?php
global $db, $twig, $twig_loader, $status;
/**
 * Dashboard
 *
 * @package   MyAAC
 * @author    Slawkens <slawkens@gmail.com>
 * @copyright 2023 MyAAC
 */
defined('MYAAC') or die('Direct access not allowed!');
$title = 'Visão geral';

if (isset($_GET['clear_cache'])) {
    if (clearCache()) {
        success('Cache limpo com sucesso.');
    } else {
        error('Não foi possível limpar o cache.');
    }
}

if (isset($_GET['maintenance'])) {
    $_status = (int)$_POST['status'];
    $message = $_POST['message'];
    if (empty($message)) {
        error('A mensagem de manutenção não pode ficar vazia.');
    } else if (strlen($message) > 255) {
        error('A mensagem pode ter no máximo 255 caracteres.');
    } else {
        $tmp = '';
        if (fetchDatabaseConfig('site_closed', $tmp))
            updateDatabaseConfig('site_closed', $_status);
        else
            registerDatabaseConfig('site_closed', $_status);

        if (fetchDatabaseConfig('site_closed_message', $tmp))
            updateDatabaseConfig('site_closed_message', $message);
        else
            registerDatabaseConfig('site_closed_message', $message);
    }
}
$is_closed = getDatabaseConfig('site_closed') == '1';

$closed_message = 'O site está em manutenção. Tente novamente mais tarde.';
$tmp = '';
if (fetchDatabaseConfig('site_closed_message', $tmp))
    $closed_message = $tmp;

$total_accounts = (int)$db->query('SELECT COUNT(*) FROM `accounts`')->fetchColumn();
$total_players = (int)$db->query('SELECT COUNT(*) FROM `players`')->fetchColumn();
$total_guilds = (int)$db->query('SELECT COUNT(*) FROM `guilds`')->fetchColumn();
$total_houses = (int)$db->query('SELECT COUNT(*) FROM `houses`')->fetchColumn();
$total_donates = $db->hasTable('pagseguro_transactions')
    ? (int)$db->query("SELECT COUNT(*) FROM `pagseguro_transactions` WHERE `payment_status` <> 'CANCELLED'")->fetchColumn()
    : null;

$twig->display('admin.statistics.html.twig', array(
    'total_accounts' => $total_accounts,
    'total_players' => $total_players,
    'total_guilds' => $total_guilds,
    'total_houses' => $total_houses,
    'total_donates' => $total_donates,
));

$twig->display('admin.dashboard.html.twig', array(
    'is_closed' => $is_closed,
    'closed_message' => $closed_message,
    'status' => $status,
    'account_type' => USE_ACCOUNT_NAME ? 'name' : 'number'
));

echo '<div class="row">';

$configAdminPanelModules = config('admin_panel_modules');
if (isset($configAdminPanelModules))
    $configAdminPanelModules = explode(',', $configAdminPanelModules);

$twig_loader->prependPath(__DIR__ . '/modules/templates');
foreach ($configAdminPanelModules as $box) {
    $file = __DIR__ . '/modules/' . $box . '.php';
    if (file_exists($file)) {
        include($file);
    }
}
echo '</div>';
