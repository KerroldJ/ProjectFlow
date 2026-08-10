<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowDown, ArrowUp, ChevronsUpDown, Pencil, Plus, Trash2 } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { create, destroy, edit, index as projectsIndex } from '@/routes/projects';
import type { Project, ProjectFilters } from '@/types';

const props = defineProps<{
    projects: Project[];
    filters: ProjectFilters;
    statuses: string[];
    priorities: string[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Projects', href: '/projects' }],
    },
});

const ALL = '__all__';

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? ALL);
const priority = ref(props.filters.priority ?? ALL);
const sort = ref(props.filters.sort ?? 'created_at');
const direction = ref(props.filters.direction ?? 'desc');

const applyFilters = () => {
    router.get(
        projectsIndex().url,
        {
            search: search.value || undefined,
            status: status.value === ALL ? undefined : status.value,
            priority: priority.value === ALL ? undefined : priority.value,
            sort: sort.value,
            direction: direction.value,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
});
watch([status, priority], applyFilters);

const sortBy = (column: string) => {
    if (sort.value === column) {
        direction.value = direction.value === 'asc' ? 'desc' : 'asc';
    } else {
        sort.value = column;
        direction.value = 'asc';
    }
    applyFilters();
};

const statusVariant = (value: string) => {
    switch (value) {
        case 'Completed':
            return 'default';
        case 'In Progress':
            return 'secondary';
        case 'On Hold':
            return 'destructive';
        default:
            return 'outline';
    }
};

const priorityVariant = (value: string) => {
    switch (value) {
        case 'High':
            return 'destructive';
        case 'Medium':
            return 'secondary';
        default:
            return 'outline';
    }
};

const formatDate = (value: string | null) => {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const columns = [
    { key: 'client_name', label: 'Client' },
    { key: 'project_name', label: 'Project' },
    { key: 'status', label: 'Status' },
    { key: 'priority', label: 'Priority' },
    { key: 'start_date', label: 'Start' },
    { key: 'due_date', label: 'Due' },
];

const projectToDelete = ref<Project | null>(null);
const deleting = ref(false);

const confirmDelete = () => {
    if (!projectToDelete.value) {
        return;
    }

    router.delete(destroy(projectToDelete.value.id).url, {
        preserveScroll: true,
        onStart: () => (deleting.value = true),
        onFinish: () => {
            deleting.value = false;
            projectToDelete.value = null;
        },
    });
};

const hasProjects = computed(() => props.projects.length > 0);
</script>

<template>
    <Head title="Projects" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div class="flex items-start justify-between gap-4">
            <Heading
                title="Projects"
                description="Track client projects, their status and priorities."
            />
            <Button as-child>
                <Link :href="create()">
                    <Plus />
                    New Project
                </Link>
            </Button>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <Input
                v-model="search"
                type="search"
                placeholder="Search client or project…"
                class="sm:max-w-xs"
            />

            <Select v-model="status">
                <SelectTrigger class="sm:w-44">
                    <SelectValue placeholder="Status" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem :value="ALL">All statuses</SelectItem>
                    <SelectItem v-for="s in statuses" :key="s" :value="s">
                        {{ s }}
                    </SelectItem>
                </SelectContent>
            </Select>

            <Select v-model="priority">
                <SelectTrigger class="sm:w-44">
                    <SelectValue placeholder="Priority" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem :value="ALL">All priorities</SelectItem>
                    <SelectItem v-for="p in priorities" :key="p" :value="p">
                        {{ p }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <div
            class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
        >
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-left">
                    <tr>
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            class="px-4 py-3 font-medium"
                        >
                            <button
                                type="button"
                                class="inline-flex items-center gap-1 hover:text-foreground"
                                @click="sortBy(column.key)"
                            >
                                {{ column.label }}
                                <ArrowUp
                                    v-if="sort === column.key && direction === 'asc'"
                                    class="size-3.5"
                                />
                                <ArrowDown
                                    v-else-if="sort === column.key && direction === 'desc'"
                                    class="size-3.5"
                                />
                                <ChevronsUpDown v-else class="size-3.5 opacity-40" />
                            </button>
                        </th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="project in projects"
                        :key="project.id"
                        class="border-t border-sidebar-border/70 dark:border-sidebar-border"
                    >
                        <td class="px-4 py-3 font-medium">{{ project.client_name }}</td>
                        <td class="px-4 py-3">
                            <div>{{ project.project_name }}</div>
                            <div
                                v-if="project.description"
                                class="line-clamp-1 text-xs text-muted-foreground"
                            >
                                {{ project.description }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <Badge :variant="statusVariant(project.status)">
                                {{ project.status }}
                            </Badge>
                        </td>
                        <td class="px-4 py-3">
                            <Badge :variant="priorityVariant(project.priority)">
                                {{ project.priority }}
                            </Badge>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-muted-foreground">
                            {{ formatDate(project.start_date) }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-muted-foreground">
                            {{ formatDate(project.due_date) }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <Button as-child variant="ghost" size="icon-sm">
                                    <Link :href="edit(project.id)" title="Edit">
                                        <Pencil />
                                    </Link>
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon-sm"
                                    title="Delete"
                                    @click="projectToDelete = project"
                                >
                                    <Trash2 class="text-destructive" />
                                </Button>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="!hasProjects">
                        <td
                            :colspan="columns.length + 1"
                            class="px-4 py-12 text-center text-muted-foreground"
                        >
                            No projects found. Try adjusting your filters or
                            <Link :href="create()" class="underline">create one</Link>.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <Dialog
        :open="projectToDelete !== null"
        @update:open="(value) => !value && (projectToDelete = null)"
    >
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Delete project</DialogTitle>
                <DialogDescription>
                    Are you sure you want to delete
                    <span class="font-medium">{{ projectToDelete?.project_name }}</span>?
                    This action cannot be undone.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <DialogClose as-child>
                    <Button variant="outline">Cancel</Button>
                </DialogClose>
                <Button
                    variant="destructive"
                    :disabled="deleting"
                    @click="confirmDelete"
                >
                    Delete
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
