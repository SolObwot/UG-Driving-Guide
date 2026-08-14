<script setup lang="ts">
import { computed, ref } from 'vue';
import { ChevronDown } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';

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

const props = defineProps<{
    session: SessionItem;
}>();

const isOpen = ref(false);
const mistakesOnly = ref(false);

const formatType = (type: string | null) => {
    if (!type) {
        return 'Mixed';
    }

    return type
        .split('_')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
};

const scoreTone = computed(() => {
    if (props.session.score_percent >= 80) {
        return 'text-primary';
    }

    if (props.session.score_percent >= 60) {
        return 'text-foreground';
    }

    return 'text-destructive';
});

const visibleQuestions = computed(() =>
    mistakesOnly.value
        ? props.session.questions.filter((question) => !question.is_correct)
        : props.session.questions,
);
</script>

<template>
    <Collapsible v-model:open="isOpen" class="rounded-xl border border-border bg-card">
        <div class="flex flex-wrap items-center justify-between gap-3 p-4 sm:p-5">
            <div>
                <p class="text-xs font-semibold tracking-[0.08em] text-muted-foreground uppercase">
                    {{ formatType(props.session.sign_type) }} session
                </p>
                <h3 class="mt-1 text-lg font-bold tracking-[-0.01em]">
                    Session #{{ props.session.id }}
                </h3>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{ props.session.correct_count }}/{{ props.session.total_questions }} correct
                </p>
            </div>

            <div class="flex items-center gap-2">
                <Badge variant="outline" class="font-semibold text-muted-foreground">
                    {{ props.session.wrong_count }} wrong
                </Badge>
                <p class="text-lg font-bold" :class="scoreTone">{{ props.session.score_percent }}%</p>
                <CollapsibleTrigger as-child>
                    <Button variant="outline" size="sm" class="gap-1.5">
                        {{ isOpen ? 'Hide details' : 'Review details' }}
                        <ChevronDown class="h-4 w-4 transition-transform" :class="isOpen ? 'rotate-180' : ''" />
                    </Button>
                </CollapsibleTrigger>
            </div>
        </div>

        <CollapsibleContent class="border-t border-border px-4 pb-4 pt-3 sm:px-5 sm:pb-5">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <p class="text-xs text-muted-foreground">
                    Showing {{ visibleQuestions.length }} of {{ props.session.questions.length }} questions
                </p>

                <Button
                    v-if="props.session.wrong_count > 0"
                    variant="ghost"
                    size="sm"
                    @click="mistakesOnly = !mistakesOnly"
                >
                    {{ mistakesOnly ? 'Show all questions' : 'Show mistakes only' }}
                </Button>
            </div>

            <div class="space-y-2">
                <article
                    v-for="question in visibleQuestions"
                    :key="`${props.session.id}-${question.order}`"
                    class="rounded-lg border border-border bg-muted/20 p-3"
                >
                    <div class="grid gap-3 md:grid-cols-[110px_1fr]">
                        <div class="overflow-hidden rounded-md border border-border bg-background/30 p-1.5">
                            <img
                                :src="question.shown_sign.image_path"
                                :alt="question.shown_sign.name"
                                class="h-20 w-full rounded object-contain"
                            />
                        </div>

                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-semibold">Question {{ question.order }}</p>
                                <Badge :variant="question.is_correct ? 'default' : 'destructive'">
                                    {{ question.is_correct ? 'Correct' : 'Wrong' }}
                                </Badge>
                            </div>

                            <p class="text-xs text-muted-foreground">
                                Your answer:
                                <span class="font-semibold text-foreground">{{ question.selected_sign?.name ?? 'Not answered' }}</span>
                            </p>

                            <p v-if="!question.is_correct" class="text-xs text-muted-foreground">
                                Correct answer:
                                <span class="font-semibold text-primary">{{ question.correct_sign.name }}</span>
                            </p>

                            <p v-if="question.time_taken_seconds !== null" class="text-xs text-muted-foreground">
                                {{ question.time_taken_seconds }}s to answer
                            </p>
                        </div>
                    </div>
                </article>

                <article
                    v-if="mistakesOnly && visibleQuestions.length === 0"
                    class="rounded-md border border-border bg-muted/15 p-3 text-sm text-muted-foreground"
                >
                    No mistakes in this session.
                </article>
            </div>
        </CollapsibleContent>
    </Collapsible>
</template>
