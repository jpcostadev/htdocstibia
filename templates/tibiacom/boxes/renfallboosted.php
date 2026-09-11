<?php defined('MYAAC') or die('Direct access not allowed!'); ?>
<section class="rf-panel"><h2>BOOSTED DO DIA</h2><div class="rf-panel-body rf-boosted-grid">
<?php foreach([['BOSS',$bossname,$bosstype,$bossaddons,$bosshead,$bossbody,$bosslegs,$bossfeet,$bossmount,$bosstypeEx],['CRIATURA',$creaturename,$creaturetype,$creatureaddons,$creaturehead,$creaturebody,$creaturelegs,$creaturefeet,$creaturemount,0]] as $entry):
[$label,$name,$look,$addons,$head,$body,$legs,$feet,$mount,$item]=$entry;
$src=$item?($config['item_images_url'].$item.'.gif'):($config['outfit_images_url'].'?'.http_build_query(['id'=>$look,'addons'=>$addons,'head'=>$head,'body'=>$body,'legs'=>$legs,'feet'=>$feet,'mount'=>$mount])); ?>
<div class="rf-boosted-entry"><div class="rf-portrait"><img src="<?= htmlspecialchars($src,ENT_QUOTES,'UTF-8') ?>" alt="<?= htmlspecialchars($name,ENT_QUOTES,'UTF-8') ?>" width="64" height="64"></div><strong><?= $label ?></strong><span><?= htmlspecialchars(ucwords(strtolower(trim($name))),ENT_QUOTES,'UTF-8') ?></span></div>
<?php endforeach ?></div></section>
