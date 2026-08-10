<script setup lang="ts">
import { useHttp } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { toast } from 'vue-sonner';
import ProjectForm, { type ProjectFormModel } from '@/components/ProjectForm.vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { Project } from '@/types';

const props = defineProps<{
    open: boolean;
    project: Project | null;
    statuses: string[];
    priorities: string[];
}>();

const emit = defineEmits<{ 'update:open': [boolean]; saved: [] }>();

const isEdit = computed(() => props.project !== null);

const toDateInput = (value: string | null | undefined): string =>
    value ? value.slice(0, 10) : '';

const http = useHttp({
    client_name: '',
    project_name: '',
    description: '',
    status: '',
    priority: '',
    start_date: '',
    due_date: '',
});

watch(
    () => props.open,
    (open) => {
        if (!open) {
            return;
        }

        http.clearErrors();
        const project = props.project;

        http.client_name = project?.client_name ?? '';
        http.project_name = project?.project_name ?? '';
        http.description = project?.description ?? '';
        http.status = project?.status ?? props.statuses[0] ?? '';
        http.priority =
            project?.priority ?? props.priorities[1] ?? props.priorities[0] ?? '';
        http.start_date = toDateInput(project?.start_date);
        http.due_date = toDateInput(project?.due_date);
    },
);

const close = () => emit('update:open', false);

const submit = () => {
    const options = {
        onSuccess: () => {
            toast.success(isEdit.value ? 'Project updated.' : 'Project created.');
            emit('saved');
            close();
        },
    };

    if (props.project) {
        http.put(`/api/projects/${props.project.id}`, options);
    } else {
        http.post('/api/projects', options);
    }
};
</script>

<template>
    <Dialog :open="open" @update:open="(value) => emit('update:open', value)">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-2xl">
            <DialogHeader>
                <DialogTitle>{{ isEdit ? 'Edit Project' : 'New Project' }}</DialogTitle>
                <DialogDescription>
                    {{
                        isEdit
                            ? 'Update the details for this client project.'
                            : 'Add a new client project to the tracker.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <ProjectForm
                :form="(http as unknown as ProjectFormModel)"
                :statuses="statuses"
                :priorities="priorities"
                :submit-label="isEdit ? 'Save Changes' : 'Create Project'"
                @submit="submit"
                @cancel="close"
            />
        </DialogContent>
    </Dialog>
</template>
