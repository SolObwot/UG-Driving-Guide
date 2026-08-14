<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { dashboard } from '@/routes';
import { index as practice } from '@/routes/practice';

type SignOption = {
    id: number;
    name: string;
};

type QuestionPayload = {
    index: number;
    total: number;
    sign: {
        id: number;
        name: string;
        type: string;
        description: string;
        image_path: string;
    };
    options: SignOption[];
};

type ActiveSessionPayload = {
    id: number;
    sign_type: string | null;
    total_questions: number;
    answered_count: number;
};

type ProgressItem = {
    sign_type: string;
    accuracy_percent: number;
    signs_attempted: number;
    signs_correct: number;
    mastery_level: string;
    last_practiced_at: string | null;
};

type SessionHistoryItem = {
    id: number;
    sign_type: string | null;
    total_questions: number;
    correct_count: number;
    wrong_count: number;
    score_percent: number;
    completed_at: string | null;
};

type Props = {
    signTypes: string[];
    activeSession: ActiveSessionPayload | null;
    question: QuestionPayload | null;
    progressByType: ProgressItem[];
    recentSessions: SessionHistoryItem[];
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Sign Practice',
                href: practice(),
            },
        ],
    },
});

const questionCountChoices = [5, 10, 15, 20];

const startForm = useForm({
    sign_type: 'all',
    total_questions: 10,
});

const answerForm = useForm({
    road_sign_id: 0,
    selected_road_sign_id: 0,
    time_taken_seconds: 0,
});

const questionStartedAt = ref(Date.now());
const selectedOptionId = ref<number | null>(null);

watch(
    () => props.question?.sign.id,
    () => {
        questionStartedAt.value = Date.now();
        selectedOptionId.value = null;
    },
    { immediate: true },
);

const currentQuestionNumber = computed(() => (props.question?.index ?? 0) + 1);
const questionProgressPercent = computed(() => {
    if (!props.question || props.question.total <= 0) {
        return 0;
    }

    return Math.min(100, Math.round((currentQuestionNumber.value / props.question.total) * 100));
});

const hasActiveQuestion = computed(() => Boolean(props.activeSession && props.question));

const formatType = (type: string | null) => {
    if (!type) {
        return 'Mixed';
    }

    return type
        .split('_')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
};

const submitStart = () => {
    startForm.post('/practice/sessions', {
        preserveScroll: true,
    });
};

const submitAnswer = (option: SignOption) => {
    if (!props.activeSession || !props.question || answerForm.processing) {
        return;
    }

    selectedOptionId.value = option.id;

    answerForm.road_sign_id = props.question.sign.id;
    answerForm.selected_road_sign_id = option.id;
    answerForm.time_taken_seconds = Math.max(1, Math.round((Date.now() - questionStartedAt.value) / 1000));

    answerForm.post(`/practice/sessions/${props.activeSession.id}/answers`, {
        preserveScroll: true,
        onFinish: () => {
            answerForm.reset('road_sign_id', 'selected_road_sign_id', 'time_taken_seconds');
        },
    });
};
</script>

<template>
    <Head title="Sign Practice" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-5 md:p-8">
        <section class="rounded-xl border border-primary bg-primary p-6 md:p-8">
            <p class="text-xs font-semibold tracking-[0.12em] text-primary-foreground/80">
                PRACTICE MODE
            </p>
            <h1 class="mt-2 text-3xl leading-tight font-bold tracking-[-0.03em] text-primary-foreground md:text-4xl">
                Train road sign recognition with image-based multiple choice.
            </h1>
            <p class="mt-3 max-w-3xl text-sm leading-relaxed text-primary-foreground">
                Start a focused category or mixed practice session. Your score and
                mastery level update automatically after each completed session.
            </p>
        </section>

        <section class="grid gap-4 lg:grid-cols-[1.35fr_0.65fr]">
            <article class="rounded-xl border border-border bg-card p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-xl font-bold tracking-[-0.02em]">Start New Session</h2>
                    <p class="text-xs font-medium tracking-[0.08em] text-muted-foreground uppercase">
                        {{ hasActiveQuestion ? 'Session in progress' : 'Ready' }}
                    </p>
                </div>

                <form class="mt-4 grid gap-3 sm:grid-cols-2" @submit.prevent="submitStart">
                    <div class="grid gap-2">
                        <p class="text-xs font-semibold tracking-[0.08em] text-muted-foreground uppercase">Sign Type</p>
                        <Select v-model="startForm.sign_type" :disabled="startForm.processing">
                            <SelectTrigger>
                                <SelectValue placeholder="Choose sign type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Mixed</SelectItem>
                                <SelectItem v-for="type in props.signTypes" :key="type" :value="type">
                                    {{ formatType(type) }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="grid gap-2">
                        <p class="text-xs font-semibold tracking-[0.08em] text-muted-foreground uppercase">Questions</p>
                        <Select v-model="startForm.total_questions" :disabled="startForm.processing">
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="count in questionCountChoices" :key="count" :value="count">
                                    {{ count }} questions
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="sm:col-span-2">
                        <Button type="submit" :disabled="startForm.processing" class="w-full sm:w-auto">
                            {{ startForm.processing ? 'Starting...' : 'Start Practice' }}
                        </Button>
                    </div>
                </form>
            </article>

            <article class="rounded-xl border border-border bg-card p-6">
                <h3 class="text-lg font-bold tracking-[-0.02em]">Quick Stats</h3>
                <div class="mt-4 grid gap-3">
                    <div class="rounded-md border border-border px-3 py-2">
                        <p class="text-xs tracking-[0.08em] text-muted-foreground uppercase">Tracked Categories</p>
                        <p class="mt-1 text-lg font-bold">{{ props.progressByType.length }}</p>
                    </div>
                    <div class="rounded-md border border-border px-3 py-2">
                        <p class="text-xs tracking-[0.08em] text-muted-foreground uppercase">Completed Sessions</p>
                        <p class="mt-1 text-lg font-bold">{{ props.recentSessions.length }}</p>
                    </div>
                    <Link :href="dashboard()" class="text-sm font-semibold text-primary hover:underline">
                        View full dashboard
                    </Link>
                    <Link href="/practice/history" class="text-sm font-semibold text-primary hover:underline">
                        Review past practice
                    </Link>
                </div>
            </article>
        </section>

        <section v-if="hasActiveQuestion" class="grid gap-4 lg:grid-cols-[1.15fr_0.85fr]">
            <article class="rounded-xl border border-border bg-card p-6">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-sm font-semibold text-muted-foreground">
                        Question {{ currentQuestionNumber }} of {{ props.question?.total }}
                    </p>
                    <p class="text-sm font-semibold text-primary">
                        {{ formatType(props.activeSession?.sign_type ?? null) }}
                    </p>
                </div>

                <div class="mt-3 h-2 rounded-full bg-secondary">
                    <div class="h-full rounded-full bg-primary transition-all" :style="{ width: `${questionProgressPercent}%` }" />
                </div>

                <div class="mt-5 overflow-hidden rounded-xl border border-border bg-muted/20 p-3">
                    <img
                        :src="props.question?.sign.image_path"
                        :alt="props.question?.sign.name"
                        class="mx-auto max-h-[320px] w-full rounded-lg object-contain"
                    />
                </div>
            </article>

            <article class="rounded-xl border border-border bg-card p-6">
                <h3 class="text-lg font-bold tracking-[-0.02em]">What does this sign mean?</h3>
                <div class="mt-4 grid gap-2">
                    <button
                        v-for="option in props.question?.options ?? []"
                        :key="option.id"
                        type="button"
                        class="w-full rounded-md border border-border px-4 py-3 text-left text-sm font-medium text-foreground transition-colors hover:border-primary hover:bg-primary/10"
                        :class="selectedOptionId === option.id ? 'border-primary bg-primary/15' : ''"
                        :disabled="answerForm.processing"
                        @click="submitAnswer(option)"
                    >
                        {{ option.name }}
                    </button>
                </div>
            </article>
        </section>

        <section v-else class="rounded-xl border border-border bg-card p-6">
            <h3 class="text-xl font-bold tracking-[-0.02em]">No Active Session</h3>
            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-muted-foreground">
                Start a new session to practice sign recognition. Once you answer
                all questions, your result appears here and updates your dashboard.
            </p>
        </section>

        <section class="grid gap-4 lg:grid-cols-2">
            <article class="rounded-xl border border-border bg-card p-6">
                <h3 class="text-xl font-bold tracking-[-0.02em]">Progress by Sign Type</h3>
                <div class="mt-4 grid gap-3">
                    <div
                        v-for="item in props.progressByType"
                        :key="item.sign_type"
                        class="rounded-md border border-border px-4 py-3"
                    >
                        <div class="flex items-center justify-between">
                            <p class="font-semibold">{{ formatType(item.sign_type) }}</p>
                            <p class="text-sm font-bold text-primary">{{ item.accuracy_percent }}%</p>
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ item.signs_correct }} correct out of {{ item.signs_attempted }} attempts • {{ item.mastery_level }}
                        </p>
                    </div>
                    <p v-if="props.progressByType.length === 0" class="text-sm text-muted-foreground">
                        No progress yet. Complete your first session to start tracking.
                    </p>
                </div>
            </article>

            <article class="rounded-xl border border-border bg-card p-6">
                <h3 class="text-xl font-bold tracking-[-0.02em]">Recent Session Results</h3>
                <div class="mt-4 grid gap-3">
                    <div
                        v-for="session in props.recentSessions"
                        :key="session.id"
                        class="rounded-md border border-border px-4 py-3"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-semibold">{{ formatType(session.sign_type) }}</p>
                            <p class="text-sm font-bold text-primary">{{ session.score_percent }}%</p>
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ session.correct_count }}/{{ session.total_questions }} correct
                        </p>
                    </div>
                    <p v-if="props.recentSessions.length === 0" class="text-sm text-muted-foreground">
                        Session history will appear here after completion.
                    </p>
                </div>
            </article>
        </section>
    </div>
</template>
