const fs = require('node:fs');
const { parse, compileScript } = require('@vue/compiler-sfc');
const Vue = require('vue');
const { renderToString } = require('@vue/server-renderer');
let page = { props: {} };
const stub = { render() { return Vue.h('div', null, this.$slots.default?.()); } };
const modules = {
    vue: Vue,
    '@inertiajs/vue3': {
        usePage: () => page,
        useForm: data => Vue.reactive({ ...data, errors: {}, processing: false }),
        Head: { render: () => null },
        Link: { props: ['href'], render() { return Vue.h('a', { href: this.href }, this.$slots.default?.()); } },
    },
    '@lucide/vue': require('@lucide/vue'),
};
function load(path) {
    const { descriptor } = parse(fs.readFileSync(path, 'utf8'));
    let code = compileScript(descriptor, { id: path, inlineTemplate: true }).content;
    code = code.replace(/import\s+\{([^}]+)\}\s+from\s+["']([^"']+)["'];?/g, (_, names, source) =>
        `const {${names.replace(/\s+as\s+/g, ':')}} = modules[${JSON.stringify(source)}];`);
    code = code.replace(/import\s+(\w+)\s+from\s+["']([^"']+)["'];?/g, (_, name) => `const ${name} = stub;`);
    code = code.replace('export default', 'return');
    return new Function('modules', 'stub', code)(modules, stub);
}
async function render(component, props) {
    const app = Vue.createSSRApp(component, props);
    app.provide('storefront', { closePanel() {} });
    return renderToString(app);
}
function check(condition, message) {
    if (!condition) throw new Error(message);
    console.log(`PASS: ${message}`);
}
(async () => {
    const form = load('resources/js/Pages/CategoryForm.vue');
    let html = await render(form, { type: 'category', categories: [], subcategories: [] });
    if (process.env.CATEGORY_PREVIEW) {
const source = fs.readFileSync('resources/js/Pages/CategoryForm.vue','utf8');
const section = html.slice(html.indexOf('<section class="visibility-section"'), html.indexOf('</section>', html.indexOf('<section class="visibility-section"')) + 10);
const style = source.split('<style scoped>')[1].split('</style>')[0];
fs.writeFileSync('public/category-ui-preview.html', '<!doctype html><html><head><meta name="viewport" content="width=device-width,initial-scale=1"><style>:root{font-family:Inter,Arial,sans-serif;--green:#23694f;--dark:#183d30;--line:#e9ede7;--cream:#eef2e8}*{box-sizing:border-box}body{margin:0;background:#f6f7f4;padding:40px 20px}main{max-width:760px;margin:auto;background:white;padding:28px;border:1px solid #e9ede7;border-radius:16px}button{font:inherit}'+style+'</style></head><body><main>'+section+'</main></body></html>');
}
check((html.match(/role="switch"/g) || []).length === 4, 'Category form renders exactly four On/Off switches');
    for (const type of ['subcategory', 'child']) {
        html = await render(form, { type, categories: [], subcategories: [] });
        check(!html.includes('role="switch"'), `${type} form hides the visibility switches`);
    }
    const navigation = load('resources/js/Storefront/Components/StoreNavigation.vue');
    const popular = load('resources/js/Storefront/Components/PopularCategories.vue');
    html = await render(popular, { categories: [{ id: 42, name: 'Popular selection', image: '/example.jpg' }] });
    check(html.includes('Popular selection') && html.includes('/categories/42'), 'Existing Popular Category section renders selected categories and links');
    html = await render(popular, { categories: [] });
    check(!html.includes('/categories/42'), 'Popular Category section does not render excluded categories');
    page.props.storeCategories = Array.from({ length: 12 }, (_, i) => ({ id: i + 1, name: `Category ${i + 1}`, subcategories: [] }));
    page.props.sidebarCategories = [{ id: 99, name: 'Sidebar only', subcategories: [] }];
    html = await render(navigation, { mobile: false });
    check((html.match(/href="\/categories\//g) || []).length === 10 && html.includes('More'), 'Header renders ten category links and a More button');
    page.props.storeCategories = page.props.storeCategories.slice(0, 10);
    html = await render(navigation, { mobile: false });
    check(!html.includes('more-categories'), 'Header hides More when there are ten or fewer categories');
    html = await render(navigation, { mobile: true });
    check(html.includes('Sidebar only') && !html.includes('Category 1'), 'Sidebar uses its independent category list');
})().catch(error => { console.error(error); process.exitCode = 1; });
