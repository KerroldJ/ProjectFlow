<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import ProjectForm from '@/components/ProjectForm.vue';
import Heading from '@/components/Heading.vue';
import { index as projectsIndex, store } from '@/routes/projects';

const props = defineProps<{
    statuses: string[];
    priorities: string[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Projects', href: '/projects' },
            { title: 'New Project', href: '/projects/create' },
        ],
    },
});

const form = useForm({
    client_name: '',
    project_name: '',
    description: '',
    status: props.statuses[0] ?? '',
    priority: props.priorities[1] ?? props.priorities[0] ?? '',
    start_date: '',
    due_date: '',
});

const submit = () => {
    form.submit(store());
};
</script>

<template>
    <Head title="New Project" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <Heading
            title="New Project"
            description="Add a new client project to the tracker."
        />

        <div class="max-w-3xl">
            <ProjectForm
                :form="form"
                :statuses="statuses"
                :priorities="priorities"
                submit-label="Create Project"
                :cancel-href="projectsIndex().url"
                @submit="submit"
            />
        </div>
    </div>
</template>
