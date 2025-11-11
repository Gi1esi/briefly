<script setup>
import { computed } from 'vue'

const props = defineProps({
    totalPages: {
        type: Number,
        required: true,
    },
    currentPage: {
        type: Number,
        required: true,
    },
})

const emits = defineEmits(['update:page'])

function setPage(n) {
    if (n !== '...' && n !== props.currentPage) {
        emits('update:page', n)
    }
}

function prevPage() {
    if (props.currentPage > 1) emits('update:page', props.currentPage - 1)
}

function nextPage() {
    if (props.currentPage < props.totalPages) emits('update:page', props.currentPage + 1)
}

// Compute visible pages (with ellipses)
const visiblePages = computed(() => {
    const total = props.totalPages
    const current = props.currentPage
    const delta = 2
    const range = []

    for (let i = Math.max(2, current - delta); i <= Math.min(total - 1, current + delta); i++) {
        range.push(i)
    }

    if (current - delta > 2) range.unshift('...')
    if (current + delta < total - 1) range.push('...')

    range.unshift(1)
    if (total > 1) range.push(total)

    return range
})
</script>

<template>
    <div class="flex justify-center mt-8 space-x-2">
        <button
            @click="prevPage"
            :disabled="currentPage === 1"
            class="px-3 py-1 border rounded-md text-sm"
            :class="currentPage === 1
        ? 'text-gray-300 border-gray-200 cursor-not-allowed'
        : 'text-gray-700 hover:bg-gray-100 border-gray-300'">
            Previous
        </button>

        <template v-for="n in visiblePages" :key="n">
      <span
          v-if="n === '...'"
          class="px-3 py-1 text-gray-400 select-none">...</span>
            <button
                v-else
                @click="setPage(n)"
                class="px-3 py-1 border rounded-md text-sm"
                :class="n === currentPage
          ? 'bg-brand-secondary text-white border-brand-secondary'
          : 'text-gray-700 border-gray-300 hover:bg-gray-100'">
                {{ n }}
            </button>
        </template>

        <button
            @click="nextPage"
            :disabled="currentPage === totalPages"
            class="px-3 py-1 border rounded-md text-sm"
            :class="currentPage === totalPages
        ? 'text-gray-300 border-gray-200 cursor-not-allowed'
        : 'text-gray-700 hover:bg-gray-100 border-gray-300'">
            Next
        </button>
    </div>
</template>

<style scoped>
.text-brand-secondary {
    color: #ff644f;
}
.bg-brand-secondary {
    background-color: #ff644f;
}
</style>
