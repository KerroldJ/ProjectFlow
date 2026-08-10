<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import ProjectForm from '@/components/ProjectForm.vue';
import { index as projectsIndex, update } from '@/routes/projects';
import type { Project } from '@/types';

const props = defineProps<{
    project: Project;
    statuses: string[];
    priorities: string[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Projects', href: '/projects' },
            { title: 'Edit Project', href: '#' },
        ],
    },
});

const toDateInput = (value: string | null): string => (value ? value.slice(0, 10) : '');

const form = useForm({
    client_name: props.project.client_name,
    project_name: props.project.project_name,
    description: props.project.description ?? '',
    status: props.project.status,
    priority: props.project.priority,
    start_date: toDateInput(props.project.start_date),
    due_date: toDateInput(props.project.due_date),
});

const submit = () => {
    form.submit(update(props.project.id));
};
</script>

<template>
    <Head title="Edit Project" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <Heading
            title="Edit Project"
            :description="`Update the details for ${project.project_name}.`"
        />

        <div class="max-w-3xl">
            <ProjectForm
                :form="form"
                :statuses="statuses"
                :priorities="priorities"
                submit-label="Save Changes"
                :cancel-href="projectsIndex().url"
                @submit="submit"
            />
        </div>
    </div>
</template>
