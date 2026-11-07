import { defaultConfig, dimensions, priceText, roomOf } from './data.js';
import { createRoom } from './scene.js';
import { attachControls } from './controls.js';
import { createPianoAmbience } from './piano.js';

const copy = Object.freeze({
 my:{heroTitle:'ကြေးဝါအနုပညာ၏ အလှ',heroCopy:'မြန်မာ့လက်ရာတွေကို နီးနီးကပ်ကပ် ခံစားကြည့်ရှုပါ။',loading:'ပြခန်းကို ပြင်ဆင်နေသည်…',enter:'ပြခန်းထဲ ဝင်ကြည့်မယ်',hint:'ဖိဆွဲ၍ လှည့်ကြည့် · ခလုတ်ဖြင့် လမ်းလျှောက်',browse:'ပန်းချီများကို တိုက်ရိုက်ကြည့်ရန်',inside:'ပြခန်းအတွင်း',entrance:'ဝင်ပေါက်သို့',artworks:'ပန်းချီများ',musicOn:'ပီယာနိုဖွင့်',musicOff:'ပီယာနိုပိတ်',walk:'လမ်းလျှောက်ရန်',look:'ဖိဆွဲ၍ လှည့်ကြည့်ပါ',selectHint:'ပန်းချီကို နှိပ်၍ အနီးကပ်ကြည့်ပါ',collection:'ကြေးဝါလက်ရာများ',fallback:'ဒီစက်မှာ 3D ပြခန်းမဖွင့်နိုင်သဖြင့် ပန်းချီများကို ဒီနေရာမှာ ကြည့်နိုင်ပါတယ်။',works:'လက်ရာ ၆ ပုံ',medium:'မြန်မာ့ကြေးဝါပန်းချီ',frameSize:'ဘောင်အပါအဝင် အရွယ်အစား',price:'ဈေးနှုန်း',continue:'ဆက်လက်ကြည့်ရှုမယ်',helpTitle:'ပြခန်းကို လှည့်ပတ်ကြည့်ရှုရန်',helpCopy:'အေးအေးဆေးဆေး လှည့်ပတ်ပြီး လက်ရာအသေးစိတ်တွေကို ကြည့်ရှုပါ။',helpMove:'WASD / မြားခလုတ်များ သို့မဟုတ် စက်ဝိုင်းခလုတ်ကို ဖိဆွဲပြီး လမ်းလျှောက်ပါ။',helpLook:'မျက်နှာပြင်ကို ဖိဆွဲ၍ လှည့်ကြည့်ပါ။ Keyboard မှ Q / E ကိုလည်း သုံးနိုင်ပါတယ်။',helpArt:'ပန်းချီကို နှိပ်ပြီး အနီးကပ်ပုံ၊ အရွယ်အစားနဲ့ ဈေးနှုန်းကို ကြည့်ပါ။',gotIt:'နားလည်ပါပြီ',framed:'ဘောင်အပါအဝင်',priceValue:'၂၅၀,၀၀၀ ကျပ်',silverHeroTitle:'ငွေထည်အနုပညာ၏ အလှ',silverHeroCopy:'မြန်မာ့ငွေပန်းချီလက်ရာတွေကို အရွယ်အစားအမှန်အတိုင်း လှည့်ပတ်ကြည့်ရှုပါ။',silverCollection:'ငွေပန်းချီလက်ရာများ',silverMedium:'မြန်မာ့ငွေပန်းချီ',roomBrass:'ကြေးဝါပြခန်း',roomSilver:'ငွေပန်းချီပြခန်း',roomFoyer:'ဧည့်ခန်းမ',brassEntrance:'Brass Entrance · ကြေးဝါပြခန်း',silverEntrance:'Silver Entrance · ငွေပန်းချီပြခန်း',goBrass:'ကြေးဝါပြခန်းဘက် သွားရန် →',goSilver:'ငွေပန်းချီပြခန်းဘက် သွားရန် →',tabBrass:'ကြေးဝါ',tabSilver:'ငွေ',worksCount:n=>`လက်ရာ ${new Intl.NumberFormat('my-MM').format(n)} ပုံ`,sizes:n=>`အရွယ်အစား ${new Intl.NumberFormat('my-MM').format(n)} မျိုး`,oneSize:'အရွယ်အစား ၁ မျိုး',from:p=>`${p} မှစ၍`,backToDesigns:'← ပန်းချီအမျိုးအစားများသို့'},
 en:{heroTitle:'The beauty of brass.',heroCopy:'Step inside. Take your time. Discover Myanmar brass craftsmanship.',loading:'Preparing your visit…',enter:'Enter the gallery',hint:'Drag to look · Use controls to walk',browse:'Browse the artworks',inside:'INSIDE THE GALLERY',entrance:'Entrance',artworks:'Artworks',musicOn:'Piano on',musicOff:'Piano off',walk:'WALK',look:'Drag to look around',selectHint:'Select an artwork for a closer look',collection:'The brass collection',fallback:'The 3D room is unavailable on this device. Explore every artwork here instead.',works:'6 WORKS',medium:'Myanmar brass artwork',frameSize:'Frame dimensions',price:'Price',continue:'Continue exploring',helpTitle:'Make yourself at home.',helpCopy:'Take a walk and discover the details in each work.',helpMove:'Walk with WASD, arrow keys, or the on-screen joystick.',helpLook:'Drag across the room to look around, or use Q / E to turn.',helpArt:'Select a painting to see it up close, with its frame size and price.',gotIt:'Got it',framed:'framed',priceValue:'MMK 250,000',silverHeroTitle:'The beauty of silver.',silverHeroCopy:'Walk among Myanmar silver artworks, each shown at its true size.',silverCollection:'The silver collection',silverMedium:'Myanmar silver artwork',roomBrass:'BRASS ROOM',roomSilver:'SILVER ROOM',roomFoyer:'FOYER',brassEntrance:'Brass Entrance',silverEntrance:'Silver Entrance',goBrass:'Go to the Brass Entrance →',goSilver:'Go to the Silver Entrance →',tabBrass:'Brass',tabSilver:'Silver',worksCount:n=>`${n} WORKS`,sizes:n=>`${n} sizes`,oneSize:'1 size',from:p=>`From ${p}`,backToDesigns:'← All designs'}
});

async function loadConfig(root){
 const encoded=root.dataset.inlineConfig;
 if(encoded){try{const bytes=Uint8Array.from(atob(encoded),c=>c.charCodeAt(0));return JSON.parse(new TextDecoder().decode(bytes));}catch{throw new Error('Invalid inline showroom configuration');}}
 const url=root.dataset.configUrl;
 if(url){const parsed=new URL(url,window.location.href);if(parsed.origin!==window.location.origin)throw new Error('Cross-origin configuration blocked');const response=await fetch(parsed,{credentials:'same-origin',headers:{Accept:'application/json'}});if(!response.ok)throw new Error('Unable to load showroom configuration');return response.json();}
 return structuredClone(defaultConfig);
}
async function initialize(root){
 if(root.dataset.initialized==='true')return;root.dataset.initialized='true';
 const q=s=>root.querySelector(s);const qa=s=>[...root.querySelectorAll(s)];
 let assetsBase; try { const parsed=new URL(root.dataset.assetsBase||'',window.location.href); if(parsed.origin!==window.location.origin) throw new Error('cross-origin asset base'); assetsBase=parsed.href.replace(/\/$/,''); } catch { root.classList.add('bs-asset-error'); return; }
 let lang='my',entered=false,list=false,failed=false,selected=null,room=null,controls=null,disposed=false,previousFocus=null,config;
 // The silver hall comes first: without a parameter visitors start at the Silver Entrance.
 // Each entrance has its own link: ?entrance=silver or ?entrance=brass (a #silver hash also works,
 // so signed preview URLs can be opened at either entrance without breaking their signature).
 const readEntrance=()=>{const p=new URLSearchParams(window.location.search).get('entrance')||(window.location.pathname.match(/(silver|brass)-art-gallery/)||[])[1]||(window.location.hash.match(/silver|brass/)||[])[0];return p==='brass'?'brass':'silver';};
 let entrance=readEntrance(),catalogRoom=entrance,zone=entrance;
 try{config=await loadConfig(root);}catch{root.classList.add('bs-config-error');config=structuredClone(defaultConfig);}
 const artworks=config.artworks;const roomIndexes={brass:[],silver:[]};artworks.forEach((a,i)=>roomIndexes[roomOf(a)].push(i));if(!roomIndexes.silver.length&&entrance==='silver')entrance=catalogRoom=zone='brass';
 const roomHost=q('[data-bs-room]'),welcome=q('[data-bs-welcome]'),inside=q('[data-bs-inside]'),catalog=q('[data-bs-catalog]'),grid=q('[data-bs-grid]'),fallback=q('[data-bs-fallback]'),artDialog=q('[data-bs-art-dialog]'),helpDialog=q('[data-bs-help-dialog]');
 const cleanup=[];
 const on=(target,event,handler,options)=>{target.addEventListener(event,handler,options);cleanup.push(()=>target.removeEventListener(event,handler,options));};
 const el=(tag,className)=>{const n=document.createElement(tag);if(className)n.className=className;return n;};
 let soundOn=false;const soundButton=q('[data-bs-sound]')||(()=>{const button=el('button');button.type='button';button.dataset.bsSound='';button.setAttribute('aria-pressed','false');const icon=el('span'),label=el('span');button.append(icon,label);q('.bs-tools').append(button);return button;})();const piano=createPianoAmbience(state=>{soundOn=state;updateSoundButton();});
 function updateSoundButton(){if(!soundButton)return;const key=soundOn?'musicOff':'musicOn';soundButton.querySelector('span:last-child').textContent=copy[lang][key];soundButton.querySelector('span:first-child').textContent=soundOn?'♫':'♪';soundButton.setAttribute('aria-label',copy[lang][key]);soundButton.setAttribute('aria-pressed',String(soundOn));}
 // Cards use the small thumbnail and only load when scrolled near; the detail view uses the full image.
 const lazy='IntersectionObserver' in window?new IntersectionObserver(entries=>entries.forEach(entry=>{if(entry.isIntersecting){lazy.unobserve(entry.target);entry.target.bsLoad?.();}}),{rootMargin:'400px'}):null;cleanup.push(()=>lazy?.disconnect());
 function artImage(a,{thumb=false}={}){const wrap=el('div',`bs-art-image bs-art-${a.orientation}`);const canvas=el('canvas');const useThumb=thumb&&a.thumb&&a.crop[0]===0&&a.crop[1]===0;const [x,y,w,h]=a.crop;wrap.style.aspectRatio=`${w} / ${h}`;wrap.style.setProperty("--bs-ar",String(w/h));canvas.setAttribute('role','img');canvas.setAttribute('aria-label',`${a.my} — ${a.en}`);const image=new Image();image.decoding='async';const url=new URL((useThumb?a.thumb:a.image)||`${a.id}.png`,assetsBase+'/');image.addEventListener('load',()=>{if(useThumb){canvas.width=image.naturalWidth;canvas.height=image.naturalHeight;canvas.getContext('2d').drawImage(image,0,0);}else{canvas.width=w;canvas.height=h;canvas.getContext('2d').drawImage(image,x,y,w,h,0,0,w,h);}},{once:true});const load=()=>{image.src=url.origin===window.location.origin?url.href:'';};if(thumb&&lazy){wrap.bsLoad=load;lazy.observe(wrap);}else load();wrap.append(canvas);return wrap;}
 function makeCard(a,index){const button=el('button','bs-card');button.type='button';button.setAttribute('aria-label',`${a.my} — ${a.en}`);const image=el('div','bs-card-image');image.append(artImage(a,{thumb:true}));const number=el('span','bs-card-number');number.textContent=String(roomIndexes[roomOf(a)].indexOf(index)+1).padStart(roomIndexes[roomOf(a)].length>99?3:2,'0');button.dataset.bsRoom=roomOf(a);const arrow=el('span','bs-card-arrow');arrow.textContent='↗\uFE0E';image.append(number,arrow);const detail=el('div','bs-card-copy');const title=el('h2');title.dataset.bsCardTitle=String(index);const size=el('span');size.dataset.bsCardSize=String(index);const price=el('strong');price.dataset.bsCardPrice=String(index);detail.append(title,size,price);button.append(image,detail);on(button,'click',()=>openArtwork(index));return button;}
 artworks.forEach((a,i)=>grid.append(makeCard(a,i)));
 // iOS shows a bare ↗ as a blue emoji; U+FE0E asks for the plain text glyph.
 qa('.bs-primary b').forEach(b=>{if(b.textContent.trim()==='↗')b.textContent='↗\uFE0E';});
 const pad=(n,len)=>String(n).padStart(len,'0');
 // Silver collection: one card per design first; opening a design shows all of its sizes.
 const designs=new Map();
 roomIndexes.silver.forEach(i=>{const a=artworks[i],key=String(a.en||a.id).trim().toLowerCase();if(!designs.has(key))designs.set(key,{key,indexes:[]});designs.get(key).indexes.push(i);});
 const area=i=>artworks[i].widthInches*artworks[i].heightInches;
 designs.forEach(d=>d.indexes.sort((p,q)=>area(q)-area(p)));
 let openDesign=null;
 const cardFor=new Map(qa('.bs-card').map((card,i)=>[i,card]));
 designs.forEach(d=>d.indexes.forEach((i,pos)=>{const card=cardFor.get(i);card.dataset.bsDesign=d.key;card.style.order=String(pos);}));
 const designCards=[...designs.values()].map((d,n)=>{
  const lead=artworks[d.indexes[0]];const button=el('button','bs-card bs-design-card');button.type='button';button.dataset.bsRoom='silver';
  const image=el('div','bs-card-image');image.append(artImage(lead,{thumb:true}));const number=el('span','bs-card-number');number.textContent=pad(n+1,2);const arrow=el('span','bs-card-arrow');arrow.textContent='↗\uFE0E';image.append(number,arrow);
  const detail=el('div','bs-card-copy');const title=el('h2');const sizes=el('span');const price=el('strong');detail.append(title,sizes,price);button.append(image,detail);
  button.bsRender=()=>{title.textContent=lead[lang];button.setAttribute('aria-label',`${lead.my} — ${lead.en}`);
   sizes.textContent=d.indexes.length>1?copy[lang].sizes(d.indexes.length):copy[lang].oneSize;
   const cheapest=Math.min(...d.indexes.map(i=>artworks[i].priceMmk));price.textContent=d.indexes.length>1?copy[lang].from(priceText({priceMmk:cheapest},lang)):priceText(lead,lang);};
  on(button,'click',()=>{if(d.indexes.length===1){openArtwork(d.indexes[0]);return;}openDesign=d.key;renderRoomText();root.scrollTo?.({top:0});window.scrollTo?.({top:0});});
  grid.append(button);return button;});
 // Silver designs first in the list, brass works after them.
 qa('.bs-card').filter(c=>c.dataset.bsRoom==='brass').forEach(c=>grid.append(c));
 const backButton=el('button','bs-link bs-design-back');backButton.type='button';backButton.hidden=true;grid.before(backButton);
 on(backButton,'click',()=>{openDesign=null;renderRoomText();});
 const countLabel=r=>{const n=roomIndexes[r].length;return `01—${pad(n,n>99?3:2)}`;};
 // Collection tabs (only when the silver room has artworks).
 const tabs=el('div','bs-tabs');tabs.setAttribute('role','tablist');
 const tabButtons=['silver','brass'].filter(r=>roomIndexes[r].length).map(r=>{const b=el('button','bs-tab');b.type='button';b.dataset.bsTab=r;b.setAttribute('role','tab');on(b,'click',()=>{catalogRoom=r;openDesign=null;renderRoomText();});tabs.append(b);return b;});
 if(tabButtons.length>1)q('.bs-catalog-head>div')?.append(tabs);
 // Switch between the two entrances from the welcome card.
 const switchButton=el('button','bs-link bs-switch-entrance');switchButton.type='button';if(roomIndexes.silver.length)q('[data-bs-welcome]')?.append(switchButton);
 on(switchButton,'click',()=>{entrance=entrance==='silver'?'brass':'silver';catalogRoom=zone=entrance;try{const url=new URL(window.location.href);if(url.searchParams.has('signature'))url.hash=entrance;else{url.searchParams.delete('entrance');url.pathname=url.pathname.replace(/(silver|brass)-art-gallery/,entrance+'-art-gallery');}history.replaceState(null,'',url);}catch{}controls?.reset(entrance);renderRoomText();});
 function renderRoomText(){
  const silver=entrance==='silver';
  const hero=q('[data-bs-welcome] h1'),heroCopy=q('[data-bs-welcome]>p');if(hero)hero.textContent=copy[lang][silver?'silverHeroTitle':'heroTitle'];if(heroCopy)heroCopy.textContent=copy[lang][silver?'silverHeroCopy':'heroCopy'];
  const eyebrow=q('[data-bs-welcome] .bs-eyebrow');if(eyebrow)eyebrow.textContent=`${silver?'SILVER':'BRASS'} ENTRANCE · ${countLabel(entrance)}`;
  switchButton.textContent=copy[lang][silver?'goBrass':'goSilver'];
  const status=q('.bs-status');if(status){const z=zone==='foyer'?entrance:zone;status.replaceChildren(el('i'),Object.assign(el('span'),{textContent:copy[lang][zone==='foyer'?'roomFoyer':z==='silver'?'roomSilver':'roomBrass']}),el('b'),document.createTextNode(countLabel(z)));}
  const design=catalogRoom==='silver'&&openDesign?designs.get(openDesign):null;
  const head=q('.bs-catalog-head');if(head){const lead=design&&artworks[design.indexes[0]];head.querySelector('.bs-eyebrow').textContent=design?`${String(lead.en).toUpperCase()} · ${design.indexes.length} SIZES`:catalogRoom==='silver'?`THE COLLECTION · ${designs.size} DESIGNS · ${roomIndexes.silver.length} WORKS`:`THE COLLECTION · ${pad(roomIndexes[catalogRoom].length,2)} WORKS`;head.querySelector('h1').textContent=design?lead[lang]:copy[lang][catalogRoom==='silver'?'silverCollection':'collection'];}
  backButton.hidden=!design;backButton.textContent=copy[lang].backToDesigns;designCards.forEach(c=>c.bsRender());
  tabButtons.forEach(b=>{const r=b.dataset.bsTab;b.textContent=`${copy[lang][r==='silver'?'tabSilver':'tabBrass']} · ${roomIndexes[r].length}`;b.setAttribute('aria-selected',String(r===catalogRoom));});
  qa('.bs-card').forEach(card=>{const inRoom=tabButtons.length<2||card.dataset.bsRoom===catalogRoom;
   if(card.classList.contains('bs-design-card'))card.hidden=!inRoom||!!design;
   else if(card.dataset.bsDesign)card.hidden=!inRoom||!design||card.dataset.bsDesign!==design.key;
   else card.hidden=!inRoom;});
  if(design)design.indexes.forEach((i,pos)=>{const n=cardFor.get(i).querySelector('.bs-card-number');if(n)n.textContent=pad(pos+1,2);});
  const footer=q('.bs-footer [data-i18n="works"]');if(footer)footer.textContent=copy[lang].worksCount(artworks.length);
 }
 // Keep the room name in the status pill in step with where the visitor is walking.
 const zoneTimer=setInterval(()=>{if(!room||!entered)return;const z=room.zone();if(z!==zone){zone=z;if(z!=='foyer')catalogRoom=z;renderRoomText();}},500);cleanup.push(()=>clearInterval(zoneTimer));
 function translate(){root.lang=lang;qa('[data-i18n]').forEach(node=>{const key=node.dataset.i18n;if(copy[lang][key])node.textContent=copy[lang][key];});qa('[data-bs-card-title]').forEach(node=>node.textContent=artworks[Number(node.dataset.bsCardTitle)][lang]);qa('[data-bs-card-size]').forEach(node=>{const a=artworks[Number(node.dataset.bsCardSize)];node.textContent=dimensions(a);});qa('[data-bs-card-price]').forEach(node=>{const a=artworks[Number(node.dataset.bsCardPrice)];node.textContent=priceText(a,lang);});const toggle=q('[data-bs-language]');{const sep=document.createElement('i');sep.textContent='/';const [p1,p2]=lang==='my'?['EN','မြန်မာ']:['မြန်မာ','EN'];toggle.replaceChildren(p1,sep,p2);}toggle.setAttribute('aria-label',lang==='my'?'English သို့ ပြောင်းရန်':'Switch to Myanmar');updateSoundButton();room?.setLanguage(lang);renderRoomText();if(selected!==null)fillArtwork();}
 function update(){const showCatalog=failed||list;welcome.hidden=entered||showCatalog;inside.hidden=!entered||showCatalog;catalog.hidden=!showCatalog;root.classList.toggle('bs-catalog-mode',showCatalog);roomHost.hidden=showCatalog;controls?.setEnabled(entered&&!showCatalog&&!artDialog.open&&!helpDialog.open);}
 function showFallback(){failed=true;list=true;if(fallback)fallback.hidden=false;update();}
 function fillArtwork(){const a=artworks[selected];q('[data-bs-detail-art]').replaceChildren(artImage(a));const r=roomOf(a),idx=roomIndexes[r],pos=idx.indexOf(selected)+1,len=idx.length>99?3:2;q('[data-bs-number]').textContent=`${r==='silver'?'SILVER':'BRASS'} COLLECTION / ${pad(pos,len)}`;q('.bs-detail-copy .bs-muted').textContent=copy[lang][r==='silver'?'silverMedium':'medium'];q('[data-bs-title]').textContent=a[lang];q('[data-bs-alt]').textContent=a[lang==='my'?'en':'my'];q('[data-bs-size]').textContent=dimensions(a);q('[data-bs-price]').textContent=priceText(a,lang);q('[data-bs-counter]').textContent=`${pad(pos,len)} / ${pad(idx.length,len)}`;}
 function openArtwork(index){selected=index;previousFocus=document.activeElement;fillArtwork();artDialog.showModal();controls?.setEnabled(false);}
 function closeDialog(dialog){if(dialog.open)dialog.close();if(dialog===artDialog)selected=null;update();if(previousFocus instanceof HTMLElement)previousFocus.focus();}
 qa('[data-bs-enter]').forEach(button=>on(button,'click',()=>{if(!failed){entered=true;list=false;update();void piano.start();}}));
 qa('[data-bs-browse]').forEach(button=>on(button,'click',()=>{list=true;openDesign=null;if(entered&&zone!=='foyer')catalogRoom=zone;renderRoomText();update();}));
 on(q('[data-bs-language]'),'click',()=>{lang=lang==='my'?'en':'my';translate();});on(q('[data-bs-reset]'),'click',()=>controls?.reset(zone==='foyer'?entrance:zone));on(soundButton,'click',()=>void piano.toggle());on(q('[data-bs-help]'),'click',()=>{previousFocus=document.activeElement;helpDialog.showModal();update();});
 qa('[data-bs-close]').forEach(button=>on(button,'click',()=>closeDialog(button.closest('dialog'))));const step=d=>{const a=artworks[selected],key=String(a.en||a.id).trim().toLowerCase();const idx=roomOf(a)==='silver'&&openDesign===key?designs.get(key).indexes:roomIndexes[roomOf(a)];selected=idx[(idx.indexOf(selected)+d+idx.length)%idx.length];fillArtwork();};on(q('[data-bs-previous]'),'click',()=>step(-1));on(q('[data-bs-next]'),'click',()=>step(1));
 [artDialog,helpDialog].forEach(dialog=>{on(dialog,'cancel',event=>{event.preventDefault();closeDialog(dialog);});on(dialog,'click',event=>{if(event.target===dialog)closeDialog(dialog);});});
 const joystick=q('[data-bs-joystick]'),knob=q('[data-bs-knob]');let joyPointer=null,knobAnimation=null;
 function moveJoystick(event){if(joyPointer!==event.pointerId)return;const r=joystick.getBoundingClientRect();let x=event.clientX-r.left-r.width/2,y=event.clientY-r.top-r.height/2;const length=Math.hypot(x,y);if(length>34){x=x/length*34;y=y/length*34;}knobAnimation?.cancel();knobAnimation=knob.animate([{transform:`translate(${x}px,${y}px)`}],{duration:1,fill:'forwards'});controls?.setAxis(x/34,y/34);}
 function stopJoystick(){joyPointer=null;knobAnimation?.cancel();knobAnimation=null;controls?.setAxis(0,0);}
 on(joystick,'pointerdown',event=>{joyPointer=event.pointerId;joystick.setPointerCapture(event.pointerId);moveJoystick(event);});on(joystick,'pointermove',moveJoystick);on(joystick,'pointerup',stopJoystick);on(joystick,'pointercancel',stopJoystick);on(joystick,'lostpointercapture',stopJoystick);
 translate();update();
 Promise.all([createRoom(roomHost,()=>{},assetsBase,config,entrance)]).then(([created])=>{if(disposed){created.dispose();return;}room=created;controls=attachControls(room,openArtwork);if(typeof BS_DEBUG!=='undefined')window.__bs={room,controls};room.setLanguage(lang);const enter=q('[data-bs-welcome] [data-bs-enter]');enter.disabled=false;enter.querySelector('span').textContent=copy[lang].enter;update();on(room.renderer.domElement,'webglcontextlost',event=>{event.preventDefault();controls?.setEnabled(false);showFallback();});}).catch(showFallback);
 root.brassShowroomDestroy=()=>{disposed=true;cleanup.forEach(fn=>fn());piano.destroy();controls?.dispose();room?.dispose();root.dataset.initialized='false';};
}

document.querySelectorAll('#brass-showroom.bs-root').forEach(root=>void initialize(root));
