<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import {
    PlusIcon,
    PencilSquareIcon,
    TrashIcon,
    XMarkIcon,
    HashtagIcon,
    SparklesIcon
} from '@heroicons/vue/24/outline'

const userFacts = ref([])
const showModal = ref(false)
const editingFact = ref(null)

const newFact = ref({
    fact_text: '',
    importance: 3,
    category: ''
})

const categories = [
    'Personal', 'Professional', 'Hobby', 'Interest', 'Other'
]

async function fetchUserFacts() {
    const res = await axios.get('/api/user-facts')
    userFacts.value = res.data
}

function openCreateModal() {
    editingFact.value = null
    newFact.value = { fact_text: '', importance: 3, category: '' }
    showModal.value = true
}

function openEditModal(fact) {
    editingFact.value = fact
    newFact.value = { ...fact }
    showModal.value = true
}

function closeModal() {
    showModal.value = false
}

async function saveFact() {
    if (!newFact.value.fact_text.trim()) return

    if (editingFact.value) {
        const res = await axios.put(`/api/user-facts/${editingFact.value.id}`, newFact.value)
        Object.assign(editingFact.value, res.data)
    } else {
        const res = await axios.post('/api/user-facts', newFact.value)
        userFacts.value.push(res.data)
    }

    closeModal()
}

async function deleteFact(id) {
    await axios.delete(`/api/user-facts/${id}`)
    userFacts.value = userFacts.value.filter(f => f.id !== id)
}

onMounted(fetchUserFacts)
</script>

<template>
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-950 px-8">

            <!-- Header -->
            <div class="border-b border-gray-200 dark:border-gray-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-xl font-light text-gray-900 dark:text-white tracking-tight">
                                Memory Facts
                            </h1>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Store and organize important information about yourself to be used in personalization
                            </p>
                        </div>

                        <button
                            @click="openCreateModal"
                            class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-brand-primary to-brand-primary/80 hover:from-brand-primary/90 hover:to-brand-primary/70 rounded-lg transition-all duration-200 shadow-sm hover:shadow"
                        >
                            <PlusIcon class="w-4 h-4" />
                            Add Fact
                        </button>
                    </div>
                </div>
            </div>

            <!-- Facts Grid -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 auto-rows-min">
                    <div
                        v-for="fact in userFacts"
                        :key="fact.id"
                        :class="[
                            'group relative rounded-xl p-6 transition-all duration-300 hover:translate-y-[-2px] hover:shadow-lg',
                            fact.importance >= 4
                                ? 'bg-gradient-to-br from-brand-primary/10 via-brand-primary/5 to-white dark:from-brand-primary/20 dark:via-brand-primary/10 dark:to-gray-900 border border-brand-primary/20 dark:border-brand-primary/30 hover:border-brand-primary/40'
                                : 'bg-gradient-to-br from-gray-50 via-white to-gray-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-800 border border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700'
                        ]"
                    >
                        <!-- Importance indicator -->
                        <div class="absolute -top-2 -right-2">
                            <div :class="[
                                'flex items-center gap-0.5 rounded-full px-2 py-1 shadow-sm border backdrop-blur-sm',
                                fact.importance >= 4
                                    ? 'bg-gradient-to-r from-brand-primary/20 to-brand-primary/10 border-brand-primary/30 text-brand-primary dark:from-brand-primary/30 dark:to-brand-primary/20 dark:border-brand-primary/40 dark:text-brand-primary/90'
                                    : 'bg-gradient-to-r from-gray-100 to-white border-gray-200 text-gray-700 dark:from-gray-800 dark:to-gray-900 dark:border-gray-700 dark:text-gray-300'
                            ]">
                                <HashtagIcon class="w-3 h-3" />
                                <span class="text-xs font-semibold">
                                    {{ fact.importance }}
                                </span>
                            </div>
                        </div>

                        <!-- High importance sparkle -->
                        <div v-if="fact.importance >= 4" class="absolute -top-2 -left-2">
                            <div class="p-1 rounded-full bg-gradient-to-r from-brand-secondary to-brand-secondary/80 backdrop-blur-sm">
                                <SparklesIcon class="w-3 h-3 text-white" />
                            </div>
                        </div>

                        <!-- Fact content -->
                        <p class="text-gray-800 dark:text-gray-200 text-sm leading-relaxed mb-6 pr-8 font-light">
                            {{ fact.fact_text }}
                        </p>

                        <!-- Footer -->
                        <div class="flex items-center justify-between pt-4 border-t" :class="fact.importance >= 4 ? 'border-brand-primary/20 dark:border-brand-primary/30' : 'border-gray-100 dark:border-gray-800'">
                            <!-- Category -->
                            <div v-if="fact.category" class="flex items-center gap-2">
                                <div :class="[
                                    'w-2 h-2 rounded-full',
                                    fact.importance >= 4
                                        ? 'bg-brand-primary'
                                        : 'bg-gray-300 dark:bg-gray-600'
                                ]"></div>
                                <span :class="[
                                    'text-xs font-medium uppercase tracking-wide',
                                    fact.importance >= 4
                                        ? 'text-brand-primary dark:text-brand-primary/90'
                                        : 'text-gray-500 dark:text-gray-400'
                                ]">
                                    {{ fact.category }}
                                </span>
                            </div>
                            <div v-else class="text-xs text-gray-400 dark:text-gray-500 italic">
                                No category
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <button
                                    @click="openEditModal(fact)"
                                    class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                                    :class="fact.importance >= 4 ? 'text-brand-primary/80 hover:text-brand-primary' : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300'"
                                    title="Edit"
                                >
                                    <PencilSquareIcon class="w-4 h-4" />
                                </button>
                                <button
                                    @click="deleteFact(fact.id)"
                                    class="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                    :class="fact.importance >= 4 ? 'text-red-400 hover:text-red-500' : 'text-gray-400 hover:text-red-500'"
                                    title="Delete"
                                >
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-if="userFacts.length === 0" class="text-center py-20">
                    <div class="w-16 h-16 mx-auto mb-6 rounded-full bg-gradient-to-br from-brand-primary/20 to-brand-primary/10 dark:from-brand-primary/30 dark:to-brand-primary/20 flex items-center justify-center">
                        <PlusIcon class="w-8 h-8 text-brand-primary dark:text-brand-primary/90" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                        No facts yet
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">
                        Start by adding your first memory fact to keep track of important information.
                    </p>
                    <button
                        @click="openCreateModal"
                        class="px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-brand-primary to-brand-primary/80 hover:from-brand-primary/90 hover:to-brand-primary/70 rounded-lg transition-all duration-200 shadow-sm hover:shadow"
                    >
                        Add Your First Fact
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <Transition name="modal">
            <div
                v-if="showModal"
                class="fixed inset-0 z-50 overflow-y-auto"
            >
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/50" @click="closeModal"></div>

                <!-- Modal container -->
                <div class="flex min-h-full items-center justify-center p-4">
                    <!-- Modal panel -->
                    <div class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-gray-900 shadow-2xl transition-all">

                        <!-- Header -->
                        <div class="px-6 pt-6 pb-4 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                    {{ editingFact ? 'Edit Fact' : 'New Fact' }}
                                </h3>
                                <button
                                    @click="closeModal"
                                    class="rounded-lg p-1.5 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                                >
                                    <XMarkIcon class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                                </button>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="px-6 py-5">
                            <textarea
                                v-model="newFact.fact_text"
                                placeholder="Type your fact here..."
                                rows="4"
                                class="w-full px-4 py-3 bg-transparent border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-primary/30 dark:focus:ring-brand-primary/50 focus:border-transparent resize-none"
                                autofocus
                            ></textarea>

                            <div class="mt-6 space-y-6">
                                <!-- Category -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Category
                                    </label>
                                    <select
                                        v-model="newFact.category"
                                        class="w-full px-4 py-2.5 bg-transparent border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-primary/30 dark:focus:ring-brand-primary/50 focus:border-transparent"
                                    >
                                        <option value="" class="dark:bg-gray-900">No category</option>
                                        <option
                                            v-for="cat in categories"
                                            :key="cat"
                                            :value="cat"
                                            class="dark:bg-gray-900"
                                        >
                                            {{ cat }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Importance -->
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Importance
                                        </label>
                                        <span class="text-sm font-medium text-brand-primary dark:text-brand-primary/90">
                                            {{ newFact.importance }}/5
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="range"
                                            min="1"
                                            max="5"
                                            v-model="newFact.importance"
                                            class="flex-1 h-1.5 bg-gradient-to-r from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-600 rounded-lg appearance-none cursor-pointer [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-brand-primary dark:[&::-webkit-slider-thumb]:bg-brand-primary/90"
                                        />
                                        <div class="flex items-center gap-1 text-xs text-gray-400">
                                            <span>Low</span>
                                            <span>High</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="px-6 py-5 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-800">
                            <div class="flex justify-end gap-3">
                                <button
                                    @click="closeModal"
                                    class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors"
                                >
                                    Cancel
                                </button>
                                <button
                                    @click="saveFact"
                                    :disabled="!newFact.fact_text.trim()"
                                    class="px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-brand-primary to-brand-primary/80 hover:from-brand-primary/90 hover:to-brand-primary/70 rounded-lg transition-all duration-200 shadow-sm hover:shadow disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-none"
                                >
                                    {{ editingFact ? 'Update' : 'Save' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Modal transition */
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-active .modal-panel,
.modal-leave-active .modal-panel {
    transition: transform 0.3s ease;
}

.modal-enter-from .modal-panel,
.modal-leave-to .modal-panel {
    transform: scale(0.95);
}

/* Card hover effects */
.group {
    will-change: transform, box-shadow;
}

.group:hover {
    transform: translateY(-2px);
}
</style>
