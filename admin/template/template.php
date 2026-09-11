<?php
global $config;
defined('MYAAC') or die('Direct access not allowed!'); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php
    echo template_header(true);
    $title_full = (isset($title) ? $title . $config['title_separator'] : '') . $config['lua']['serverName'];
    ?>

    <title><?= $title_full ?></title>
    <link rel="shortcut icon" href="<?= BASE_URL; ?>images/favicon.ico" type="image/x-icon"/>
    <link rel="icon" href="<?= BASE_URL; ?>images/favicon.ico" type="image/x-icon"/>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <link rel="stylesheet" href="<?= BASE_URL; ?>admin/bootstrap/css/bootstrap.min.css"> <!-- BOOTSTRAP 5 -->
    <link rel="stylesheet" href="<?= BASE_URL; ?>admin/bootstrap/bootstrap-myaac.css"> <!-- CUSTOM -->
    <link rel="stylesheet" href="<?= BASE_URL; ?>tools/css/AdminLTE.min.css">
    <link rel="stylesheet" href="<?= BASE_URL; ?>tools/css/skins/skin-blue.min.css">

    <link rel="stylesheet" href="<?= BASE_URL; ?>tools/fonts/fontawesome/all.css">

    <link rel="stylesheet" href="<?= BASE_URL; ?>tools/css/ionicons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL; ?>tools/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="<?= $template_path; ?>style.css"/>
    <link rel="stylesheet" type="text/css" href="<?= $template_path; ?>renfall-admin.css?v=1"/>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
    <?php if ($logged && admin()) { ?>
    <header class="main-header">
        <a href="." class="logo">
            <span class="logo-mini"><b>R</b>F</span>
            <span class="logo-lg"><b>RENFALL</b></span>
        </a>

        <nav class="navbar navbar-static-top" role="navigation">
            <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                <span class="sr-only">Abrir ou fechar menu</span>
            </a>
            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <li>
                        <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    <aside class="main-sidebar">
        <section class="sidebar">
            <ul class="sidebar-menu" data-widget="tree">
                <li class="header">PAINEL RENFALL <span>v<?= MYAAC_VERSION ?></span></li>

                <?php
                $icons_a = array(
                    'dashboard', 'newspaper', 'envelope',
                    'book', 'user', 'list',
                    'plug', 'user',
                    'edit', 'gavel',
                    'wrench', 'edit',
                );

                $menus = array(
                    'Renfall' => array(
                        'Add Account Coins' => 'add_account_coins',
                        'Add Player Coins' => 'add_player_coins',
                    ),
                    'Visão geral' => 'dashboard',
                    'Notícias' => 'news',
                    'Mensagens' => 'mailer',
                    'Páginas' => 'pages',
                    'Configurações' => 'modifiers',
                    'Menus' => 'menus',
                    'Plugins' => 'plugins',
                    'Visitantes' => 'visitors',
                    'Editor' => array(
                        'Contas' => 'accounts',
                        'Jogadores' => 'players',
                    ),
                    'Itens' => 'items',
                    'Ferramentas' => array(
                        'Doações' => 'pag_transactions',
                        'Atualizar Premium/VIP' => 'premiumvipupdater',
                        'Bloco de notas' => 'notepad',
                        'phpinfo' => 'phpinfo',
                        //'Premium/VIP Fixer' => 'fixvippremiumnewsystem', //Unused function (used to fix new vip/premium) system
                    ),
                    'Logs' => array(
                        'Logs' => 'logs',
                        'Relatórios' => 'reports',
                    ),
                );

                $i = 0;
                foreach ($menus as $_name => $_page) {
                    $has_child = is_array($_page);
                    if (!$has_child) {
                        echo '<li ';
                        if ($page == $_page) echo ' class="active"';
                        echo ">";
                        echo '<a href="?p=' . $_page . '"><i class="fa fa-' . (isset($icons_a[$i]) ? $icons_a[$i] : 'link') . '"></i> <span>' . $_name . '</span></a></li>';
                    }

                    if ($has_child) {
                        $used_menu = "";
                        $nav_construct = '';
                        foreach ($_page as $__name => $__page) {
                            $nav_construct = $nav_construct . '<li';

                            if ($page == $__page) {
                                $nav_construct = $nav_construct . ' class="active"';
                                $used_menu = true;
                            }
                            $nav_construct = $nav_construct . '><a href="?p=' . $__page . '"><i class="fa fa-circle-o"></i> ' . $__name . '</a></li>';
                        }

                        echo '<li class="treeview' . (($used_menu) ? ' menu-open' : '') . '">
                                      <a href="#"><i class="fa fa-' . (isset($icons_a[$i]) ? $icons_a[$i] : 'link') . '"></i> <span>' . $_name . '</span>
						              <span class="float-end"><i class="fa fa-angle-left float-end"></i></span></a>
						              <ul class="treeview-menu" style="' . (($used_menu) ? '  display: block' : ' display: none') . '">';
                        echo $nav_construct;
                        echo '</ul>
                                </li>';
                    }
                    $i++;
                }

                $query = $db->query('SELECT `name`, `page`, `flags` FROM `' . TABLE_PREFIX . 'admin_menu` ORDER BY `ordering`');
                $menu_db = $query->fetchAll();
                foreach ($menu_db as $item) {
                    if ($item['flags'] == 0 || hasFlag($item['flags'])) {
                        echo '<li ';
                        if ($page == $item['page']) echo ' class="active"';
                        echo ">";
                        echo '<a href="?p=' . $item['page'] . '"><i class="fa fa-link"></i> <span>' . $item['name'] . '</span></a></li>';
                    }
                }
                ?>
                <li class="bg-danger">
                    <a href="?action=logout"><i class="fa fa-sign-out"></i> <span>Sair</span></a>
                </li>
            </ul>
        </section>
    </aside>

    <div class="content-wrapper">
        <section class="content-header">
            <h1><?= ($title ?? ''); ?>
                <small>Painel administrativo</small>
                <div class="float-end">
                    <span
                        class="badge bg-<?= ((($status[1]['online'] ?? false)) ? 'success' : 'danger'); ?>"><?= $config['lua']['serverName'] ?></span>
                </div>
            </h1>
        </section>
        <section class="content">
            <?= $content; ?>
        </section>

    </div>

    <footer class="main-footer">

        <div class="hidden-xs float-end">
            <div id="status">
                <?php if (($status[1]['online'] ?? false)): ?>
                    <p class="badge bg-success">Servidor online</p>
                <?php else: ?>
                    <p class="badge bg-danger">Servidor offline</p>
                <?php endif; ?>
            </div>
        </div>
        <?= base64_decode('UG93ZXJlZCBieSA8YSBocmVmPSJodHRwczovL2dpdGh1Yi5jb20vanByemltYmEvY3J5c3RhbHNlcnZlci1teWFjYyIgdGFyZ2V0PSJfYmxhbmsiPkNyeXN0YWwgU2VydmVyPC9hPiBhbmQgQ29udHJpYnV0b3JzLg==') ?>
    </footer>

    <aside class="control-sidebar control-sidebar-dark">
        <div class="tab-content">
            <div class="tab-pane active" id="control-sidebar-home-tab">
                <h3 class="control-sidebar-heading">Conta</h3>
                <ul class="control-sidebar-menu">
                    <li>
                        <a href="?action=logout">
                            <i class="menu-icon fa fa-sign-out bg-red"></i>
                            <div class="menu-info">
                                <h4 class="control-sidebar-subheading">Sair</h4>
                                <p>Encerrar a sessão de <?= (USE_ACCOUNT_NAME ? $account_logged->getName() : $account_logged->getId()); ?></p>
                            </div>
                        </a>
                    </li>
                </ul>

                <h3 class="control-sidebar-heading">Site</h3>
                <ul class="control-sidebar-menu">
                    <li>
                        <a href="<?= BASE_URL; ?>" target="_blank">
                            <i class="menu-icon fa fa-eye bg-blue"></i>
                            <div class="menu-info">
                                <h4 class="control-sidebar-subheading">Abrir site</h4>
                                <p>Visualizar em uma nova aba</p>
                            </div>
                        </a>
                    </li>
                </ul>

                <h3 class="control-sidebar-heading">Versão</h3>
                <ul class="control-sidebar-menu">
                    <li>
                        <a href="?p=version">
                            <i class="menu-icon fa fa-info bg-warning"></i>
                            <div class="menu-info">
                                <h4 class="control-sidebar-subheading">Verificar versão</h4>
                                <p>MyAAC <?= MYAAC_VERSION ?></p>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </aside>
    <div class="control-sidebar-bg"></div>
</div>

<?php }
if (!$logged && !admin()) {
    echo $content;
}
?>

<script src="<?= BASE_URL; ?>admin/bootstrap/js/bootstrap.min.js"></script> <!-- BOOTSTRAP 5 -->
<!-- <script src="<?= BASE_URL; ?>tools/js/bootstrap.min.js"></script> -->
<script src="<?= BASE_URL; ?>tools/js/jquery-ui.min.js"></script>
<script src="<?= BASE_URL; ?>tools/js/jquery.dataTables.min.js"></script>
<script src="<?= BASE_URL; ?>tools/js/adminlte.min.js"></script>
</body>
</html>
