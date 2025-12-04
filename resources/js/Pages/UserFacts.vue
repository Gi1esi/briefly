<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import {
    PlusIcon,
    PencilSquareIcon,
    TrashIcon,
    XMarkIcon,
    HashtagIcon
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
                                Store and organize important information
                            </p>
                        </div>

                        <button
                            @click="openCreateModal"
                            class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-gray-900 dark:bg-white dark:text-gray-900 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-200 transition-all duration-200"
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
                        class="group relative bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 p-6 hover:border-gray-200 dark:hover:border-gray-700 transition-all duration-300"
                    >
                        <!-- Importance indicator -->
                        <div class="absolute -top-2 -right-2">
                            <div class="flex items-center gap-0.5 bg-white dark:bg-gray-800 rounded-full px-2 py-1 shadow-sm border border-gray-100 dark:border-gray-700">
                                <HashtagIcon class="w-3 h-3 text-gray-400" />
                                <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                    {{ fact.importance }}
                                </span>
                            </div>
                        </div>

                        <!-- Fact content -->
                        <p class="text-gray-800 dark:text-gray-200 text-sm font-light leading-relaxed mb-6 pr-8">
                            {{ fact.fact_text }}
                        </p>

                        <!-- Footer -->
                        <div class="flex items-center justify-between pt-4 border-t border-gray-50 dark:border-gray-800">
                            <!-- Category -->
                            <div v-if="fact.category" class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-gray-300 dark:bg-gray-600"></div>
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
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
                                    class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                                    title="Edit"
                                >
                                    <PencilSquareIcon class="w-4 h-4" />
                                </button>
                                <button
                                    @click="deleteFact(fact.id)"
                                    class="p-1.5 text-gray-400 hover:text-red-500 transition-colors"
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
                    <div class="w-16 h-16 mx-auto mb-6 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                        <PlusIcon class="w-8 h-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                        No facts yet
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">
                        Start by adding your first memory fact to keep track of important information.
                    </p>
                    <button
                        @click="openCreateModal"
                        class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
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
                                class="w-full px-4 py-3 bg-transparent border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:focus:ring-gray-600 focus:border-transparent resize-none"
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
                                        class="w-full px-4 py-2.5 bg-transparent border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-400 dark:focus:ring-gray-600 focus:border-transparent"
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
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ newFact.importance }}/5
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="range"
                                            min="1"
                                            max="5"
                                            v-model="newFact.importance"
                                            class="flex-1 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-gray-900 dark:[&::-webkit-slider-thumb]:bg-white"
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
                                    class="px-4 py-2.5 text-sm font-medium text-white bg-gray-900 dark:bg-white dark:text-gray-900 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-200 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
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
</style>
