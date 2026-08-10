<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Pencil } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { edit } from '@/routes/projects';
import type { Project } from '@/types';

const props = defineProps<{ project: Project }>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Projects', href: '/projects' },
            { title: 'Details', href: '#' },
        ],
    },
});

const formatDate = (value: string | null) =>
    value
        ? new Date(value).toLocaleDateString(undefined, {
              year: 'numeric',
              month: 'short',
              day: 'numeric',
          })
        : '—';

const details = [
    { label: 'Client Name', value: props.project.client_name },
    { label: 'Status', value: props.project.status },
    { label: 'Priority', value: props.project.priority },
    { label: 'Start Date', value: formatDate(props.project.start_date) },
    { label: 'Due Date', value: formatDate(props.project.due_date) },
];
</script>

<template>
    <Head :title="project.project_name" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div class="flex items-start justify-between gap-4">
            <Heading :title="project.project_name" :description="project.client_name" />
            <Button as-child>
                <Link :href="edit(project.id)">
                    <Pencil />
                    Edit
                </Link>
            </Button>
        </div>

        <Card class="max-w-2xl">
            <CardHeader>
                <CardTitle>Project Details</CardTitle>
            </CardHeader>
            <CardContent class="grid gap-4 sm:grid-cols-2">
                <div v-for="detail in details" :key="detail.label" class="grid gap-1">
                    <span class="text-xs text-muted-foreground">{{ detail.label }}</span>
                    <span class="text-sm font-medium">{{ detail.value }}</span>
                </div>
                <div class="grid gap-1 sm:col-span-2">
                    <span class="text-xs text-muted-foreground">Description</span>
                    <span class="text-sm">{{ project.description ?? '—' }}</span>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
