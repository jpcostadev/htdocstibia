<?php
/** Renfall Bazaar: keep Crystal escrow, payments and routes; enrich the catalogue. */
defined('MYAAC') or die('Direct access not allowed!');
require_once SYSTEM.'libs/crystal_bazaar.php';
require_once SYSTEM.'libs/renfall_bazaar_catalog.php';
$title='Bazaar de Personagens';
$escrow=$db->query("SELECT id FROM accounts WHERE name='crystal_bazaar_escrow'")->fetchColumn();
if(!$escrow){error('Execute tools/instalar-site.php para configurar o bazaar.');return;}
$bazaar=new CrystalBazaar($db,(int)$escrow,(int)$config['bazaar_create'],(int)$config['bazaar_tax']);
$_SESSION['bazaar_csrf'] ??= bin2hex(random_bytes(24));
$accountId=$logged?(int)$account_logged->getId():0;
$escape=static fn($v)=>htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');
$get=static fn($k,$d='')=>is_scalar($_GET[$k]??null)?(string)$_GET[$k]:$d;
$url=static fn(array $params)=>BASE_URL.'?'.http_build_query(array_merge($params,['subtopic'=>'currentcharactertrades']));
$view=$get('view',PAGE==='pastcharactertrades'?'history':(PAGE==='owncharactertrades'?'mine':'current'));
if(!in_array($view,['current','history','mine','watch'],true))$view='current';
try {
    $bazaar->settle();
    if($_SERVER['REQUEST_METHOD']==='POST') {
        if(!$logged || !is_string($_POST['csrf']??null) || !hash_equals($_SESSION['bazaar_csrf'],$_POST['csrf'])) throw new RuntimeException('Entre na conta e atualize o formulário.');
        $number=static fn($key)=>filter_var($_POST[$key]??null,FILTER_VALIDATE_INT)?:0;
        if(($_POST['operation']??'')==='create') {
            $id=$bazaar->create($accountId,$number('player'),$number('price'),$number('days'));
            $_SESSION['renfall_bazaar_notice']='Anúncio '.$id.' criado. O personagem fica em custódia até o encerramento.';
        } elseif(($_POST['operation']??'')==='bid') {
            $bazaar->bid($accountId,$number('auction'),$number('amount'));
            $_SESSION['renfall_bazaar_notice']='Lance registrado. Coins reservados com sucesso.';
        } else throw new RuntimeException('Operação inválida.');
        // Rotate the token: a browser refresh must never repeat a charge.
        $_SESSION['bazaar_csrf']=bin2hex(random_bytes(24));
        header('Location: '.$url(['view'=>$view]),true,303);exit;
    }
} catch(Throwable $e){error($escape($e instanceof PDOException?'Operação não concluída. Tente novamente.':$e->getMessage()));}
if(isset($_SESSION['renfall_bazaar_notice'])){success($escape($_SESSION['renfall_bazaar_notice']));unset($_SESSION['renfall_bazaar_notice']);}
$detail=filter_var($get('details','0'),FILTER_VALIDATE_INT)?:0;
?>
<div class="rf-bazaar">
<nav class="rf-tabs" aria-label="Navegação do bazaar">
<?php foreach(['current'=>'Leilões atuais','history'=>'Histórico','mine'=>'Meus leilões','watch'=>'Favoritos'] as $key=>$label): ?>
<a href="<?= $escape($url(['view'=>$key])) ?>" <?= $view===$key?'aria-current="page"':'' ?> <?= $key==='watch'?'data-watch-link':'' ?>><?= $label ?></a>
<?php endforeach ?></nav>
<p>Encontre seu próximo personagem. Compare habilidades, explore os equipamentos e acompanhe seus leilões.</p>
<div class="rf-note">Anúncio: <b><?= (int)$config['bazaar_create'] ?> coins</b> · Taxa sobre a venda: <b><?= (int)$config['bazaar_tax'] ?>%</b>.<br>Somente coins transferíveis. O lance é reservado e devolvido se você for superado. Sem comprador, o personagem retorna ao vendedor. A taxa de anúncio não é reembolsada.</div>
<?php if($logged):
$q=$db->prepare('SELECT coins_transferable FROM accounts WHERE id=?');$q->execute([$accountId]); ?>
<p><b>Seu saldo disponível:</b> <?= number_format((int)$q->fetchColumn(),0,',','.') ?> coins transferíveis.</p>
<details class="rf-create" <?= PAGE==='createcharacterauction'?'open':'' ?>><summary>+ Anunciar meu personagem</summary>
<p>Desconecte o personagem, saia da guild e resolva casas e lances antes de anunciar. Ele ficará indisponível para jogar durante o leilão.</p>
<form method="post" action="<?= $escape($url(['view'=>'mine'])) ?>">
<input type="hidden" name="csrf" value="<?= $escape($_SESSION['bazaar_csrf']) ?>"><input type="hidden" name="operation" value="create">
<div class="rf-form-grid"><label>Personagem<select name="player" required><option value="">Selecione</option>
<?php $q=$db->prepare('SELECT id,name FROM players WHERE account_id=? AND group_id=1 AND deletion=0 ORDER BY name');$q->execute([$accountId]);foreach($q as $p): ?>
<option value="<?= (int)$p['id'] ?>"><?= $escape($p['name']) ?></option><?php endforeach ?></select></label>
<label>Preço mínimo (coins)<input name="price" type="number" min="1" max="100000000" required></label><label>Duração (dias)<input name="days" type="number" min="1" max="30" value="1" required></label></div>
<div class="rf-actions"><button type="submit">Criar anúncio · <?= (int)$config['bazaar_create'] ?> coins</button></div></form></details>
<?php else: ?><p><a href="<?= getLink('account/manage') ?>">Entre na sua conta</a> para anunciar ou dar lances.</p><?php endif ?>
<?php if($view==='watch'): ?><p class="rf-note">Os favoritos ficam salvos neste navegador.</p><?php endif ?>
<?php if($view==='mine'&&!$logged): ?><div class="rf-empty">Entre na sua conta para consultar seus anúncios e lances.</div></div><?php return;endif ?>
<?php if(!$detail): ?>
<form class="rf-filters" method="get" action="<?= $escape(BASE_URL) ?>">
<input type="hidden" name="subtopic" value="currentcharactertrades"><input type="hidden" name="view" value="<?= $escape($view) ?>">
<?php if($view==='watch'): ?><input type="hidden" name="watch" value="<?= $escape($get('watch','0')) ?>"><?php endif ?>
<div class="rf-form-grid">
<label>Nome do personagem<input name="name" maxlength="80" value="<?= $escape($get('name')) ?>" placeholder="Quem você procura?"></label>
<label>Vocação<select name="vocation"><option value="">Todas as vocações</option><?php foreach([1=>'Sorcerer',2=>'Druid',3=>'Paladin',4=>'Knight',9=>'Monk'] as $id=>$label): ?><option value="<?= $id ?>" <?= $get('vocation')===(string)$id?'selected':'' ?>><?= $label ?></option><?php endforeach ?></select></label>
<label>Mundo<input value="<?= $escape($config['lua']['serverName']??'Renfall') ?>" readonly aria-label="Mundo do servidor"></label>
<?php foreach(['level_min'=>'Nível mínimo','level_max'=>'Nível máximo','price_min'=>'Lance mínimo','price_max'=>'Lance máximo'] as $key=>$label): ?><label><?= $label ?><input type="number" min="0" max="100000000" name="<?= $key ?>" value="<?= $escape($get($key)) ?>"></label><?php endforeach ?>
<label>Ordenar por<select name="sort"><?php foreach(['end'=>'Término','start'=>'Início','level'=>'Nível','price'=>'Lance','name'=>'Nome'] as $key=>$label): ?><option value="<?= $key ?>" <?= $get('sort','end')===$key?'selected':'' ?>><?= $label ?></option><?php endforeach ?></select></label>
<label>Ordem<select name="direction"><option value="asc">Menor / mais antigo</option><option value="desc" <?= $get('direction')==='desc'?'selected':'' ?>>Maior / mais recente</option></select></label></div>
<details <?= array_intersect(array_keys($_GET),array_keys(RenfallBazaarCatalog::SKILLS))?'open':'' ?>><summary>Habilidades · filtrar pelo mínimo</summary><div class="rf-form-grid"><?php foreach(RenfallBazaarCatalog::SKILLS as $key=>$label): ?><label><?= $label ?><input type="number" name="<?= $key ?>" min="0" max="100000000" value="<?= $escape($get($key)) ?>"></label><?php endforeach ?></div></details>
<details <?= $get('item')||$get('boss_points')||$get('forge_dusts')||$get('balance')?'open':'' ?>><summary>Extras · itens, boss points e forja</summary><div class="rf-form-grid"><label>Nome ou ID de item no inventário<input name="item" maxlength="80" value="<?= $escape($get('item')) ?>"></label><?php foreach(['boss_points'=>'Boss points mínimos','forge_dusts'=>'Dust mínimo','balance'=>'Gold mínimo no banco'] as $key=>$label): ?><label><?= $label ?><input type="number" name="<?= $key ?>" min="0" max="100000000" value="<?= $escape($get($key)) ?>"></label><?php endforeach ?></div></details>
<div class="rf-actions"><button>Aplicar filtros</button><a href="<?= $escape($url(['view'=>$view])) ?>">Limpar filtros</a></div></form>
<?php endif;
$input=$_GET;unset($input['item_ids']);
if($view==='watch'&&!isset($input['watch']))$input['watch']='0';
$item=trim(mb_substr($get('item'),0,80));
if($item!=='') {
    require_once SYSTEM.'libs/items.php';Items::get(1);if(!Items::$items)Items::loadFromXML();$input['item_ids']=[];
    if(ctype_digit($item))$input['item_ids']=[(int)$item];
    else foreach(Items::$items??[] as $id=>$data)if(stripos($data['name']??'',$item)!==false)$input['item_ids'][]=(int)$id;
}
[$where,$args,$order]=RenfallBazaarCatalog::query($input,$view,$accountId);
if($detail){$where='a.id=?';$args=[$detail];}
$join=' FROM myaac_charbazaar a JOIN players p ON p.id=a.player_id WHERE '.$where;
$q=$db->prepare('SELECT COUNT(*)'.$join);$q->execute($args);$total=(int)$q->fetchColumn();
$pages=max(1,(int)ceil($total/20));$page=max(1,min($pages,(int)$get('page','1')));$offset=($page-1)*20;
$q=$db->prepare('SELECT a.*,p.name,p.level,p.vocation,p.looktype,p.lookaddons,p.lookhead,p.lookbody,p.looklegs,p.lookfeet,p.sex,p.maglevel,p.skill_fist,p.skill_club,p.skill_sword,p.skill_axe,p.skill_dist,p.skill_shielding,p.skill_fishing'.$join.' ORDER BY '.$order.' LIMIT 20 OFFSET '.$offset);$q->execute($args);$auctions=$q->fetchAll(PDO::FETCH_ASSOC);
if(!$detail)echo '<p><b>'.$total.'</b> personagem(ns) · página '.$page.' de '.$pages.'</p>';
if(!$auctions)echo '<div class="rf-empty"><h3>Nenhum leilão encontrado</h3><p>Experimente outros filtros ou volte para acompanhar novos anúncios.</p></div>';
foreach($auctions as $a):
$active=(int)$a['status']===0 && strtotime($a['date_end'])>time();$minimum=max((int)$a['price'],(int)$a['bid_price']+1);
$outfit=($config['outfit_images_url']??'').'?'.http_build_query(['id'=>$a['looktype'],'addons'=>$a['lookaddons'],'head'=>$a['lookhead'],'body'=>$a['lookbody'],'legs'=>$a['looklegs'],'feet'=>$a['lookfeet']]);
?>
<article class="rf-auction"><header><div><a href="<?= $escape($url(['details'=>(int)$a['id']])) ?>"><?= $escape($a['name']) ?></a><small>Nível <?= (int)$a['level'] ?> · <?= $escape($config['vocations'][(int)$a['vocation']]??'None') ?> · <?= (int)$a['sex']===0?'Feminino':'Masculino' ?> · <?= $escape($config['lua']['serverName']??'Renfall') ?></small></div><button type="button" class="rf-watch" data-watch-auction="<?= (int)$a['id'] ?>" aria-pressed="false" aria-label="Salvar leilão de <?= $escape($a['name']) ?>">☆ Salvar</button></header>
<div class="rf-auction-body"><img class="rf-outfit" src="<?= $escape($outfit) ?>" alt="Outfit de <?= $escape($a['name']) ?>" width="80" height="80" loading="lazy"><div><dl><dt>Início</dt><dd><?= date('d/m/Y H:i',strtotime($a['date_start'])) ?></dd><dt>Fim</dt><dd><?= date('d/m/Y H:i',strtotime($a['date_end'])) ?></dd><dt>Estado</dt><dd class="rf-status"><?= [0=>'Em andamento',1=>'Vendido',2=>'Sem comprador'][(int)$a['status']]??'Encerrado' ?></dd><?php if($active): ?><dt>Tempo restante</dt><dd data-auction-end="<?= strtotime($a['date_end']) ?>"><?= $escape($a['date_end']) ?></dd><?php endif ?></dl><div class="rf-skills"><?php foreach(['maglevel'=>'Magic','skill_sword'=>'Sword','skill_axe'=>'Axe','skill_dist'=>'Distance','skill_fist'=>'Fist'] as $key=>$label): ?><span><?= $label ?> <b><?= (int)$a[$key] ?></b></span><?php endforeach ?></div></div>
<div><small><?= (int)$a['bid_price']?'Lance atual':'Lance mínimo' ?></small><div class="rf-price"><?= number_format(max((int)$a['price'],(int)$a['bid_price']),0,',','.') ?> <small>coins transferíveis</small></div>
<?php if($logged&&$active&&(int)$a['account_old']!==$accountId): ?>
<form method="post" action="<?= $escape($url(['view'=>$view])) ?>" class="rf-bid"><input type="hidden" name="csrf" value="<?= $escape($_SESSION['bazaar_csrf']) ?>"><input type="hidden" name="operation" value="bid"><input type="hidden" name="auction" value="<?= (int)$a['id'] ?>"><label>Seu lance<input type="number" name="amount" min="<?= $minimum ?>" max="100000000" required aria-label="Lance em <?= $escape($a['name']) ?>"></label><button>Dar lance</button></form>
<?php if((int)$a['bid_account']===$accountId): ?><small>Você está vencendo este leilão.</small><?php endif ?>
<?php elseif($active&&!$logged): ?><a href="<?= getLink('account/manage') ?>">Entrar para dar lance →</a><?php elseif($active): ?><small>Este é seu anúncio.</small><?php endif ?>
</div></div></article>
<?php if($detail)require SYSTEM.'pages/renfall_bazaar_details.php';endforeach;
if(!$detail&&$pages>1): ?><nav class="rf-pagination" aria-label="Páginas do bazaar"><?php foreach(array_unique([1,max(1,$page-1),$page,min($pages,$page+1),$pages]) as $n): ?><a <?= $page===$n?'aria-current="page"':'' ?> href="<?= $escape($url(array_merge(array_filter($_GET,'is_scalar'),['page'=>$n]))) ?>"><?= $n ?></a><?php endforeach ?></nav><?php endif ?>
</div>
