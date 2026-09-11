<?php
defined('MYAAC') or die('Direct access not allowed!');
$q=$db->prepare('SELECT * FROM players WHERE id=?');$q->execute([(int)$a['player_id']]);$player=$q->fetch(PDO::FETCH_ASSOC);
$q=$db->prepare('SELECT charm_points,max_charm_points FROM player_charms WHERE player_id=?');$q->execute([(int)$a['player_id']]);$charms=$q->fetch(PDO::FETCH_ASSOC)?:[];
?>
<p><a href="<?= $escape($url(['view'=>'current'])) ?>">← Voltar aos leilões</a></p>
<?php if((int)$a['status']!==0): ?><p class="rf-note">Leilão encerrado. Os dados abaixo refletem o estado atual do personagem; não constituem uma fotografia da data da venda.</p><?php endif ?>
<div class="rf-detail-grid">
<section><h3>Informações do personagem</h3><table><?php foreach(['level'=>'Nível','experience'=>'Experiência','healthmax'=>'Vida máxima','manamax'=>'Mana máxima','cap'=>'Capacidade','soul'=>'Soul','balance'=>'Gold no banco','boss_points'=>'Boss points','forge_dusts'=>'Exalted dust','forge_dust_level'=>'Limite de dust'] as $key=>$label): ?><tr><td><?= $label ?></td><td><b><?= number_format((int)($player[$key]??0),0,',','.') ?></b></td></tr><?php endforeach ?><tr><td>Charm points disponíveis</td><td><?= (int)($charms['charm_points']??0) ?></td></tr><tr><td>Charm points totais</td><td><?= (int)($charms['max_charm_points']??0) ?></td></tr></table></section>
<section><h3>Habilidades</h3><table><?php foreach(RenfallBazaarCatalog::SKILLS as $key=>$label): ?><tr><td><?= $label ?></td><td><b><?= (int)$player[$key] ?></b></td></tr><?php endforeach ?></table><p class="rf-note">Valores base do personagem, sem bônus temporários ou de loyalty da conta.</p></section>
<section><h3>Equipamentos e inventário</h3><div class="rf-items">
<?php $q=$db->prepare('SELECT itemtype,SUM(count) AS quantity FROM player_items WHERE player_id=? GROUP BY itemtype ORDER BY MIN(pid),itemtype');$q->execute([(int)$a['player_id']]);$items=$q->fetchAll(PDO::FETCH_ASSOC);foreach($items as $item): $itemName=getItemNameById((int)$item['itemtype'])?:'Item #'.(int)$item['itemtype']; ?>
<figure title="<?= $escape($itemName) ?>"><?= getItemImage((int)$item['itemtype']) ?><figcaption><?= $escape($itemName) ?><br>× <?= (int)$item['quantity'] ?></figcaption></figure>
<?php endforeach;if(!$items)echo '<p>Inventário vazio.</p>'; ?></div></section>
<section><h3>Quests e acessos</h3><table>
<?php $quests=$config['quests']??[];if($quests){$q=$db->prepare('SELECT `key`,`value` FROM player_storage WHERE player_id=?');$q->execute([(int)$a['player_id']]);$storage=$q->fetchAll(PDO::FETCH_KEY_PAIR);foreach($quests as $label=>$key): ?><tr><td><?= $escape($label) ?></td><td><?= (int)($storage[(int)$key]??0)>0?'Concluída':'Não concluída' ?></td></tr><?php endforeach;}else echo '<tr><td>Nenhuma quest configurada para exibição.</td></tr>'; ?></table></section>
<section><h3>Histórico de lances</h3><table><tr><th>Lance</th><th>Data</th></tr><?php $q=$db->prepare('SELECT bid,`date` FROM myaac_charbazaar_bid WHERE auction_id=? ORDER BY bid DESC LIMIT 50');$q->execute([(int)$a['id']]);$bids=$q->fetchAll(PDO::FETCH_ASSOC);foreach($bids as $bid): ?><tr><td><?= number_format((int)$bid['bid'],0,',','.') ?> coins</td><td><?= $escape($bid['date']) ?></td></tr><?php endforeach;if(!$bids)echo '<tr><td colspan="2">Nenhum lance recebido.</td></tr>'; ?></table></section>
</div>
