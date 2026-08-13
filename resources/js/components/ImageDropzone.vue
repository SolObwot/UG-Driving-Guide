<script setup lang="ts">
import { UploadCloud } from '@lucide/vue';
import { ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue?: string;
        label?: string;
        accept?: string;
    }>(),
    {
        label: 'Click to upload or drag and drop',
        accept: 'image/*',
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const previewUrl = ref(props.modelValue ?? '');
const isDragging = ref(false);

watch(
    () => props.modelValue,
    (value) => {
        previewUrl.value = value ?? '';
    },
    { immediate: true },
);

const compressImage = (file: File, maxWidth = 1600, quality = 0.82): Promise<string> => new Promise((resolve, reject) => {
    const image = new Image();
    const reader = new FileReader();

    reader.onload = () => {
        image.onload = () => {
            const canvas = document.createElement('canvas');
            const scale = Math.min(1, maxWidth / Math.max(image.width, 1));
            canvas.width = Math.max(1, Math.round(image.width * scale));
            canvas.height = Math.max(1, Math.round(image.height * scale));

            const context = canvas.getContext('2d');

            if (!context) {
                reject(new Error('Canvas is not supported in this browser.'));
                return;
            }

            context.drawImage(image, 0, 0, canvas.width, canvas.height);
            resolve(canvas.toDataURL(file.type || 'image/jpeg', quality));
        };

        image.onerror = () => reject(new Error('Unable to read the selected image.'));
        image.src = String(reader.result);
    };

    reader.onerror = () => reject(new Error('Unable to read the selected image.'));
    reader.readAsDataURL(file);
});

const setPreviewFromFile = async (file?: File | null) => {
    if (!file || !file.type.startsWith('image/')) {
        return;
    }

    try {
        const result = await compressImage(file);
        previewUrl.value = result;
        emit('update:modelValue', result);
    } catch {
        const reader = new FileReader();

        reader.onload = () => {
            const result = typeof reader.result === 'string' ? reader.result : '';
            previewUrl.value = result;
            emit('update:modelValue', result);
        };

        reader.readAsDataURL(file);
    }
};

const handleInput = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    void setPreviewFromFile(file);
    input.value = '';
};

const handleDrop = (event: DragEvent) => {
    isDragging.value = false;
    void setPreviewFromFile(event.dataTransfer?.files?.[0]);
};

const clearImage = () => {
    previewUrl.value = '';
    emit('update:modelValue', '');
};
</script>

<template>
    <div class="flex w-full items-center justify-center">
        <label
            class="flex h-64 w-full cursor-pointer flex-col items-center justify-center rounded-lg border border-dashed border-border bg-muted/20 transition-colors hover:bg-muted/30"
            :class="isDragging ? 'border-primary bg-primary/5' : ''"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
        >
            <div v-if="previewUrl" class="relative flex h-full w-full items-center justify-center p-3">
                <img :src="previewUrl" alt="Road sign preview" class="max-h-52 w-auto rounded-md object-contain" />

                <button
                    type="button"
                    class="absolute right-3 top-3 rounded-md bg-background/90 px-2 py-1 text-[11px] font-medium text-foreground ring-1 ring-border"
                    @click.prevent="clearImage"
                >
                    Remove
                </button>
            </div>

            <div v-else class="flex flex-col items-center justify-center pb-6 pt-5 text-center text-muted-foreground">
                <UploadCloud class="mb-4 h-8 w-8" />
                <p class="mb-2 text-sm">
                    <span class="font-semibold text-foreground">Click to upload</span>
                    or drag and drop
                </p>
                <p class="text-xs">SVG, PNG, JPG or GIF</p>
            </div>

            <input :accept="accept" type="file" class="hidden" @input="handleInput" />
        </label>
    </div>
</template>
