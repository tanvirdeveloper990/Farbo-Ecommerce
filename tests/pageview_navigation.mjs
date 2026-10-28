import assert from 'node:assert/strict';
const scripts=new Map();const calls=[];let initialized=false;
function fbq(...args){if(args[0]==='init'){assert.equal(fbq.disablePushState,true,'Automatic history tracking must be disabled before Pixel init');initialized=true;}calls.push(args);}
globalThis.document={getElementById:id=>scripts.get(id),createElement:()=>({}),head:{appendChild:node=>{if(node.id==='store-meta-pixel')assert.equal(fbq.disablePushState,true,'Flag must be set before SDK load');scripts.set(node.id,node);}}};
globalThis.window={fbq,location:{pathname:'/',origin:'https://store.example'},history:{pushState(_state,_title,url){window.location.pathname=url;if(initialized && !fbq.disablePushState)fbq('track','PageView');},replaceState(_state,_title,url){window.location.pathname=url;if(initialized && !fbq.disablePushState)fbq('track','PageView');}},sessionStorage:{getItem:()=>JSON.stringify([{id:7,name:'Saree',price:100,qty:1}])},localStorage:{getItem:()=>null,setItem:()=>{}}};
const {trackPage}=await import('../resources/js/Storefront/tracking.js');const settings={meta_pixel_enabled:true,meta_pixel_id:'123456789012345'};
const page=(name,url,props={})=>({component:'Storefront/'+name,url,props:{storeSettings:settings,...props}});
const pageViews=()=>calls.filter(a=>a[0]==='trackSingle' && a[2]==='PageView' || a[0]==='track' && a[1]==='PageView').length;
trackPage(page('Home','/',{products:[]}));assert.equal(pageViews(),1);console.log('PASS: Initial page emits one PageView');
for(const [name,url,props] of [['Category','/categories/7',{category:{name:'Saree'},products:{data:[]}}],['ProductDetails','/product/12',{product:{id:12,name:'Saree',price:4050}}],['Checkout','/checkout',{}]]){const before=pageViews();window.history.pushState({},'',url);trackPage(page(name,url,props));trackPage(page(name,url,props));assert.equal(pageViews()-before,1);console.log('PASS: '+name+' history navigation and remount emit one PageView');}
assert.equal(calls.filter(a=>a[2]==='ViewContent').length,1);assert.equal(calls.filter(a=>a[2]==='InitiateCheckout').length,1);console.log('PASS: Product and checkout ecommerce events remain intact');
const before=pageViews();window.history.replaceState({},'','/categories/7');trackPage(page('Category','/categories/7',{products:[]}));assert.equal(pageViews()-before,1);console.log('PASS: Replace-state navigation emits one PageView');
const revisit=pageViews();window.history.pushState({},'','/product/12');trackPage(page('ProductDetails','/product/12',{product:{id:12,name:'Saree',price:4050}}));assert.equal(pageViews()-revisit,1);console.log('PASS: Revisited product still emits one new PageView');
assert.equal(scripts.size,1);console.log('PASS: Pixel SDK remains a single script');
