<?php
/** Query-only catalogue. All user values use bound parameters; sort columns are allowlisted. */
final class RenfallBazaarCatalog {
    public const SKILLS = ['maglevel'=>'Magic level','skill_fist'=>'Fist','skill_club'=>'Club','skill_sword'=>'Sword','skill_axe'=>'Axe','skill_dist'=>'Distance','skill_shielding'=>'Shielding','skill_fishing'=>'Fishing'];
    public static function query(array $input, string $view, int $account): array {
        $where=[];$args=[];
        if($view==='history') $where[]='a.status<>0';
        elseif($view==='mine') { $where[]='(a.account_old=? OR EXISTS (SELECT 1 FROM myaac_charbazaar_bid mb WHERE mb.auction_id=a.id AND mb.account_id=?))';$args[]=$account;$args[]=$account; }
        else $where[]='a.status=0 AND a.date_end>NOW()';
        foreach(['level'=>'p.level','price'=>'GREATEST(a.price,a.bid_price)'] as $key=>$column) {
            foreach(['min'=>'>=','max'=>'<='] as $suffix=>$op) {
                $v=$input[$key.'_'.$suffix]??'';
                if(is_scalar($v) && ctype_digit((string)$v) && (int)$v<=100000000) {$where[]="$column $op ?";$args[]=(int)$v;}
            }
        }
        $voc=is_scalar($input['vocation']??null)?(string)$input['vocation']:'';
        $vocations=['1'=>[1,5],'2'=>[2,6],'3'=>[3,7],'4'=>[4,8],'9'=>[9,10]];
        if(isset($vocations[$voc])){$where[]='p.vocation IN (?,?)';array_push($args,...$vocations[$voc]);}
        $name=is_string($input['name']??null)?trim(mb_substr($input['name'],0,80)):'';
        if($name!==''){$where[]="p.name LIKE ? ESCAPE '!'";$args[]='%'.str_replace(['!','%','_'],['!!','!%','!_'],$name).'%';}
        foreach(self::SKILLS+['boss_points'=>'Boss points','forge_dusts'=>'Dust','balance'=>'Gold no banco'] as $key=>$label) {
            $v=$input[$key]??'';if(is_scalar($v)&&ctype_digit((string)$v)&&(int)$v>0){$where[]="p.$key>=?";$args[]=min((int)$v,100000000);}
        }
        if(isset($input['watch']) && is_string($input['watch'])) {
            $ids=array_slice(array_values(array_filter(explode(',',$input['watch']),'ctype_digit')),0,200);
            $where[]=$ids?'a.id IN ('.implode(',',array_fill(0,count($ids),'?')).')':'1=0';array_push($args,...array_map('intval',$ids));
        }
        if(isset($input['item_ids']) && is_array($input['item_ids'])) {
            $ids=array_slice(array_map('intval',$input['item_ids']),0,1000);
            $where[]=$ids?'EXISTS (SELECT 1 FROM player_items pi WHERE pi.player_id=p.id AND pi.itemtype IN ('.implode(',',array_fill(0,count($ids),'?')).'))':'1=0';array_push($args,...$ids);
        }
        $sorts=['end'=>'a.date_end','start'=>'a.date_start','level'=>'p.level','price'=>'GREATEST(a.price,a.bid_price)','name'=>'p.name'];
        $sort=is_string($input['sort']??null)?$input['sort']:'end';$sort=$sorts[$sort]??$sorts['end'];$direction=($input['direction']??'asc')==='desc'?'DESC':'ASC';
        return [implode(' AND ',$where),$args,"$sort $direction, a.id $direction"];
    }
}
