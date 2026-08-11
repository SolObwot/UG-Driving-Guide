<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

type DataTableColumn = {
    key: string;
    label: string;
    class?: HTMLAttributes['class'];
    headerClass?: HTMLAttributes['class'];
};

const props = withDefaults(
    defineProps<{
        columns: DataTableColumn[];
        rows: Record<string, unknown>[];
        rowKey?: string;
        emptyMessage?: string;
    }>(),
    {
        rowKey: 'id',
        emptyMessage: 'No records found.',
    },
);

const resolveRowKey = (row: Record<string, unknown>, index: number) => {
    const key = row[props.rowKey];

    return typeof key === 'string' || typeof key === 'number'
        ? key
        : `${props.rowKey}-${index}`;
};
</script>

<template>
    <div class="overflow-x-auto rounded-xl border border-border">
        <table class="w-full min-w-full border-collapse text-sm">
            <thead class="bg-muted/40">
                <tr>
                    <th
                        v-for="column in columns"
                        :key="column.key"
                        :class="
                            cn(
                                'px-4 py-3 text-left text-xs font-semibold tracking-[0.04em] text-muted-foreground uppercase',
                                column.headerClass,
                            )
                        "
                    >
                        {{ column.label }}
                    </th>
                </tr>
            </thead>

            <tbody>
                <tr
                    v-for="(row, rowIndex) in rows"
                    :key="resolveRowKey(row, rowIndex)"
                    class="border-t border-border transition-colors hover:bg-muted/20"
                >
                    <td
                        v-for="column in columns"
                        :key="`${resolveRowKey(row, rowIndex)}-${column.key}`"
                        :class="cn('px-4 py-3 align-middle text-muted-foreground', column.class)"
                    >
                        <slot
                            :name="`cell-${column.key}`"
                            :row="row"
                            :value="row[column.key]"
                        >
                            {{ row[column.key] }}
                        </slot>
                    </td>
                </tr>

                <tr v-if="!rows.length">
                    <td
                        :colspan="columns.length"
                        class="px-4 py-8 text-center text-sm text-muted-foreground"
                    >
                        {{ emptyMessage }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>