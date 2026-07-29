<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login } from '@/routes';
import { register } from '@/routes';

const stats = [
    { label: 'Practice Topics', value: '40+' },
    { label: 'Sample Questions', value: '500+' },
    { label: 'Progress Tracking', value: 'Daily' },
];

const features = [
    {
        title: 'Theory Test Practice',
        description:
            'Train with Uganda driving guide style questions and timed quiz sessions.',
    },
    {
        title: 'Progress Tracking',
        description:
            'See your weak sections quickly and keep improving with focused revision.',
    },
    {
        title: 'Structured Learning',
        description:
            'Study road signs, right-of-way, penalties, and safe driving principles in sequence.',
    },
];
</script>

<template>
    <Head title="UD Driving Guide">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-background text-foreground">
        <header class="border-b border-border/90">
            <div
                class="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-4"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-9 items-center justify-center rounded-md bg-primary text-[13px] font-bold text-primary-foreground"
                    >
                        UD
                    </div>
                    <p class="text-sm font-semibold tracking-tight">
                        UD Driving Guide
                    </p>
                </div>

                <nav class="flex items-center gap-2">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="inline-flex h-10 items-center rounded-md border border-primary bg-primary px-5 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
                    >
                        Open Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="inline-flex h-10 items-center rounded-md border border-border bg-card px-5 text-sm font-semibold text-foreground transition-colors hover:border-primary hover:text-primary"
                        >
                            Log in
                        </Link>
                        <Link
                            :href="register()"
                            class="inline-flex h-10 items-center rounded-md border border-primary bg-primary px-5 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
                        >
                            Get started
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <main class="mx-auto w-full max-w-6xl px-6 py-16 md:py-24">
            <section class="grid items-start gap-10 lg:grid-cols-[1.1fr_0.9fr]">
                <div>
                    <p
                        class="mb-4 inline-flex rounded-full border border-border bg-card px-3 py-1 text-xs font-semibold tracking-[0.12em] text-muted-foreground"
                    >
                        THEORY PREP TOOL
                    </p>
                    <h1
                        class="max-w-3xl text-4xl leading-[1.05] font-bold tracking-[-0.03em] text-foreground md:text-6xl"
                    >
                        Learn, Practice, and Track Your Progress for Uganda
                        Driving Theory.
                    </h1>
                    <p
                        class="mt-6 max-w-2xl text-base leading-relaxed text-[#cccccc]"
                    >
                        UD Driving Guide helps learners prepare for theory tests
                        with structured content based on the Uganda driving
                        guide, practice questions, and revision-friendly
                        progress insights.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="dashboard()"
                            class="inline-flex h-11 items-center rounded-md border border-primary bg-primary px-6 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
                        >
                            Continue learning
                        </Link>
                        <template v-else>
                            <Link
                                :href="register()"
                                class="inline-flex h-11 items-center rounded-md border border-primary bg-primary px-6 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
                            >
                                Create free account
                            </Link>
                            <Link
                                :href="login()"
                                class="inline-flex h-11 items-center rounded-md border border-border bg-card px-6 text-sm font-semibold text-foreground transition-colors hover:border-primary hover:text-primary"
                            >
                                I already have an account
                            </Link>
                        </template>
                    </div>

                    <div class="mt-12 grid gap-4 sm:grid-cols-3">
                        <article
                            v-for="item in stats"
                            :key="item.label"
                            class="rounded-xl border border-border bg-card p-5"
                        >
                            <p
                                class="text-4xl leading-none font-bold tracking-[-0.03em] text-primary"
                            >
                                {{ item.value }}
                            </p>
                            <p class="mt-2 text-sm text-muted-foreground">
                                {{ item.label }}
                            </p>
                        </article>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="rounded-xl border border-border bg-card p-6">
                        <p
                            class="mb-3 text-xs font-semibold tracking-[0.12em] text-muted-foreground"
                        >
                            SAMPLE PRACTICE ITEM
                        </p>
                        <p class="text-sm leading-relaxed text-foreground">
                            While approaching a zebra crossing, a driver should:
                        </p>
                        <ul class="mt-4 space-y-2 text-sm text-muted-foreground">
                            <li class="rounded-md border border-border px-3 py-2">
                                A. Maintain speed if the road is clear
                            </li>
                            <li
                                class="rounded-md border border-primary bg-primary px-3 py-2 font-semibold text-primary-foreground"
                            >
                                B. Slow down and give way to pedestrians
                            </li>
                            <li class="rounded-md border border-border px-3 py-2">
                                C. Overtake slower vehicles near the crossing
                            </li>
                        </ul>
                    </div>

                    <div class="rounded-xl border border-primary bg-primary p-6">
                        <h2
                            class="text-2xl leading-tight font-bold tracking-[-0.02em] text-primary-foreground"
                        >
                            Independent Educational Tool Notice
                        </h2>
                        <p class="mt-3 text-sm leading-relaxed text-primary-foreground">
                            This app is an independent educational tool. It is
                            not a government entity and is not affiliated with,
                            endorsed by, or connected to the Uganda Ministry of
                            Works and Transport, Uganda Police Force, or the
                            Uganda Driving License System (UDLS).
                        </p>
                    </div>
                </div>
            </section>

            <section class="mt-16 grid gap-4 md:grid-cols-3">
                <article
                    v-for="feature in features"
                    :key="feature.title"
                    class="rounded-xl border border-border bg-card p-6"
                >
                    <h3 class="text-lg font-bold tracking-[-0.02em]">
                        {{ feature.title }}
                    </h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
                        {{ feature.description }}
                    </p>
                </article>
            </section>
        </main>
    </div>
</template>
