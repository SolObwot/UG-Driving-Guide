<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { dashboard } from '@/routes';

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

const modules = [
    {
        title: 'Road Signs',
        progress: 72,
        note: 'Warning, regulatory, and informatory signs',
    },
    {
        title: 'Traffic Rules',
        progress: 45,
        note: 'Lane discipline, right of way, and penalties',
    },
    {
        title: 'Safe Driving',
        progress: 63,
        note: 'Hazard awareness and defensive driving',
    },
];

const todayPlan = [
    'Revise 15 road sign flashcards',
    'Attempt a 20-question mixed theory set',
    'Review incorrect answers and notes',
];
</script>

<template>
    <Head title="Study Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-5 md:p-8">
        <section class="rounded-xl border border-primary bg-primary p-6 md:p-8">
            <p
                class="text-xs font-semibold tracking-[0.12em] text-primary-foreground/80"
            >
                UD DRIVING GUIDE
            </p>
            <h1
                class="mt-2 text-3xl leading-tight font-bold tracking-[-0.03em] text-primary-foreground md:text-4xl"
            >
                Keep building confidence for your Uganda theory test.
            </h1>
            <p class="mt-3 max-w-3xl text-sm leading-relaxed text-primary-foreground">
                This tool helps you study with focused theory practice, score
                tracking, and revision cues. It is an independent educational
                tool and not an official government platform.
            </p>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <article
                v-for="module in modules"
                :key="module.title"
                class="rounded-xl border border-border bg-card p-5"
            >
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold tracking-[-0.01em]">
                        {{ module.title }}
                    </h2>
                    <p class="text-lg font-bold tracking-tight text-primary">
                        {{ module.progress }}%
                    </p>
                </div>
                <p class="mt-2 text-sm text-muted-foreground">
                    {{ module.note }}
                </p>
                <div class="mt-4 h-2 rounded-full bg-secondary">
                    <div
                        class="h-full rounded-full bg-primary"
                        :style="{ width: `${module.progress}%` }"
                    />
                </div>
            </article>
        </section>

        <section class="grid gap-4 lg:grid-cols-[1.15fr_0.85fr]">
            <article class="rounded-xl border border-border bg-card p-6">
                <h3 class="text-xl font-bold tracking-[-0.02em]">
                    Quick Theory Challenge
                </h3>
                <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
                    Best immediate action when a traffic light turns amber and
                    you are close to the stop line:
                </p>
                <ul class="mt-4 space-y-2 text-sm text-muted-foreground">
                    <li class="rounded-md border border-border px-3 py-2">
                        A. Accelerate to cross before red
                    </li>
                    <li
                        class="rounded-md border border-primary bg-primary px-3 py-2 font-semibold text-primary-foreground"
                    >
                        B. Stop safely if you can do so without sudden braking
                    </li>
                    <li class="rounded-md border border-border px-3 py-2">
                        C. Use horn and continue regardless
                    </li>
                </ul>
            </article>

            <article class="rounded-xl border border-border bg-card p-6">
                <h3 class="text-xl font-bold tracking-[-0.02em]">
                    Today&apos;s Study Plan
                </h3>
                <ol class="mt-4 space-y-2 text-sm text-muted-foreground">
                    <li
                        v-for="step in todayPlan"
                        :key="step"
                        class="rounded-md border border-border px-3 py-2"
                    >
                        {{ step }}
                    </li>
                </ol>
            </article>
        </section>
    </div>
</template>
