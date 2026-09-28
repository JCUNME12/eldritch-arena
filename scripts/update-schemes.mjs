// Run explicitly with node scripts/update-schemes.mjs. No API key is required.
import {mkdir, writeFile, rename} from 'node:fs/promises';
const headers={'User-Agent':'EldritchArena/1.0 (scheme catalog updater)',Accept:'application/json'};
async function read(url) {
    await new Promise(resolve=>setTimeout(resolve,150));
    const response=await fetch(url,{headers,signal:AbortSignal.timeout(30000)});
    if(!response.ok) throw new Error(`HTTP ${response.status}: ${url}`);
    return response.json();
}
const search='https://api.scryfall.com/cards/search?q=t%3Ascheme&unique=prints';
let next=search; const prints=[];
while(next) {
    const page=await read(next); prints.push(...page.data);
    next=page.has_more ? page.next_page : null;
    if(next && new URL(next).hostname!=='api.scryfall.com') throw new Error('Unexpected pagination host');
}
const cards=new Map(), names=new Map(), sets=new Map();
for(const p of prints) {
    if(!p.oracle_id || !p.image_uris?.large || !p.type_line.includes('Scheme')) throw new Error('Incomplete scheme');
    const id=p.oracle_id; names.set(p.name,id);
    if(!cards.has(id)) cards.set(id,{id,name:p.name,text:p.oracle_text||'',ongoing:p.type_line.includes('Ongoing'),image:p.image_uris.large,url:p.scryfall_uri,artist:p.artist||''});
    if(!sets.has(p.set)) sets.set(p.set,{id:`set-${p.set}`,name:p.set_name,kind:'Coleções',cards:[]});
    if(!sets.get(p.set).cards.includes(id)) sets.get(p.set).cards.push(id);
}
const decks=[{id:'all',name:'Todos os esquemas',kind:'Catálogo completo',cards:[...cards.keys()]},...[...sets.values()].filter(s=>s.cards.length>=10)];
const deckFiles=['AssembleTheDoomsdayMachine_ARC','BringAboutTheUndeadApocalypse_ARC','ScorchTheWorldWithDragonfire_ARC','TrampleCivilizationUnderfoot_ARC','DeathToll_DSC','EndlessPunishment_DSC','JumpScare_DSC','MiracleWorker_DSC','ArchenemyNicolBolas_E01'];
for(const id of deckFiles) {
    const {data}=await read(`https://mtgjson.com/api/v5/decks/${id}.json`);
    const ids=[...new Set(data.schemes.map(card=>names.get(card.name)))];
    if(ids.includes(undefined)||ids.length<10) throw new Error(`Incomplete deck ${id}`);
    decks.push({id,name:data.name,kind:'Baralhos temáticos',cards:ids,source:data.source});
}
const output=new URL('../public/data/archenemy-schemes.json',import.meta.url);
await mkdir(new URL('../public/data/',import.meta.url),{recursive:true});
await writeFile(new URL(output.href+'.tmp'),JSON.stringify({version:1,updated:new Date().toISOString().slice(0,10),sources:[search,'https://mtgjson.com/api/v5/DeckList.json'],cards:[...cards.values()],decks},null,2)+'\n');
await rename(new URL(output.href+'.tmp'),output);
console.log(`Saved ${cards.size} schemes and ${decks.length} deck choices. Review the diff and run npm run test:counter.`);
