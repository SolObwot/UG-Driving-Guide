<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Eye, Pencil, Plus, Search, Trash2 } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import DataTable from '@/components/DataTable.vue';
import Heading from '@/components/Heading.vue';
import ImageDropzone from '@/components/ImageDropzone.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, } from '@/components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue, } from '@/components/ui/select';
import { index as roadSigns } from '@/routes/road-signs';

type RoadSign = {
    id: number;
    name: string;
    type: string;
    description: string;
    image_path: string;
    is_active: boolean;
};

type PaginationMeta = {
    data: RoadSign[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
};

type Props = {
    roadSigns: PaginationMeta;
    types: string[];
    filters: {
        search: string;
        type: string;
        per_page: number;
    };
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
    {
        key: 'actions',
        label: 'Actions',
        class: 'text-right',
        headerClass: 'text-right',
    },
];

const isFormDialogOpen = ref(false);
const isDeleteDialogOpen = ref(false);
const isViewDialogOpen = ref(false);
const selectedRoadSign = ref<RoadSign | null>(null);
const roadSignToDelete = ref<RoadSign | null>(null);
const imageToView = ref<string>('');
const searchQuery = ref(props.filters.search ?? '');
const typeFilter = ref(props.filters.type ?? 'all');
const perPage = ref(props.filters.per_page ?? 10);

const rows = computed(() => props.roadSigns?.data ?? []);
const totalRecords = computed(() => Number(props.roadSigns?.total ?? 0));
const currentPage = computed(() => Number(props.roadSigns?.current_page ?? 1));
const lastPage = computed(() => Number(props.roadSigns?.last_page ?? 1));
const perPageValue = computed(() => Number(props.roadSigns?.per_page ?? perPage.value));
const rangeStart = computed(() => totalRecords.value === 0 ? 0 : ((currentPage.value - 1) * perPageValue.value) + 1);
const rangeEnd = computed(() => Math.min(currentPage.value * perPageValue.value, totalRecords.value));

const pageNumbers = computed(() => {
    const pages = new Set<number>();
    pages.add(1);
    pages.add(lastPage.value);
    pages.add(currentPage.value);
    pages.add(Math.max(1, currentPage.value - 1));
    pages.add(Math.min(lastPage.value, currentPage.value + 1));

    for (let page = Math.max(1, currentPage.value - 2); page <= Math.min(lastPage.value, currentPage.value + 2); page++) {
        pages.add(page);
    }

    const sorted = [...pages].filter((page) => page >= 1 && page <= lastPage.value).sort((a, b) => a - b);

    const output: Array<number | 'ellipsis'> = [];
    let previous: number | null = null;

    sorted.forEach((page) => {
        if (previous !== null && page - previous > 1) {
            output.push('ellipsis');
        }

        output.push(page);
        previous = page;
    });

    return output;
});

const form = useForm({
    name: '',
    type: props.types[0] ?? 'warning',
    description: '',
    image_path: '',
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

const openViewDialog = (roadSign: RoadSign) => {
    imageToView.value = roadSign.image_path;
    isViewDialogOpen.value = true;
};

const applyFilters = () => {
    router.get('/road-signs', {
        search: searchQuery.value.trim() || undefined,
        type: typeFilter.value && typeFilter.value !== 'all' ? typeFilter.value : undefined,
        per_page: perPage.value || 10,
        page: 1,
    }, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};

const changePage = (page: number) => {
    if (page < 1 || page > lastPage.value || page === currentPage.value) {
        return;
    }

    router.get('/road-signs', {
        search: searchQuery.value.trim() || undefined,
        type: typeFilter.value && typeFilter.value !== 'all' ? typeFilter.value : undefined,
        per_page: perPage.value || 10,
        page,
    }, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};

watch(searchQuery, (value) => {
    if (value.trim() === '') {
        applyFilters();
    }
});
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

            <div class="rounded-xl border border-border bg-card p-4">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div class="flex flex-1 flex-col gap-3 md:flex-row md:items-center">
                        <div class="relative max-w-sm flex-1">
                            <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                v-model="searchQuery"
                                placeholder="Search by name or description"
                                class="pl-9"
                                @keydown.enter="applyFilters"
                            />
                        </div>

                        <div class="w-full md:w-52">
                            <Select v-model="typeFilter" @update:model-value="applyFilters">
                                <SelectTrigger>
                                    <SelectValue placeholder="All types" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All types</SelectItem>
                                    <SelectItem v-for="type in props.types" :key="type" :value="type">
                                        {{ formatType(type) }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <Label for="per-page" class="text-sm text-muted-foreground">Rows</Label>
                        <Select v-model="perPage" @update:model-value="applyFilters">
                            <SelectTrigger id="per-page" class="w-24">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="10">10</SelectItem>
                                <SelectItem :value="25">25</SelectItem>
                                <SelectItem :value="50">50</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
            </div>

            <DataTable
                :columns="columns"
                :rows="rows as unknown as Record<string, unknown>[]"
                empty-message="No road signs found. Add your first sign to begin."
            >
                <template #cell-type="{ value }">
                    {{ formatType(String(value)) }}
                </template>

                <template #cell-actions="{ row }">
                    <div class="flex justify-end gap-1">
                        <Button variant="ghost" size="sm" @click="openViewDialog(row as RoadSign)">
                            <Eye class="h-4 w-4" />
                            <span class="sr-only">View road sign</span>
                        </Button>

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

            <div v-if="totalRecords > 0" class="flex flex-col gap-3 rounded-xl border border-border bg-card px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-muted-foreground">
                    Showing {{ rangeStart }}-{{ rangeEnd }} of {{ totalRecords }}
                </p>

                <nav class="flex flex-wrap items-center gap-1" aria-label="Pagination">
                    <Button variant="outline" size="sm" :disabled="currentPage <= 1" @click="changePage(currentPage - 1)">
                        Previous
                    </Button>

                    <template v-for="page in pageNumbers" :key="`${page}-page`">
                        <span v-if="page === 'ellipsis'" class="px-2 text-sm text-muted-foreground">...</span>
                        <Button
                            v-else
                            :variant="page === currentPage ? 'default' : 'outline'"
                            size="sm"
                            @click="changePage(Number(page))"
                        >
                            {{ page }}
                        </Button>
                    </template>

                    <Button variant="outline" size="sm" :disabled="currentPage >= lastPage" @click="changePage(currentPage + 1)">
                        Next
                    </Button>
                </nav>
            </div>

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
                            <Label>Image</Label>
                            <ImageDropzone v-model="form.image_path" />
                            <InputError :message="form.errors.image_path" />
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
                            Are you sure you want to delete "{{ roadSignToDelete?.name }}"? This action will mark it inactive and remove it from the list.
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

            <Dialog :open="isViewDialogOpen" @update:open="isViewDialogOpen = $event">
                <DialogContent class="sm:max-w-xl">
                    <DialogHeader>
                        <DialogTitle>Road sign image</DialogTitle>
                    </DialogHeader>

                    <div v-if="imageToView" class="flex min-h-64 items-center justify-center rounded-lg border border-border bg-muted/20 p-4">
                        <img :src="imageToView" alt="Road sign preview" class="max-h-[420px] w-auto rounded-md object-contain" />
                    </div>
                </DialogContent>
            </Dialog>
        </div>
</template>
