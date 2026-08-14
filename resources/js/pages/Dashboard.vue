<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import { index as practice } from '@/routes/practice';
import { index as roadSigns } from '@/routes/road-signs';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Study Dashboard',
                href: dashboard(),
            },
        ],
    },
});

type ProgressByType = {
    sign_type: string;
    accuracy_percent: number;
    signs_attempted: number;
    signs_correct: number;
    mastery_level: string;
    last_practiced_at: string | null;
};

type SessionSummary = {
    id: number;
    sign_type: string | null;
    total_questions: number;
    correct_count: number;
    wrong_count: number;
    score_percent: number;
    completed_at: string | null;
};

type Props = {
    overallProgress: {
        attempted: number;
        correct: number;
        accuracy_percent: number;
        sessions_completed: number;
        best_score: number;
    };
    progressByType: ProgressByType[];
    recentSessions: SessionSummary[];
    activeRoadSignsCount: number;
};

const props = defineProps<Props>();

const formatType = (type: string | null) => {
    if (!type) {
        return 'Mixed';
    }

    return type
        .split('_')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
};
</script>

<template>

    <Head title="Study Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-5 md:p-8">
        <section class="rounded-xl border border-primary bg-primary p-6 md:p-8">
            <p class="text-xs font-semibold tracking-[0.12em] text-primary-foreground/80">
                UD DRIVING GUIDE
            </p>
            <h1 class="mt-2 text-3xl leading-tight font-bold tracking-[-0.03em] text-primary-foreground md:text-4xl">
                Keep building confidence for your Uganda theory test.
            </h1>
            <p class="mt-3 max-w-3xl text-sm leading-relaxed text-primary-foreground">
                This tool helps you study with focused theory practice, score
                tracking, and revision cues. It is an independent educational
                tool and not an official government platform.
            </p>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <article class="rounded-xl border border-border bg-card p-5">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold tracking-[-0.01em]">
                        Overall Accuracy
                    </h2>
                    <p class="text-lg font-bold tracking-tight text-primary">
                        {{ props.overallProgress.accuracy_percent }}%
                    </p>
                </div>
                <p class="mt-2 text-sm text-muted-foreground">
                    {{ props.overallProgress.correct }} correct out of {{ props.overallProgress.attempted }} answers
                </p>
                <div class="mt-4 h-2 rounded-full bg-secondary">
                    <div class="h-full rounded-full bg-primary" :style="{ width: `${props.overallProgress.accuracy_percent}%` }" />
                </div>
            </article>

            <article class="rounded-xl border border-border bg-card p-5">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold tracking-[-0.01em]">
                        Completed Sessions
                    </h2>
                    <p class="text-lg font-bold tracking-tight text-primary">
                        {{ props.overallProgress.sessions_completed }}
                    </p>
                </div>
                <p class="mt-2 text-sm text-muted-foreground">
                    Best score achieved: {{ props.overallProgress.best_score }}%
                </p>
                <div class="mt-4 h-2 rounded-full bg-secondary">
                    <div class="h-full rounded-full bg-primary" :style="{ width: `${props.overallProgress.best_score}%` }" />
                </div>
            </article>

            <article class="rounded-xl border border-border bg-card p-5">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold tracking-[-0.01em]">
                        Active Sign Bank
                    </h2>
                    <p class="text-lg font-bold tracking-tight text-primary">
                        {{ props.activeRoadSignsCount }}
                    </p>
                </div>
                <p class="mt-2 text-sm text-muted-foreground">
                    Road signs available for practice sessions
                </p>
                <div class="mt-4 flex gap-2">
                    <Link :href="practice()" class="text-sm font-semibold text-primary hover:underline">Start practice</Link>
                    <Link :href="roadSigns()" class="text-sm font-semibold text-primary hover:underline">Manage signs</Link>
                </div>
            </article>
        </section>

        <section class="grid gap-4 lg:grid-cols-[1.15fr_0.85fr]">
            <article class="rounded-xl border border-border bg-card p-6">
                <h3 class="text-xl font-bold tracking-[-0.02em]">
                    Sign Type Mastery
                </h3>
                <div class="mt-4 space-y-2 text-sm text-muted-foreground">
                    <div
                        v-for="item in props.progressByType"
                        :key="item.sign_type"
                        class="rounded-md border border-border px-3 py-2"
                    >
                        <div class="flex items-center justify-between">
                            <p class="font-semibold text-foreground">{{ formatType(item.sign_type) }}</p>
                            <p class="font-bold text-primary">{{ item.accuracy_percent }}%</p>
                        </div>
                        <p class="text-xs">
                            {{ item.signs_correct }} correct / {{ item.signs_attempted }} attempted • {{ item.mastery_level }}
                        </p>
                    </div>
                    <p v-if="props.progressByType.length === 0" class="rounded-md border border-border px-3 py-2">
                        No mastery data yet. Complete a practice session to begin tracking.
                    </p>
                </div>
            </article>

            <article class="rounded-xl border border-border bg-card p-6">
                <h3 class="text-xl font-bold tracking-[-0.02em]">
                    Recent Results
                </h3>
                <div class="mt-4 space-y-2 text-sm text-muted-foreground">
                    <div
                        v-for="session in props.recentSessions"
                        :key="session.id"
                        class="rounded-md border border-border px-3 py-2"
                    >
                        <div class="flex items-center justify-between">
                            <p class="font-semibold text-foreground">{{ formatType(session.sign_type) }}</p>
                            <p class="font-bold text-primary">{{ session.score_percent }}%</p>
                        </div>
                        <p class="text-xs">
                            {{ session.correct_count }}/{{ session.total_questions }} correct
                        </p>
                    </div>
                    <p v-if="props.recentSessions.length === 0" class="rounded-md border border-border px-3 py-2">
                        No completed sessions yet.
                    </p>
                </div>
            </article>
        </section>
    </div>
</template>
