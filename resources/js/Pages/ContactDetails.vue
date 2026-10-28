<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ArrowLeft, Pencil, Phone, Trash2 } from "@lucide/vue";
import Swal from "sweetalert2";
import AdminLayout from "../Components/AdminLayout.vue";

defineProps({ contact: Object });

async function remove(contact) {
    const answer = await Swal.fire({
        title: "Delete this message?",
        text: `${contact.name}'s message will be permanently removed.`,
        icon: "warning",
        showCancelButton: true,
        showCloseButton: true,
        confirmButtonText: "Yes, delete",
        cancelButtonText: "No, keep it",
        allowOutsideClick: false,
        allowEscapeKey: false,
        confirmButtonColor: "#b42318",
    });
    if (answer.isConfirmed) router.delete("/admin/contacts/" + contact.id);
}
</script>

<template>
    <Head :title="'Message from ' + contact.name" />
    <AdminLayout title="Contacts">
        <Link href="/admin/contacts" class="back-link">
            <ArrowLeft :size="16" />All messages
        </Link>
        <div class="page-heading">
            <div>
                <span class="eyebrow">CUSTOMER MESSAGE</span>
                <h1>{{ contact.subject }}</h1>
                <p>
                    Received {{ new Date(contact.created_at).toLocaleString() }}
                </p>
            </div>
            <div class="message-actions">
                <Link
                    :href="`/admin/contacts/${contact.id}/edit`"
                    class="btn secondary"
                >
                    <Pencil :size="16" />Edit message
                </Link>
                <button class="btn danger-button" @click="remove(contact)">
                    <Trash2 :size="17" />Delete message
                </button>
            </div>
        </div>
        <article class="panel message-card">
            <div class="message-meta">
                <div class="sender-avatar">
                    {{ contact.name.slice(0, 1).toUpperCase() }}
                </div>
                <div class="sender-details">
                    <h2>{{ contact.name }}</h2>
                    <a :href="'tel:' + contact.phone_number">
                        <Phone :size="15" />{{ contact.phone_number }}
                    </a>
                </div>
                <a
                    class="btn secondary reply-link"
                    :href="'tel:' + contact.phone_number"
                >
                    <Phone :size="16" />Call customer
                </a>
            </div>
            <div class="message-body">
                <span class="eyebrow">MESSAGE</span>
                <p>{{ contact.description }}</p>
            </div>
        </article>
    </AdminLayout>
</template>

<style scoped>
.danger-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: 0;
    border-radius: 10px;
    padding: 12px 16px;
    background: #b42318;
    color: white;
    font-weight: 600;
    cursor: pointer;
}
.message-actions {
    display: flex;
    align-items: center;
    gap: 9px;
}
.message-card {
    overflow: hidden;
}
.message-meta {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 25px 28px;
    border-bottom: 1px solid #edf0e9;
}
.sender-avatar {
    display: grid;
    width: 50px;
    height: 50px;
    flex: 0 0 50px;
    place-items: center;
    border-radius: 50%;
    background: #edf3e7;
    color: #627b54;
    font-size: 20px;
    font-weight: 600;
}
.sender-details {
    min-width: 0;
    flex: 1;
}
.sender-details h2 {
    margin-bottom: 5px;
    color: #334c39;
    font-size: 16px;
    font-weight: 600;
}
.sender-details a {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: #718078;
    font-size: 13px;
}
.reply-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}
.message-body {
    padding: 30px 32px 36px;
}
.message-body p {
    margin-top: 14px;
    color: #4c5d51;
    font-size: 14px;
    line-height: 1.9;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
}
@media (max-width: 600px) {
    .message-meta {
        flex-wrap: wrap;
        padding: 20px;
    }
    .reply-link {
        margin-left: 66px;
    }
    .message-body {
        padding: 24px 20px 28px;
    }
    .danger-button {
        padding: 10px 12px;
        font-size: 12px;
    }
    .message-actions {
        flex-wrap: wrap;
    }
}
</style>
