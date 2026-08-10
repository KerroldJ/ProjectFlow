<script setup lang="ts">
import { Head, useHttp } from '@inertiajs/vue3';
import {
    ArrowDown,
    ArrowUp,
    ChevronLeft,
    ChevronRight,
    ChevronsUpDown,
    Pencil,
    Plus,
    Trash2,
} from '@lucide/vue';
import { computed, onMounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import Heading from '@/components/Heading.vue';
import ProjectFormDialog from '@/components/ProjectFormDialog.vue';
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
import { Skeleton } from '@/components/ui/skeleton';
import type { Project } from '@/types';

defineProps<{
    statuses: string[];
    priorities: string[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Projects', href: '/projects' }],
    },
});

const ALL = '__all__';

const projects = ref<Project[]>([]);
const loading = ref(true);

const search = ref('');
const status = ref(ALL);
const priority = ref(ALL);
const sort = ref('created_at');
const direction = ref('desc');

const page = ref(1);
const perPage = ref(10);
const meta = ref({
    current_page: 1,
    last_page: 1,
    per_page: perPage.value,
    total: 0,
    from: 0,
    to: 0,
});

const fetchProjects = async () => {
    loading.value = true;

    const params = new URLSearchParams();
    if (search.value) params.set('search', search.value);
    if (status.value !== ALL) params.set('status', status.value);
    if (priority.value !== ALL) params.set('priority', priority.value);
    params.set('sort', sort.value);
    params.set('direction', direction.value);
    params.set('page', String(page.value));
    params.set('per_page', String(perPage.value));

    try {
        const response = await fetch(`/api/projects?${params.toString()}`, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });
        const payload = await response.json();
        projects.value = payload.data ?? [];
        if (payload.meta) {
            meta.value = payload.meta;
        }

        // If the current page no longer exists (e.g. after a delete), step back.
        if (page.value > meta.value.last_page && meta.value.last_page >= 1) {
            page.value = meta.value.last_page;
            return fetchProjects();
        }
    } finally {
        loading.value = false;
    }
};

onMounted(fetchProjects);

/** Reset to the first page and refetch (used for any filter/sort change). */
const applyFilters = () => {
    page.value = 1;
    fetchProjects();
};

// Server-side filtering — refetch immediately when a filter changes.
watch([status, priority], applyFilters);

// Search is server-side too, but only runs when the user presses Enter.
const submitSearch = () => applyFilters();

const goToPage = (target: number) => {
    if (target < 1 || target > meta.value.last_page || target === page.value) {
        return;
    }

    page.value = target;
    fetchProjects();
};

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
const deleteHttp = useHttp({});

const confirmDelete = () => {
    if (!projectToDelete.value) {
        return;
    }

    deleteHttp.delete(`/api/projects/${projectToDelete.value.id}`, {
        onSuccess: () => {
            toast.success('Project deleted.');
            projectToDelete.value = null;
            fetchProjects();
        },
    });
};

const hasProjects = computed(() => projects.value.length > 0);

const formDialogOpen = ref(false);
const editingProject = ref<Project | null>(null);

const openCreateDialog = () => {
    editingProject.value = null;
    formDialogOpen.value = true;
};

const openEditDialog = (project: Project) => {
    editingProject.value = project;
    formDialogOpen.value = true;
};
</script>

<template>
    <Head title="Projects" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div class="flex items-start justify-between gap-4">
            <Heading
                title="Projects"
                description="Track client projects, their status and priorities."
            />
            <Button @click="openCreateDialog">
                <Plus />
                New Project
            </Button>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <Input
                v-model="search"
                type="search"
                placeholder="Search client or project… (press Enter)"
                class="sm:max-w-xs"
                @keyup.enter="submitSearch"
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
                    <template v-if="loading">
                        <tr
                            v-for="n in 5"
                            :key="`skeleton-${n}`"
                            class="border-t border-sidebar-border/70 dark:border-sidebar-border"
                        >
                            <td
                                v-for="i in columns.length + 1"
                                :key="i"
                                class="px-4 py-3"
                            >
                                <Skeleton class="h-5 w-full" />
                            </td>
                        </tr>
                    </template>

                    <tr
                        v-for="project in projects"
                        v-else
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
                                <Button
                                    variant="ghost"
                                    size="icon-sm"
                                    title="Edit"
                                    @click="openEditDialog(project)"
                                >
                                    <Pencil />
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

                    <tr v-if="!loading && !hasProjects">
                        <td
                            :colspan="columns.length + 1"
                            class="px-4 py-12 text-center text-muted-foreground"
                        >
                            No projects found. Try adjusting your filters or
                            <button
                                type="button"
                                class="underline"
                                @click="openCreateDialog"
                            >
                                create one
                            </button>.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-if="!loading && meta.total > 0"
            class="flex flex-col items-center justify-between gap-3 sm:flex-row"
        >
            <p class="text-sm text-muted-foreground">
                Showing {{ meta.from ?? 0 }}–{{ meta.to ?? 0 }} of
                {{ meta.total }} project{{ meta.total === 1 ? '' : 's' }}
            </p>

            <div class="flex items-center gap-2">
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="meta.current_page <= 1"
                    @click="goToPage(meta.current_page - 1)"
                >
                    <ChevronLeft />
                    Previous
                </Button>
                <span class="text-sm text-muted-foreground">
                    Page {{ meta.current_page }} of {{ meta.last_page }}
                </span>
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="meta.current_page >= meta.last_page"
                    @click="goToPage(meta.current_page + 1)"
                >
                    Next
                    <ChevronRight />
                </Button>
            </div>
        </div>
    </div>

    <ProjectFormDialog
        v-model:open="formDialogOpen"
        :project="editingProject"
        :statuses="statuses"
        :priorities="priorities"
        @saved="fetchProjects"
    />

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
                    :disabled="deleteHttp.processing"
                    @click="confirmDelete"
                >
                    Delete
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
