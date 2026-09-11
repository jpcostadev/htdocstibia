document.addEventListener('DOMContentLoaded', () => {
 let watched=[]; try {const saved=JSON.parse(localStorage.getItem('renfall.auctions')||'[]'); if(Array.isArray(saved)) watched=saved.map(String).slice(0,200);}catch(_){}
 const buttons=document.querySelectorAll('[data-watch-auction]');
 function paint(){buttons.forEach(b=>{const active=watched.includes(b.dataset.watchAuction);b.setAttribute('aria-pressed',String(active));b.textContent=active?'★ Salvo':'☆ Salvar';}); const link=document.querySelector('[data-watch-link]');if(link){const url=new URL(link.href);url.searchParams.set('watch',watched.join(',')||'0');link.href=url.href;}}
 buttons.forEach(b=>b.addEventListener('click',()=>{const id=b.dataset.watchAuction;watched=watched.includes(id)?watched.filter(v=>v!==id):[...watched,id].slice(-200);try{localStorage.setItem('renfall.auctions',JSON.stringify(watched));}catch(_){}paint();}));paint();
 function tick(){document.querySelectorAll('[data-auction-end]').forEach(el=>{const remaining=Math.max(0,Math.floor((Number(el.dataset.auctionEnd)*1000-Date.now())/1000));const d=Math.floor(remaining/86400),h=Math.floor(remaining%86400/3600),m=Math.floor(remaining%3600/60);el.textContent=remaining?`${d?d+'d ':''}${h}h ${m}m`:'Encerrado';});}tick();if(document.querySelector('[data-auction-end]'))setInterval(tick,30000);
});
