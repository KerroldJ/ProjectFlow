<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';

export interface ProjectFormFields {
    client_name: string;
    project_name: string;
    description: string;
    status: string;
    priority: string;
    start_date: string;
    due_date: string;
}

defineProps<{
    form: InertiaForm<ProjectFormFields>;
    statuses: string[];
    priorities: string[];
    submitLabel: string;
    cancelHref: string;
}>();

const emit = defineEmits<{ submit: [] }>();
</script>

<template>
    <form class="flex flex-col gap-6" @submit.prevent="emit('submit')">
        <div class="grid gap-6 md:grid-cols-2">
            <div class="grid gap-2">
                <Label for="client_name">Client Name</Label>
                <Input
                    id="client_name"
                    v-model="form.client_name"
                    type="text"
                    autofocus
                    placeholder="Acme Corporation"
                />
                <InputError :message="form.errors.client_name" />
            </div>

            <div class="grid gap-2">
                <Label for="project_name">Project Name</Label>
                <Input
                    id="project_name"
                    v-model="form.project_name"
                    type="text"
                    placeholder="Corporate Website Redesign"
                />
                <InputError :message="form.errors.project_name" />
            </div>
        </div>

        <div class="grid gap-2">
            <Label for="description">Description</Label>
            <textarea
                id="description"
                v-model="form.description"
                rows="3"
                placeholder="Short summary of the project scope…"
                class="flex min-h-16 w-full rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
            />
            <InputError :message="form.errors.description" />
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div class="grid gap-2">
                <Label for="status">Status</Label>
                <Select v-model="form.status">
                    <SelectTrigger id="status" class="w-full">
                        <SelectValue placeholder="Select a status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="status in statuses"
                            :key="status"
                            :value="status"
                        >
                            {{ status }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.status" />
            </div>

            <div class="grid gap-2">
                <Label for="priority">Priority</Label>
                <Select v-model="form.priority">
                    <SelectTrigger id="priority" class="w-full">
                        <SelectValue placeholder="Select a priority" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="priority in priorities"
                            :key="priority"
                            :value="priority"
                        >
                            {{ priority }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.priority" />
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div class="grid gap-2">
                <Label for="start_date">Start Date</Label>
                <Input id="start_date" v-model="form.start_date" type="date" />
                <InputError :message="form.errors.start_date" />
            </div>

            <div class="grid gap-2">
                <Label for="due_date">Due Date</Label>
                <Input id="due_date" v-model="form.due_date" type="date" />
                <InputError :message="form.errors.due_date" />
            </div>
        </div>

        <div class="flex items-center gap-3">
            <Button type="submit" :disabled="form.processing">
                <Spinner v-if="form.processing" />
                {{ submitLabel }}
            </Button>
            <Button as-child variant="outline" type="button">
                <a :href="cancelHref">Cancel</a>
            </Button>
        </div>
    </form>
</template>
