import fs from 'node:fs/promises';
const base='http://127.0.0.1:8011';
const screenshots=new URL('./screenshots/',import.meta.url);
const reportPath=new URL('./screenshots/report.json',import.meta.url);
const report=JSON.parse(await fs.readFile(reportPath,'utf8'));
report.failures=[];
const target=await(await fetch('http://127.0.0.1:9223/json/new?about:blank',{method:'PUT'})).json();
const ws=new WebSocket(target.webSocketDebuggerUrl);let id=0;const pending=new Map();const errors=[];
ws.addEventListener('message',e=>{const m=JSON.parse(e.data);if(m.method==='Runtime.exceptionThrown')errors.push({text:m.params.exceptionDetails.exception?.value??m.params.exceptionDetails.text,stack:m.params.exceptionDetails.stackTrace?.callFrames?.slice(0,3).map(f=>`${f.url}:${f.lineNumber+1}`)});if(m.id&&pending.has(m.id)){const p=pending.get(m.id);pending.delete(m.id);m.error?p.reject(m.error):p.resolve(m.result)}});
await new Promise(r=>ws.addEventListener('open',r,{once:true}));
const send=(method,params={})=>new Promise((resolve,reject)=>{const n=++id;pending.set(n,{resolve,reject});ws.send(JSON.stringify({id:n,method,params}))});
const ev=async expression=>{const r=await send('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});if(r.exceptionDetails)throw Error(r.exceptionDetails.text);return r.result?.value};
const wait=ms=>new Promise(r=>setTimeout(r,ms));
async function login(email,password){await send('Network.clearBrowserCookies');await send('Page.navigate',{url:`${base}/login`});await wait(700);await ev(`(()=>{const f=document.querySelector('form');f.elements.identifier.value=${JSON.stringify(email)};f.elements.password.value=${JSON.stringify(password)};f.requestSubmit()})()`);await wait(1200)}
async function goto(path,width=1440,height=1000,mobile=false){await send('Emulation.setDeviceMetricsOverride',{width,height,deviceScaleFactor:1,mobile});await send('Page.navigate',{url:`${base}${path}`});await wait(800)}
async function capture(name){await wait(300);const {data}=await send('Page.captureScreenshot',{format:'png',captureBeyondViewport:false,fromSurface:true});await fs.writeFile(new URL(name,screenshots),Buffer.from(data,'base64'))}
async function clickWire(expression,visible=true){return ev(`(()=>{const b=[...document.querySelectorAll('button')].find(el=>el.getAttribute('wire:click')===${JSON.stringify(expression)}&&(!${visible}||el.getClientRects().length));if(!b)return false;b.click();return true})()`)}
async function dialogIncludes(text){return ev(`Boolean([...document.querySelectorAll('[role=dialog]')].some(d=>d.innerText.includes(${JSON.stringify(text)})))`)}
const results=[];
await send('Page.enable');await send('Runtime.enable');
await login('owner@fastlogi.test','password123');await goto('/negocio/inventario');
let clicked=await clickWire('openEditModal(2)');await wait(800);let opened=await dialogIncludes('Editar producto');
if(opened)await capture('inventory-edit-modal-desktop.png');
results.push({path:'/negocio/inventario',mode:'desktop',action:'Editar producto 2',clicked,opened});
await goto('/negocio/inventario',390,844,true);clicked=await clickWire('openEditModal(2)');await wait(800);opened=await dialogIncludes('Editar producto');
if(opened)await capture('inventory-edit-modal-mobile.png');
results.push({path:'/negocio/inventario',mode:'mobile',action:'Editar producto 2',clicked,opened});
await goto('/negocio/inventario');
await ev('window.confirm=()=>true');
const before=await ev(`Boolean([...document.querySelectorAll('tr')].find(el=>el.getAttribute('wire:key')==='product-7'))`);
clicked=await clickWire('delete(7)');await wait(1300);
const after=await ev(`Boolean([...document.querySelectorAll('tr')].find(el=>el.getAttribute('wire:key')==='product-7'))`);
await capture('inventory-delete-result-desktop.png');
results.push({path:'/negocio/inventario',mode:'desktop',action:'Eliminar producto 7 sin pedidos asociados',clicked,rowExistedBefore:before,rowExistsAfter:after,deleted:before&&!after});

await login('owner@fastlogi.test','password123');await goto('/negocio/repartidores');
let driverEditAction=await ev(`[...document.querySelectorAll('button')].find(el=>el.getAttribute('wire:click')?.startsWith('openEditModal(')&&el.getClientRects().length)?.getAttribute('wire:click')`);
clicked=driverEditAction?await clickWire(driverEditAction):false;await wait(800);opened=await dialogIncludes('Editar repartidor');
if(opened)await capture('drivers-edit-modal-desktop.png');
results.push({path:'/negocio/repartidores',mode:'desktop',action:`Editar ${driverEditAction??'repartidor'}`,clicked,opened});
await goto('/negocio/repartidores',390,844,true);driverEditAction=await ev(`[...document.querySelectorAll('button')].find(el=>el.getAttribute('wire:click')?.startsWith('openEditModal(')&&el.getClientRects().length)?.getAttribute('wire:click')`);clicked=driverEditAction?await clickWire(driverEditAction):false;await wait(800);opened=await dialogIncludes('Editar repartidor');
if(opened)await capture('drivers-edit-modal-mobile.png');
results.push({path:'/negocio/repartidores',mode:'mobile',action:`Editar ${driverEditAction??'repartidor'}`,clicked,opened});
await goto('/negocio/repartidores');
let driverRowKey=await ev(`[...document.querySelectorAll('tr')].find(el=>el.getAttribute('wire:key')?.startsWith('driver-')&&el.getClientRects().length)?.getAttribute('wire:key')`);
let toggleAction=await ev(`[...([...document.querySelectorAll('tr')].find(el=>el.getAttribute('wire:key')===${JSON.stringify(driverRowKey)})?.querySelectorAll('button')??[])].map(el=>el.getAttribute('wire:click')).find(value=>value?.startsWith('toggleAvailability('))`);
const statusBefore=await ev(`[...document.querySelectorAll('tr')].find(el=>el.getAttribute('wire:key')===${JSON.stringify(driverRowKey)})?.innerText`);
clicked=toggleAction?await clickWire(toggleAction):false;await wait(800);
const statusChanged=await ev(`[...document.querySelectorAll('tr')].find(el=>el.getAttribute('wire:key')===${JSON.stringify(driverRowKey)})?.innerText`);
const toggled=Boolean(statusBefore&&statusChanged&&statusBefore!==statusChanged);
if(toggled){await clickWire(toggleAction);await wait(800)}
const statusRestored=await ev(`[...document.querySelectorAll('tr')].find(el=>el.getAttribute('wire:key')===${JSON.stringify(driverRowKey)})?.innerText`);
results.push({path:'/negocio/repartidores',mode:'desktop',action:`Cambiar disponibilidad ${toggleAction??''} y volver en línea`,clicked,statusBefore,statusChanged,toggled,restored:Boolean(statusRestored&&!statusRestored.includes('Desconectado')),statusAfterRestore:statusRestored?.match(/Disponible|Ocupado|Desconectado/)?.[0]});

report.interactionAudit=results;report.actionJavascriptErrors=errors;
for(const result of results){if(!result.clicked||(result.opened===false)||result.deleted===false||(result.toggled===false)||(result.restored===false))report.failures.push({issue:`Falló acción UI: ${result.action}`,result})}
if(errors.length)report.failures.push({issue:`${errors.length} excepción(es) JavaScript en auditoría de acciones`,details:errors});
await fs.writeFile(reportPath,JSON.stringify(report,null,2));
console.log(JSON.stringify({results,errors,failures:report.failures},null,2));ws.close();
