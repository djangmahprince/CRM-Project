<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    notableType: {
        type: String,
        required: true,
    },
    notableId: {
        type: [Number, String],
        required: true,
    },
    notes: {
        type: Array,
        default: () => [],
    },
    attachments: {
        type: Array,
        default: () => [],
    },
});

const noteForm = useForm({
    notable_type: props.notableType,
    notable_id: props.notableId,
    title: '',
    body: '',
});

const attachmentForm = useForm({
    attachable_type: props.notableType,
    attachable_id: props.notableId,
    file: null,
});

function submitNote() {
    noteForm.post('/notes', {
        preserveScroll: true,
        onSuccess: () => noteForm.reset('title', 'body'),
    });
}

function submitAttachment() {
    attachmentForm.post('/attachments', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            attachmentForm.reset('file');
        },
    });
}
</script>

<template>
    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <section class="border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-950">Notes</h2>
            <form class="mt-4 space-y-3" @submit.prevent="submitNote">
                <input
                    v-model="noteForm.title"
                    type="text"
                    placeholder="Title (optional)"
                    class="block w-full border border-slate-300 px-3 py-2 text-sm outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-100"
                />
                <textarea
                    v-model="noteForm.body"
                    rows="3"
                    required
                    placeholder="Add a note"
                    class="block w-full border border-slate-300 px-3 py-2 text-sm outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-100"
                />
                <button type="submit" :disabled="noteForm.processing" class="bg-teal-700 px-3 py-2 text-sm font-semibold text-white hover:bg-teal-800 disabled:opacity-60">
                    Save note
                </button>
            </form>
            <ul v-if="notes.length" class="mt-4 divide-y divide-slate-100 text-sm">
                <li v-for="note in notes" :key="note.id" class="py-3">
                    <p class="font-medium text-slate-900">{{ note.title || 'Note' }}</p>
                    <p class="mt-1 text-slate-600">{{ note.body }}</p>
                </li>
            </ul>
            <p v-else class="mt-4 text-sm text-slate-500">No notes yet.</p>
        </section>

        <section class="border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-950">Attachments</h2>
            <form class="mt-4 space-y-3" @submit.prevent="submitAttachment">
                <input
                    type="file"
                    class="block w-full text-sm"
                    @change="attachmentForm.file = $event.target.files[0]"
                />
                <button type="submit" :disabled="attachmentForm.processing || !attachmentForm.file" class="bg-teal-700 px-3 py-2 text-sm font-semibold text-white hover:bg-teal-800 disabled:opacity-60">
                    Upload
                </button>
            </form>
            <ul v-if="attachments.length" class="mt-4 divide-y divide-slate-100 text-sm">
                <li v-for="attachment in attachments" :key="attachment.id" class="flex items-center justify-between gap-3 py-3">
                    <span class="text-slate-800">{{ attachment.original_name }}</span>
                    <span class="flex gap-3">
                        <a
                            :href="`/attachments/${attachment.id}/preview`"
                            target="_blank"
                            rel="noopener"
                            class="font-medium text-teal-700 hover:underline"
                        >
                            Preview
                        </a>
                        <a
                            :href="`/attachments/${attachment.id}/download`"
                            class="font-medium text-teal-700 hover:underline"
                        >
                            Download
                        </a>
                    </span>
                </li>
            </ul>
            <p v-else class="mt-4 text-sm text-slate-500">No attachments yet.</p>
        </section>
    </div>
</template>
