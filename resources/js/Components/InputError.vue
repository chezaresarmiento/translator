<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
    <div v-show="message">
        <p class="text-sm text-gray-100">
            {{ message }}
        </p>
    </div>
</template>
