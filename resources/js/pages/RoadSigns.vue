<script setup lang="ts">
import { useForm, Head } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';
import DataTable from '@/components/DataTable.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { type BreadcrumbItem } from '@/types';
import { index as roadSigns } from '@/routes/road-signs';

type RoadSign = {
    id: number;
    name: string;
    type: string;
    description: string;
    image_path: string;
    is_active: boolean;
};

type Props = {
    roadSigns: RoadSign[];
    types: string[];
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Road Signs',
                href: roadSigns(),
            },
        ],
    },
});


const columns = [
    { key: 'name', label: 'Name' },
    { key: 'type', label: 'Type' },
    { key: 'description', label: 'Description' },
    { key: 'image_path', label: 'Image Path' },
    { key: 'is_active', label: 'Status' },
    {
        key: 'actions',
        label: 'Actions',
        class: 'text-right',
        headerClass: 'text-right',
    },
];

const isFormDialogOpen = ref(false);
const isDeleteDialogOpen = ref(false);
const selectedRoadSign = ref<RoadSign | null>(null);
const roadSignToDelete = ref<RoadSign | null>(null);

const form = useForm({
    name: '',
    type: props.types[0] ?? 'warning',
    description: '',
    image_path: '',
    is_active: true,
});

const deleteForm = useForm({});

const isEditing = computed(() => selectedRoadSign.value !== null);

const dialogTitle = computed(() =>
    isEditing.value ? 'Edit road sign' : 'Add road sign',
);

const dialogDescription = computed(() =>
    isEditing.value
        ? 'Update the selected road sign details.'
        : 'Create a new road sign entry for study content.',
);

const resetForm = () => {
    form.name = '';
    form.type = props.types[0] ?? 'warning';
    form.description = '';
    form.image_path = '';
    form.is_active = true;
    form.clearErrors();
};

const formatType = (type: string) =>
    type
        .split('_')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');

const openCreateDialog = () => {
    selectedRoadSign.value = null;
    resetForm();
    isFormDialogOpen.value = true;
};

const openEditDialog = (roadSign: RoadSign) => {
    selectedRoadSign.value = roadSign;
    form.name = roadSign.name;
    form.type = roadSign.type;
    form.description = roadSign.description;
    form.image_path = roadSign.image_path;
    form.is_active = roadSign.is_active;
    form.clearErrors();
    isFormDialogOpen.value = true;
};

const closeFormDialog = () => {
    isFormDialogOpen.value = false;
    form.clearErrors();
};

const submitForm = () => {
    if (isEditing.value && selectedRoadSign.value) {
        form.put(`/road-signs/${selectedRoadSign.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                isFormDialogOpen.value = false;
                selectedRoadSign.value = null;
            },
        });

        return;
    }

    form.post('/road-signs', {
        preserveScroll: true,
        onSuccess: () => {
            isFormDialogOpen.value = false;
            resetForm();
        },
    });
};

const openDeleteDialog = (roadSign: RoadSign) => {
    roadSignToDelete.value = roadSign;
    isDeleteDialogOpen.value = true;
};

const closeDeleteDialog = () => {
    isDeleteDialogOpen.value = false;
    roadSignToDelete.value = null;
};

const submitDelete = () => {
    if (!roadSignToDelete.value) {
        return;
    }

    deleteForm.delete(`/road-signs/${roadSignToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: closeDeleteDialog,
    });
};
</script>

<template>
    <Head title="Road Signs" />

    <div class="space-y-6 px-4 py-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading
                title="Road Signs"
                description="Manage road signs for Uganda driving theory study content"
            />

            <Button @click="openCreateDialog">
                <Plus class="h-4 w-4" />
                Add road sign
            </Button>
        </div>

        <DataTable
            :columns="columns"
            :rows="(props.roadSigns as unknown as Record<string, unknown>[])"
            empty-message="No road signs found. Add your first sign to begin."
        >
            <template #cell-type="{ value }">
                {{ formatType(String(value)) }}
            </template>

            <template #cell-is_active="{ value }">
                <span
                    :class="
                        value
                            ? 'inline-flex items-center rounded-md bg-green-100 px-2 py-0.5 text-[11px] font-medium text-green-700 ring-1 ring-green-200 ring-inset dark:bg-green-700/20 dark:text-green-100 dark:ring-green-500/30'
                            : 'inline-flex items-center rounded-md bg-muted px-2 py-0.5 text-[11px] font-medium text-muted-foreground ring-1 ring-border ring-inset'
                    "
                >
                    {{ value ? 'Active' : 'Inactive' }}
                </span>
            </template>

            <template #cell-actions="{ row }">
                <div class="flex justify-end gap-1">
                    <Button
                        variant="ghost"
                        size="sm"
                        @click="openEditDialog(row as RoadSign)"
                    >
                        <Pencil class="h-4 w-4" />
                        <span class="sr-only">Edit road sign</span>
                    </Button>

                    <Button
                        variant="ghost"
                        size="sm"
                        class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                        @click="openDeleteDialog(row as RoadSign)"
                    >
                        <Trash2 class="h-4 w-4" />
                        <span class="sr-only">Delete road sign</span>
                    </Button>
                </div>
            </template>
        </DataTable>

        <Dialog :open="isFormDialogOpen" @update:open="isFormDialogOpen = $event">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>{{ dialogTitle }}</DialogTitle>
                    <DialogDescription>
                        {{ dialogDescription }}
                    </DialogDescription>
                </DialogHeader>

                <form class="space-y-4" @submit.prevent="submitForm">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" v-model="form.name" placeholder="e.g. Stop" />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="type">Type</Label>
                        <Select v-model="form.type">
                            <SelectTrigger id="type" class="w-full">
                                <SelectValue placeholder="Select sign type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="type in props.types"
                                    :key="type"
                                    :value="type"
                                >
                                    {{ formatType(type) }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.type" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Description</Label>
                        <Input
                            id="description"
                            v-model="form.description"
                            placeholder="Short learning note"
                        />
                        <InputError :message="form.errors.description" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="image_path">Image Path</Label>
                        <Input
                            id="image_path"
                            v-model="form.image_path"
                            placeholder="images/road-signs/stop.png"
                        />
                        <InputError :message="form.errors.image_path" />
                    </div>

                    <div class="grid gap-2">
                        <div class="flex items-center gap-3">
                            <Checkbox id="is_active" v-model:checked="form.is_active" />
                            <Label for="is_active">Active sign</Label>
                        </div>
                        <InputError :message="form.errors.is_active" />
                    </div>

                    <DialogFooter class="gap-2">
                        <DialogClose as-child>
                            <Button variant="secondary" type="button" @click="closeFormDialog">
                                Cancel
                            </Button>
                        </DialogClose>

                        <Button type="submit" :disabled="form.processing">
                            {{ isEditing ? 'Save changes' : 'Add road sign' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog :open="isDeleteDialogOpen" @update:open="isDeleteDialogOpen = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete road sign</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete
                        "{{ roadSignToDelete?.name }}"? This action cannot be
                        undone.
                    </DialogDescription>
                </DialogHeader>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary" type="button" @click="closeDeleteDialog">
                            Cancel
                        </Button>
                    </DialogClose>

                    <Button
                        variant="destructive"
                        :disabled="deleteForm.processing"
                        @click="submitDelete"
                    >
                        Delete road sign
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
