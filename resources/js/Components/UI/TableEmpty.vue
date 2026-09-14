<template>
    <div class="py-10 text-center text-sm text-text-muted">
        <div v-if="$slots.icon" class="mb-3 flex justify-center">
            <slot name="icon" />
        </div>
        <slot />
    </div>
</template>
