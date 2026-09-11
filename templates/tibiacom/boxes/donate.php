<style>
    .renfall-shop { width: 180px; margin-bottom: 10px; font-family: Verdana, Arial, sans-serif; }
    .renfall-shop__header { height: 44px; line-height: 64px; color: #ffe7ad; font-size: 13px; font-weight: bold; text-shadow: 1px 1px 2px #000; background: url('templates/tibiacom/images/themeboxes/box_top.png') center top no-repeat; }
    .renfall-shop__content { box-sizing: border-box; width: 180px; min-height: 157px; padding: 8px 10px 13px; color: #f4dfbd; background: url('templates/tibiacom/images/themeboxes/box_bg.png') center top repeat-y; }
    .renfall-shop__image { display: block; width: 150px; height: 74px; margin: 0 auto 5px; object-fit: cover; object-position: center; border: 1px solid #94713b; box-shadow: 0 0 8px rgba(255, 145, 0, .45); }
    .renfall-shop__text { margin: 3px 0 8px; font-size: 10px; line-height: 14px; text-shadow: 1px 1px #000; }
    .renfall-shop__button { display: block; box-sizing: border-box; width: 154px; margin: 0 auto; padding: 9px 4px; border: 1px solid #ffca54; border-radius: 3px; color: #fff; font-size: 11px; font-weight: bold; text-decoration: none; text-shadow: 1px 1px 1px #733200; background: linear-gradient(#ffad19, #d76400); box-shadow: inset 0 1px #ffe69d, 0 2px 4px #160b02; transition: filter .15s, transform .15s; }
    .renfall-shop__button:hover { color: #fff; filter: brightness(1.16); transform: translateY(-1px); }
    .renfall-shop__bottom { height: 30px; margin-top: -20px; background: url('templates/tibiacom/images/themeboxes/box_bottom.png') center bottom no-repeat; pointer-events: none; }
</style>
<div class="renfall-shop">
    <div class="renfall-shop__header">RENFALL WEBSHOP</div>
    <div class="renfall-shop__content">
        <img class="renfall-shop__image" src="templates/tibiacom/images/themeboxes/donate/donate.png" alt="Renfall Coins">
        <div class="renfall-shop__text">Coins com pagamento via Pix ou cartão.</div>
        <a class="renfall-shop__button" href="<?php echo BASE_URL ?>?donate">COMPRAR COINS</a>
    </div>
    <div class="renfall-shop__bottom"></div>
</div>
