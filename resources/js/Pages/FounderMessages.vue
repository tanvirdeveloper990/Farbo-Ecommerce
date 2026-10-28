<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, GripVertical, ArrowUp, ArrowDown } from '@lucide/vue';
import Swal from 'sweetalert2';
import AdminLayout from '../Components/AdminLayout.vue';
const props = defineProps({records: Array});
const rows = ref([...props.records]), saving = ref(false), dragged = ref(null), armed = ref(null);
watch(() => props.records, value => {rows.value = [...value];});
function persist() {
    saving.value = true;
    router.post('/admin/founder-messages/reorder', {ids:rows.value.map(row => row.id)}, {preserveScroll:true, onError:()=>{rows.value=[...props.records];}, onFinish:()=>{saving.value=false;}});
}
function start(event, id) {
    if (saving.value) return;
    event.preventDefault();
    dragged.value = id;
    event.currentTarget.setPointerCapture(event.pointerId);
}
function finish(event) {
    const target = document.elementFromPoint(event.clientX, event.clientY)?.closest('[data-founder-id]');
    if (target) drop(Number(target.dataset.founderId));
    dragged.value = null;
}
function drop(target) {
    if (dragged.value === null || dragged.value === target || saving.value) return;
    const from = rows.value.findIndex(row=>row.id===dragged.value), to=rows.value.findIndex(row=>row.id===target);
    if(from<0 || to<0) return;
    const [row]=rows.value.splice(from,1); rows.value.splice(to,0,row); dragged.value=null; armed.value=null; persist();
}
function move(index, direction) {
    if (saving.value || index+direction<0 || index+direction>=rows.value.length) return;
    const [row]=rows.value.splice(index,1);rows.value.splice(index+direction,0,row);persist();
}
async function remove(record) {
    const answer=await Swal.fire({title:'Delete this founder message?',text:'This card will be removed from the website.',icon:'warning',showCancelButton:true,confirmButtonText:'Delete',confirmButtonColor:'#b42318'});
    if(answer.isConfirmed) router.delete('/admin/founder-messages/'+record.id,{preserveScroll:true});
}
</script>
<template>
    <Head title="Founder Message" /><AdminLayout title="Founder Message">
        <div class="page-heading"><div><span class="eyebrow">WEBSITE CONTENT</span><h1>Founder Message</h1><p>Drag the handle to arrange cards. Photos alternate left and right on the website.</p></div><Link href="/admin/founder-messages/create" class="btn primary"><Plus :size="17" />Add message</Link></div>
        <div class="panel founder-table-panel">
            <div class="table-intro"><span>{{ rows.length }} messages</span><a href="/founder-message" target="_blank" rel="noopener" class="btn">View page ↗</a></div>
            <div class="table-scroll"><table class="founder-table"><thead><tr><th>Order</th><th>Founder</th><th>Heading</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody><tr v-for="(record,index) in rows" :key="record.id" :data-founder-id="record.id" :class="{'is-dragging':dragged===record.id}">
                    <td><div class="order-cell"><button class="drag-handle" style="touch-action:none" :disabled="saving" :aria-label="'Drag '+record.founder_name" @pointerdown="start($event,record.id)" @pointerup="finish" @pointercancel="dragged=null"><GripVertical :size="18" /></button><span>{{ index+1 }}</span></div></td>
                    <td><div class="founder-cell"><img :src="record.image" :alt="record.founder_name" draggable="false" /><div><strong>{{ record.founder_name }}</strong><small>{{ record.designation || '—' }}</small></div></div></td>
                    <td>{{ record.title }}</td><td><span class="founder-status" :class="{'is-active':record.is_active}">{{ record.is_active ? 'Active' : 'Hidden' }}</span></td>
                    <td><div class="actions"><button class="icon-button" :disabled="saving || index===0" aria-label="Move up" @click="move(index,-1)"><ArrowUp :size="16" /></button><button class="icon-button" :disabled="saving || index===rows.length-1" aria-label="Move down" @click="move(index,1)"><ArrowDown :size="16" /></button><Link :href="'/admin/founder-messages/'+record.id+'/edit'" class="icon-button" :aria-label="'Edit '+record.founder_name"><Pencil :size="16" /></Link><button class="icon-button" :disabled="saving" :aria-label="'Delete '+record.founder_name" @click="remove(record)"><Trash2 :size="16" /></button></div></td>
                </tr><tr v-if="!rows.length"><td colspan="5" class="empty">No founder messages yet. Add the first message to get started.</td></tr></tbody>
            </table></div><p v-if="saving" role="status" class="order-saving">Saving order…</p>
        </div>
    </AdminLayout>
</template>
<style scoped>
.founder-table-panel{overflow:hidden}.table-intro{display:flex;align-items:center;justify-content:space-between;padding:18px 24px;border-bottom:1px solid #edf1ee;font-size:12px;color:#718078}.table-scroll{overflow:auto}.founder-table{width:100%;border-collapse:collapse;min-width:780px;font-size:12px}.founder-table th{text-align:left;padding:16px 22px;background:#f8faf7;color:#718078;font-size:10px;letter-spacing:.07em;text-transform:uppercase}.founder-table td{padding:18px 22px;border-top:1px solid #edf1ee;vertical-align:middle}.order-cell,.actions,.founder-cell{display:flex;align-items:center;gap:10px}.drag-handle{cursor:grab;color:#87998e;padding:6px}.drag-handle:active{cursor:grabbing}.founder-cell img{width:44px;height:54px;object-fit:cover;border-radius:8px}.founder-cell strong{font-weight:600}.founder-cell small{display:block;margin-top:5px;color:#829187}.founder-status{display:inline-flex;border-radius:20px;padding:5px 10px;background:#f0f1f1;color:#7a807d;font-size:10px}.founder-status.is-active{background:#e9f4ed;color:#286349}.actions{gap:7px}.actions button:disabled{opacity:.3;cursor:default}.is-dragging{opacity:.45;background:#f2f7f4}.empty{text-align:center;color:#839088;padding:42px!important}.order-saving{padding:12px 24px;color:#286349;font-size:12px}
</style>
