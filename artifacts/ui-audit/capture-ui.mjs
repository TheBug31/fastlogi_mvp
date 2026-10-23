import fs from 'node:fs/promises';

const base = 'http://127.0.0.1:8011';
const out = new URL('./screenshots/', import.meta.url);
await fs.mkdir(out, { recursive: true });
const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
const target = await (await fetch('http://127.0.0.1:9223/json/new?about:blank', { method: 'PUT' })).json();
const socket = new WebSocket(target.webSocketDebuggerUrl);
let id = 0;
const pending = new Map();
const pageErrors = [];
socket.addEventListener('message', (event) => {
    const msg = JSON.parse(event.data);
    if (msg.method === 'Runtime.exceptionThrown') pageErrors.push({ kind: 'exception', text: msg.params.exceptionDetails.text, details: msg.params.exceptionDetails.exception?.description, stack: msg.params.exceptionDetails.stackTrace?.callFrames?.slice(0,4).map(frame => `${frame.url}:${frame.lineNumber+1}`) });
    if (msg.method === 'Runtime.consoleAPICalled' && msg.params.type === 'error') pageErrors.push({ kind: 'console', text: msg.params.args.map((arg) => arg.value ?? arg.description ?? '').join(' ') });
    if (msg.id && pending.has(msg.id)) {
        const p = pending.get(msg.id); pending.delete(msg.id);
        msg.error ? p.reject(new Error(msg.error.message)) : p.resolve(msg.result);
    }
});
await new Promise((resolve, reject) => { socket.addEventListener('open', resolve, { once: true }); socket.addEventListener('error', reject, { once: true }); });
function send(method, params = {}) {
    const requestId = ++id;
    socket.send(JSON.stringify({ id: requestId, method, params }));
    return new Promise((resolve, reject) => pending.set(requestId, { resolve, reject }));
}
async function evaluate(expression) {
    const r = await send('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
    if (r.exceptionDetails) throw new Error(r.exceptionDetails.text);
    return r.result?.value;
}
async function navigate(path) { await send('Page.navigate', { url: `${base}${path}` }); await wait(900); }
async function login(email, password) {
    await send('Network.clearBrowserCookies');
    await navigate('/login');
    await evaluate(`(() => { const f=document.querySelector('form'); f.elements.identifier.value=${JSON.stringify(email)}; f.elements.password.value=${JSON.stringify(password)}; f.requestSubmit(); })()`);
    await wait(1400);
    const result = await evaluate('({ path: location.pathname, text: document.body.innerText.slice(0,160) })');
    if (result.path === '/login') throw new Error(`No inició sesión ${email}: ${result.text}`);
}
async function screenshot(name) {
    await wait(400);
    const { data } = await send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: false, fromSurface: true });
    await fs.writeFile(new URL(name, out), Buffer.from(data, 'base64'));
}
async function visitRole(label, email, password, routes) {
    await login(email, password);
    for (const [slug, path] of routes) {
        for (const mode of [{ name: 'desktop', width: 1440, height: 1000, mobile: false }, { name: 'mobile', width: 390, height: 844, mobile: true }]) {
            await send('Emulation.setDeviceMetricsOverride', { width: mode.width, height: mode.height, deviceScaleFactor: 1, mobile: mode.mobile });
            await navigate(path);
            const state = await evaluate('({ path: location.pathname, title: document.title, heading: document.querySelector("h1")?.innerText ?? document.querySelector("h2")?.innerText ?? "", body: document.body.innerText.slice(0,240), dialogs: document.querySelectorAll("[role=dialog]").length, components: window.Livewire?.all?.().map(c => c.name) ?? [] })');
            const file = `${label}-${slug}-${mode.name}.png`;
            await screenshot(file);
            report.pages.push({ role: label, path, mode: mode.name, file, state });
            if (!state.path.startsWith(path.split('{')[0])) report.failures.push({ path, issue: `Redirigió a ${state.path}`, state });
            if (state.body.includes('Whoops!') || state.body.includes('Server Error')) report.failures.push({ path, issue: 'Pantalla de error', state });
            if (state.heading === '404' || state.title === 'Not Found') report.failures.push({ path, issue: 'Ruta devuelve 404', state });
        }
    }
}
const report = { pages: [], failures: [], javascriptErrors: pageErrors };
await send('Page.enable'); await send('Runtime.enable'); await send('Network.enable');
await send('Emulation.setDeviceMetricsOverride', { width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false });
await send('Network.clearBrowserCookies');
await navigate('/login'); await screenshot('login-desktop.png');
report.pages.push({ role: 'guest', path: '/login', mode: 'desktop', file: 'login-desktop.png', state: await evaluate('({path:location.pathname,title:document.title,heading:document.querySelector("h1")?.innerText??"",body:document.body.innerText.slice(0,200)})') });
await send('Emulation.setDeviceMetricsOverride', { width: 390, height: 844, deviceScaleFactor: 1, mobile: true });
await navigate('/login'); await screenshot('login-mobile.png');
report.pages.push({ role: 'guest', path: '/login', mode: 'mobile', file: 'login-mobile.png', state: await evaluate('({path:location.pathname,title:document.title,heading:document.querySelector("h1")?.innerText??"",body:document.body.innerText.slice(0,200)})') });
await visitRole('owner', 'owner@fastlogi.test', 'password123', [
    ['dashboard','/negocio'],['settings','/negocio/configuracion'],['inventory','/negocio/inventario'],['notifications','/negocio/notificaciones'],['orders','/negocio/pedidos'],['owner-order-detail','/negocio/pedidos/maria-camila-rodriguez'],['new-order','/negocio/pedidos/nuevo'],['drivers','/negocio/repartidores'],['reports','/negocio/reportes'],
]);
await login('owner@fastlogi.test', 'password123');
await send('Emulation.setDeviceMetricsOverride', { width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false });
await navigate('/negocio/inventario');
const inventoryAction = await evaluate(`(() => { const button=Array.from(document.querySelectorAll('button')).find(b=>b.getAttribute('wire:click')==='openCreateModal'); button?.click(); return {found:Boolean(button)}; })()`);
await wait(900);
inventoryAction.modalAfterClick = await evaluate(`Boolean(Array.from(document.querySelectorAll('[role=dialog]')).find(d=>d.innerText.includes('Nuevo producto')))`);
report.interactions = [{ role: 'owner', path: '/negocio/inventario', action: 'click Nuevo producto', result: inventoryAction }];
if (!inventoryAction.modalAfterClick) {
    report.failures.push({ path: '/negocio/inventario', issue: 'El clic real en Nuevo producto no abre el modal.' });
    await evaluate(`window.Livewire.all().find(c=>c.name==='owner.inventory').$wire.$call('openCreateModal')`);
    await wait(500);
    report.interactions[0].result.directComponentCallOpensModal = await evaluate(`Boolean(Array.from(document.querySelectorAll('[role=dialog]')).find(d=>d.innerText.includes('Nuevo producto')))`);
}
await screenshot('owner-inventory-create-modal-desktop.png');
await send('Emulation.setDeviceMetricsOverride', { width: 390, height: 844, deviceScaleFactor: 1, mobile: true });
await screenshot('owner-inventory-create-modal-mobile.png');
await login('driver1@fastlogi.test', 'driverpass');
for (const [slug,path] of [['history','/repartidor/historial'],['orders','/repartidor/pedidos/julian-david-morales'],['routes','/repartidor/rutas']]) {
    for (const mode of [{name:'desktop',width:1440,height:1000,mobile:false},{name:'mobile',width:390,height:844,mobile:true}]) {
        await send('Emulation.setDeviceMetricsOverride', {width:mode.width,height:mode.height,deviceScaleFactor:1,mobile:mode.mobile});
        await navigate(path);
        const state = await evaluate('({path:location.pathname,title:document.title,heading:document.querySelector("h1")?.innerText??document.querySelector("h2")?.innerText??"",body:document.body.innerText.slice(0,240),dialogs:document.querySelectorAll("[role=dialog]").length,components:window.Livewire?.all?.().map(c=>c.name)??[]})');
        const file=`driver-${slug}-${mode.name}.png`; await screenshot(file); report.pages.push({role:'driver',path,mode:mode.name,file,state});
        if (!state.path.startsWith(path)) report.failures.push({path,issue:`Redirigió a ${state.path}`,state});
        if (state.body.includes('Whoops!')||state.body.includes('Server Error')) report.failures.push({path,issue:'Pantalla de error',state});
        if (state.heading === '404' || state.title === 'Not Found') report.failures.push({path,issue:'Ruta devuelve 404',state});
    }
}
await login('owner@fastlogi.test', 'password123');
await send('Emulation.setDeviceMetricsOverride', { width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false });
await navigate('/negocio/repartidores');
const driverAction = await evaluate(`(() => { const button=Array.from(document.querySelectorAll('button')).find(b=>b.getAttribute('wire:click')==='openCreateModal'); button?.click(); return {found:Boolean(button)}; })()`);
await wait(700);
driverAction.modalAfterClick = await evaluate(`Boolean(Array.from(document.querySelectorAll('[role=dialog]')).find(d=>d.innerText.includes('Nuevo repartidor')))`);
report.interactions.push({ role: 'owner', path: '/negocio/repartidores', action: 'click Nuevo repartidor', result: driverAction });
if (!driverAction.modalAfterClick) {
    report.failures.push({ path: '/negocio/repartidores', issue: 'El clic real en Nuevo repartidor no abre el modal.' });
    await evaluate(`window.Livewire.all().find(c=>c.name==='owner.drivers').$wire.$call('openCreateModal')`);
    await wait(500);
    report.interactions[1].result.directComponentCallOpensModal = await evaluate(`Boolean(Array.from(document.querySelectorAll('[role=dialog]')).find(d=>d.innerText.includes('Nuevo repartidor')))`);
}
await screenshot('owner-drivers-create-modal-desktop.png');
await send('Emulation.setDeviceMetricsOverride', { width: 390, height: 844, deviceScaleFactor: 1, mobile: true });
await screenshot('owner-drivers-create-modal-mobile.png');
await visitRole('superadmin','super@fastlogi.test','superadmin',[['dashboard','/superadmin'],['businesses','/superadmin/negocios']]);
if (pageErrors.length) report.failures.push({ issue: `Se produjeron ${pageErrors.length} excepciones JavaScript durante el recorrido.` });
await fs.writeFile(new URL('report.json', out), JSON.stringify(report,null,2));
console.log(JSON.stringify({ captured: report.pages.length, failures: report.failures, javascriptErrors: report.javascriptErrors.length, directory: out.pathname }, null, 2));
socket.close();
