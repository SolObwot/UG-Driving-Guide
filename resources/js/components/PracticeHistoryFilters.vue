<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

const props = defineProps<{
    signTypes: string[];
    signType: string;
    outcome: string;
    sort: string;
}>();

const emit = defineEmits<{
    change: [filters: { sign_type: string; outcome: string; sort: string }];
    reset: [];
}>();

const formatType = (type: string) =>
    type
        .split('_')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');

const emitChange = (next: { sign_type?: string; outcome?: string; sort?: string }) => {
    emit('change', {
        sign_type: next.sign_type ?? props.signType,
        outcome: next.outcome ?? props.outcome,
        sort: next.sort ?? props.sort,
    });
};
</script>

<template>
    <section class="rounded-xl border border-border bg-card p-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-xs font-semibold tracking-[0.08em] text-muted-foreground uppercase">Refine History</p>
            <Button variant="outline" size="sm" @click="emit('reset')">Reset</Button>
        </div>

        <div class="mt-3 grid gap-3 md:grid-cols-3">
            <div class="grid gap-1.5">
                <p class="text-xs text-muted-foreground">Sign type</p>
                <Select :model-value="props.signType" @update:model-value="(value) => emitChange({ sign_type: String(value) })">
                    <SelectTrigger>
                        <SelectValue placeholder="All types" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All types</SelectItem>
                        <SelectItem v-for="type in props.signTypes" :key="type" :value="type">
                            {{ formatType(type) }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="grid gap-1.5">
                <p class="text-xs text-muted-foreground">Outcome</p>
                <Select :model-value="props.outcome" @update:model-value="(value) => emitChange({ outcome: String(value) })">
                    <SelectTrigger>
                        <SelectValue placeholder="All outcomes" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All outcomes</SelectItem>
                        <SelectItem value="mistakes_only">Has mistakes</SelectItem>
                        <SelectItem value="perfect_only">Perfect sessions</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="grid gap-1.5">
                <p class="text-xs text-muted-foreground">Sort by</p>
                <Select :model-value="props.sort" @update:model-value="(value) => emitChange({ sort: String(value) })">
                    <SelectTrigger>
                        <SelectValue placeholder="Newest first" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="latest">Newest first</SelectItem>
                        <SelectItem value="score_desc">Highest score</SelectItem>
                        <SelectItem value="score_asc">Lowest score</SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>
    </section>
</template>
