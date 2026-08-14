<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import PracticeHistoryFilters from '@/components/PracticeHistoryFilters.vue';
import PracticeHistorySessionCard from '@/components/PracticeHistorySessionCard.vue';
import { history as practiceHistory, index as practice } from '@/routes/practice';

type SessionQuestion = {
    order: number;
    shown_sign: {
        id: number;
        name: string;
        image_path: string;
        type: string;
    };
    selected_sign: {
        id: number;
        name: string;
    } | null;
    correct_sign: {
        id: number;
        name: string;
    };
    is_correct: boolean;
    time_taken_seconds: number | null;
};

type SessionItem = {
    id: number;
    sign_type: string | null;
    score_percent: number;
    total_questions: number;
    correct_count: number;
    wrong_count: number;
    completed_at: string | null;
    questions: SessionQuestion[];
};

type SessionsPayload = {
    data: SessionItem[];
    current_page: number;
    last_page: number;
    total: number;
};

type Props = {
    sessions: SessionsPayload;
    availableSignTypes: string[];
    filters: {
        sign_type: string;
        outcome: string;
        sort: string;
    };
    summary: {
        total: number;
        average_score: number;
        perfect_sessions: number;
    };
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Practice History',
                href: practiceHistory(),
            },
        ],
    },
});

const pageNumbers = computed(() => {
    const pages = new Set<number>();

    pages.add(1);
    pages.add(props.sessions.last_page);
    pages.add(props.sessions.current_page);
    pages.add(Math.max(1, props.sessions.current_page - 1));
    pages.add(Math.min(props.sessions.last_page, props.sessions.current_page + 1));

    for (
        let page = Math.max(1, props.sessions.current_page - 2);
        page <= Math.min(props.sessions.last_page, props.sessions.current_page + 2);
        page++
    ) {
        pages.add(page);
    }

    const sorted = [...pages]
        .filter((page) => page >= 1 && page <= props.sessions.last_page)
        .sort((a, b) => a - b);

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

const applyFilters = (next: { sign_type: string; outcome: string; sort: string }) => {
    router.get(practiceHistory().url, {
        sign_type: next.sign_type,
        outcome: next.outcome,
        sort: next.sort,
        page: 1,
    }, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    router.get(practiceHistory().url, {
        sign_type: 'all',
        outcome: 'all',
        sort: 'latest',
        page: 1,
    }, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};

const goToPage = (page: number) => {
    if (page < 1 || page > props.sessions.last_page || page === props.sessions.current_page) {
        return;
    }

    router.get(practiceHistory().url, {
        sign_type: props.filters.sign_type,
        outcome: props.filters.outcome,
        sort: props.filters.sort,
        page,
    }, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};
</script>

<template>
    <Head title="Practice History" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-5 md:p-8">
        <section class="rounded-xl border border-primary bg-primary p-6 md:p-8">
            <p class="text-xs font-semibold tracking-[0.12em] text-primary-foreground/80">
                REVIEW MODE
            </p>
            <h1 class="mt-2 text-3xl leading-tight font-bold tracking-[-0.03em] text-primary-foreground md:text-4xl">
                Review past attempts without getting lost in long history.
            </h1>
            <p class="mt-3 max-w-3xl text-sm leading-relaxed text-primary-foreground">
                Filter sessions by category or performance, then open only the tests you
                want to analyze in detail.
            </p>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <article class="rounded-xl border border-border bg-card p-4">
                <p class="text-xs tracking-[0.08em] text-muted-foreground uppercase">Filtered Sessions</p>
                <p class="mt-1 text-2xl font-bold tracking-tight">{{ props.summary.total }}</p>
            </article>
            <article class="rounded-xl border border-border bg-card p-4">
                <p class="text-xs tracking-[0.08em] text-muted-foreground uppercase">Average Score</p>
                <p class="mt-1 text-2xl font-bold tracking-tight text-primary">{{ props.summary.average_score }}%</p>
            </article>
            <article class="rounded-xl border border-border bg-card p-4">
                <p class="text-xs tracking-[0.08em] text-muted-foreground uppercase">Perfect Sessions</p>
                <p class="mt-1 text-2xl font-bold tracking-tight">{{ props.summary.perfect_sessions }}</p>
            </article>
        </section>

        <PracticeHistoryFilters
            :sign-types="props.availableSignTypes"
            :sign-type="props.filters.sign_type"
            :outcome="props.filters.outcome"
            :sort="props.filters.sort"
            @change="applyFilters"
            @reset="resetFilters"
        />

        <section class="flex items-center justify-between gap-3 rounded-xl border border-border bg-card p-4">
            <p class="text-sm text-muted-foreground">
                Page {{ props.sessions.current_page }} of {{ props.sessions.last_page }}
            </p>

            <Link :href="practice()" class="text-sm font-semibold text-primary hover:underline">
                Back to practice
            </Link>
        </section>

        <section class="space-y-3">
            <PracticeHistorySessionCard
                v-for="session in props.sessions.data"
                :key="session.id"
                :session="session"
            />

            <article v-if="props.sessions.data.length === 0" class="rounded-xl border border-border bg-card p-6 text-sm text-muted-foreground">
                No completed practice sessions yet.
            </article>
        </section>

        <section v-if="props.sessions.last_page > 1" class="flex flex-wrap items-center gap-1">
            <Button variant="outline" size="sm" :disabled="props.sessions.current_page <= 1" @click="goToPage(props.sessions.current_page - 1)">
                Previous
            </Button>

            <template v-for="page in pageNumbers" :key="`page-${page}`">
                <span v-if="page === 'ellipsis'" class="px-2 text-sm text-muted-foreground">...</span>
                <Button
                    v-else
                    :variant="page === props.sessions.current_page ? 'default' : 'outline'"
                    size="sm"
                    @click="goToPage(Number(page))"
                >
                    {{ page }}
                </Button>
            </template>

            <Button variant="outline" size="sm" :disabled="props.sessions.current_page >= props.sessions.last_page" @click="goToPage(props.sessions.current_page + 1)">
                Next
            </Button>
        </section>
    </div>
</template>
