/* ---------- Menu data (edit categories / subcategories here) ---------- */
const sourceMenu = window.farboMenu || [];
const remainingMenu = sourceMenu.slice(5);
const menuData = [...sourceMenu.slice(0,5), ...(remainingMenu.length ? [{title:'More',icon:'fa-solid fa-layer-group',url:'/categories',items:[],groups:remainingMenu}] : [])];
const escapeHtml = value => String(value ?? "").replace(/[&<>"\x27]/g, character => ({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","\x27":"&#39;"}[character]));

/* ---------- Desktop navbar with hover dropdown ---------- */
const desktopNav = document.getElementById("desktopNav");
desktopNav.style.setProperty('--farbo-nav-count', Math.max(1, menuData.length));
menuData.forEach((m, i) => {
  const li = document.createElement("li");
  li.className =
    "relative group flex-1 flex justify-center" +
    (i ? " border-l border-white/15" : "");
  li.innerHTML = `
        <a href="${escapeHtml(m.url)}" class="flex items-center gap-2 py-4 px-2 text-white text-[13px] xl:text-sm font-bold uppercase whitespace-nowrap group-hover:text-white">
          <span>${escapeHtml(m.title)}</span>
          <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-300 group-hover:rotate-180"></i>
        </a>
        <div class="farbo-nav-dropdown absolute top-full ${i >= 4 ? "right-0" : "left-0"} w-72 opacity-0 invisible translate-y-3 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 transition-all duration-300 z-50">
          <div class="bg-white rounded-b-xl shadow-2xl border-t-4 border-brand-red overflow-hidden">
            <ul class="py-2 max-h-[65vh] overflow-y-auto">
              ${m.groups ? m.groups.map(category => `<li class="border-b border-gray-100 last:border-0"><a href="${escapeHtml(category.url)}" class="block px-5 py-3 text-sm font-semibold text-brand-navy hover:text-brand-red hover:bg-brand-light">${escapeHtml(category.title)}</a>${category.items.length ? `<details class="px-5 pb-3"><summary class="cursor-pointer text-xs font-medium text-gray-500 hover:text-brand-red">Subcategories</summary><ul class="mt-2 border-l-2 border-gray-100">${category.items.map(([name,icon,url])=>`<li><a href="${escapeHtml(url)}" class="block pl-3 py-2 text-sm text-gray-600 hover:text-brand-red">${escapeHtml(name)}</a></li>`).join('')}</ul></details>` : ''}</li>`).join('') : m.items
                .map(
                  ([name, icon, url]) => `
                <li class="border-b border-gray-100 last:border-0">
                  <a href="${escapeHtml(url)}" class="group/sub relative block px-5 py-3 text-sm font-medium text-gray-700 hover:text-brand-red hover:bg-brand-light hover:pl-7 transition-all duration-300">
                    <span class="absolute left-0 top-0 h-full w-[3px] bg-brand-red scale-y-0 group-hover/sub:scale-y-100 transition-transform duration-300"></span>
                    ${escapeHtml(name)}
                  </a>
                </li>`,
                )
                .join("")}
            </ul>
            <a href="${escapeHtml(m.url)}" class="farbo-nav-view-all flex items-center justify-center gap-2 py-3 bg-brand-navy text-white text-xs font-semibold uppercase tracking-wider hover:bg-brand-red transition">
              <span>View All ${escapeHtml(m.title)}</span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </a>
          </div>
        </div>`;
  desktopNav.appendChild(li);
});

/* ---------- Mobile sidebar accordion ---------- */
const mobileNav = document.getElementById("mobileNav");
(window.farboSidebarMenu || menuData).forEach((m) => {
  const wrap = document.createElement("div");
  wrap.className = "border-b border-gray-100";
  wrap.innerHTML = `
        <button class="acc-btn w-full flex items-center gap-3 px-5 py-3.5 text-left font-semibold text-gray-800 hover:text-brand-red transition">
          <span class="w-9 h-9 grid place-items-center rounded-lg bg-brand-navy text-white"><i class="${m.icon} text-sm"></i></span>
          <span class="flex-1 text-sm">${escapeHtml(m.title)}</span>
          <i class="acc-icon fa-solid fa-chevron-down text-xs text-gray-400 transition-transform duration-300"></i>
        </button>
        <div class="acc-panel max-h-0 overflow-hidden transition-all duration-300 bg-gray-50">
          <ul class="py-2">
            ${m.items
              .map(
                ([name, icon, url]) => `
              <li><a href="${escapeHtml(url)}" class="flex items-center gap-3 pl-[4.25rem] pr-5 py-2.5 text-sm text-gray-600 hover:text-brand-red transition">
                <i class="${icon} text-xs w-4 text-brand-red"></i>${escapeHtml(name)}</a></li>`,
              )
              .join("")}
          </ul>
        </div>`;
  mobileNav.appendChild(wrap);
});
mobileNav.addEventListener("click", (e) => {
  const btn = e.target.closest(".acc-btn");
  if (!btn) return;
  const panel = btn.nextElementSibling;
  const isOpen = panel.style.maxHeight;
  mobileNav
    .querySelectorAll(".acc-panel")
    .forEach((p) => (p.style.maxHeight = null));
  mobileNav
    .querySelectorAll(".acc-icon")
    .forEach((ic) => ic.classList.remove("rotate-180"));
  if (!isOpen) {
    panel.style.maxHeight = panel.scrollHeight + "px";
    btn.querySelector(".acc-icon").classList.add("rotate-180");
  }
});

/* ---------- Drawers (mobile menu + cart) ---------- */
const overlay = document.getElementById("overlay");
const mobileMenu = document.getElementById("mobileMenu");
const cartDrawer = document.getElementById("cartDrawer");

function openDrawer(el, hiddenClass) {
  el.classList.remove(hiddenClass);
  overlay.classList.remove("opacity-0", "invisible");
  document.body.classList.add("no-scroll");
}
function closeDrawers() {
  mobileMenu.classList.add("-translate-x-full");
  cartDrawer.classList.add("translate-x-full");
  overlay.classList.add("opacity-0", "invisible");
  document.body.classList.remove("no-scroll");
}
document.getElementById("openMenu").onclick = () =>
  openDrawer(mobileMenu, "-translate-x-full");
document.getElementById("openCart").onclick = () =>
  openDrawer(cartDrawer, "translate-x-full");
overlay.onclick = closeDrawers;
document
  .querySelectorAll(".close-drawer")
  .forEach((b) => (b.onclick = closeDrawers));
document.addEventListener(
  "keydown",
  (e) => e.key === "Escape" && closeDrawers(),
);

/* ---------- Mobile search toggle ---------- */
const mobileSearch = document.getElementById("mobileSearch");
const searchBtn = document.getElementById("toggleSearch");
const searchIcon = document.getElementById("searchIcon");
searchBtn.onclick = () => {
  const open = mobileSearch.style.maxHeight;
  mobileSearch.style.maxHeight = open ? null : mobileSearch.scrollHeight + "px";
  // Swap search icon <-> cross icon
  searchIcon.classList.toggle("fa-magnifying-glass", !!open);
  searchIcon.classList.toggle("fa-xmark", !open);
  searchIcon.classList.toggle("rotate-90", !open);
  searchBtn.classList.toggle("bg-brand-red", !open);
  searchBtn.classList.toggle("text-white", !open);
  searchBtn.classList.toggle("text-brand-navy", !!open);
  if (!open)
    setTimeout(() => document.getElementById("mobileSearchInput").focus(), 250);
};

/* ---------- Sticky header shadow ---------- */
const sticky = document.getElementById("stickyHeader");
window.addEventListener("scroll", () =>
  sticky.classList.toggle("shadow-lg", window.scrollY > 40),
);

window.farboCloseDrawers = closeDrawers;
window.farboInitSliders = function () {
const lifecycle = new AbortController();
const cleanups = [];
/* ---------- Banner slider ---------- */
if (document.getElementById("sliderTrack")?.children.length) {
const track = document.getElementById("sliderTrack");
const slides = track.children;
let current = 0,
  timer;
cleanups.push(() => clearInterval(timer));


function goTo(i) {
  current = (i + slides.length) % slides.length;
  track.style.transform = `translateX(-${current * 100}%)`;

}
const next = () => goTo(current + 1);
const restart = () => {
  clearInterval(timer);
  timer = setInterval(next, 5000);
};
document.getElementById("nextSlide").onclick = () => {
  next();
  restart();
};
document.getElementById("prevSlide").onclick = () => {
  goTo(current - 1);
  restart();
};
const slider = document.getElementById("slider");
slider.addEventListener("mouseenter", () => clearInterval(timer));
slider.addEventListener("mouseleave", restart);
// Touch swipe
let startX = 0;
slider.addEventListener("touchstart", (e) => (startX = e.touches[0].clientX), {
  passive: true,
});
slider.addEventListener("touchend", (e) => {
  const diff = e.changedTouches[0].clientX - startX;
  if (Math.abs(diff) > 40) {
    diff < 0 ? next() : goTo(current - 1);
    restart();
  }
});
goTo(0);
restart();

}

// product js

document.querySelectorAll('.product-slider').forEach(section => {
  const track = section.querySelector('.ps-track');
  if (!track || !track.querySelector('.ps-card')) return;
  track.style.touchAction = 'pan-x pan-y';
  track.querySelectorAll('img, a').forEach(element => element.draggable = false);
  track.addEventListener('dragstart', event => event.preventDefault());
  let drag = null, blockClick = false;
  track.addEventListener('pointerdown', event => {
    if (section.classList.contains('category-product-section') && window.innerWidth < 1024) return;
    if (event.pointerType !== 'mouse' || event.button !== 0 || event.target.closest('button,input,select,textarea')) return;
    blockClick = false;
    drag = {id:event.pointerId, x:event.clientX, left:track.scrollLeft, moved:false};
  });
  track.addEventListener('pointermove', event => {
    if (!drag || drag.id !== event.pointerId) return;
    const distance = event.clientX - drag.x;
    if (!drag.moved && Math.abs(distance) <= 6) return;
    if (!drag.moved) {
      drag.moved = true;
      track.setPointerCapture(event.pointerId);
      track.style.scrollSnapType = 'none';
      track.style.scrollBehavior = 'auto';
      track.style.cursor = 'grabbing';
    }
    event.preventDefault();
    track.scrollLeft = drag.left - distance;
  });
  const finish = event => {
    if (!drag || (event.pointerId !== undefined && event.pointerId !== drag.id)) return;
    blockClick = drag.moved;
    const id = drag.id;
    drag = null;
    if (track.hasPointerCapture(id)) track.releasePointerCapture(id);
    track.style.removeProperty('scrollSnapType');
    track.style.removeProperty('scrollBehavior');
    track.style.removeProperty('cursor');
  };
  track.addEventListener('pointerup', finish);
  track.addEventListener('pointercancel', finish);
  track.addEventListener('lostpointercapture', finish);
  track.addEventListener('pointerleave', event => { if (drag && !drag.moved) finish(event); });
  window.addEventListener('blur', finish, {signal:lifecycle.signal});
  track.addEventListener('click', event => {
    if (blockClick) {event.preventDefault(); event.stopPropagation(); blockClick = false;}
  }, true);
});

return () => { lifecycle.abort(); cleanups.forEach(cleanup => cleanup()); };
};
window.farboInitSliders();

