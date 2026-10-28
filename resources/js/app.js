import "../css/app.css";
import { createApp, h } from "vue";
import { createInertiaApp, router } from "@inertiajs/vue3";
import Swal from "sweetalert2";
const toast = (icon, title) =>
    Swal.fire({
        toast: true,
        position: "top-end",
        icon,
        title,
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
    });
router.on("success", (event) => {
    const f = event.detail.page.props.flash;
    if (f?.success) toast("success", f.success);
    if (f?.error) toast("error", f.error);
});
router.on("error", () =>
    toast("error", "Please check the highlighted fields."),
);
// Storefront uses a separate stylesheet and root document from the account UI.
router.on("before", (event) => {
    if (event.detail.visit.url.pathname === "/") {
        window.location.assign(event.detail.visit.url.href);
        return false;
    }
});
createInertiaApp({
    title: (title) => `${title} · ${window.farboCompanyName || 'FARBO'}`,
    resolve: (name) => {
        const pages = import.meta.glob("./Pages/*.vue", { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup: ({ el, App, props, plugin }) =>
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el),
    progress: { color: "#E30613" },
});
