<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link } from '@inertiajs/vue3'
import {
    HashtagIcon,
    ArrowRightIcon,
    ChartBarIcon,
    FireIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    tags: Array
})
</script>

<template>
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-950 px-8">
            <!-- Header -->
            <div class="border-b border-gray-200 dark:border-gray-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="p-2 rounded-lg bg-brand-primary">
                            <HashtagIcon class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-light text-gray-900 dark:text-white tracking-tight">
                                Topics
                            </h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                Browse articles by category
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <!-- Popular Topics Section -->
                <div class="mb-10">
                    <div class="flex items-center gap-2 mb-6">
                        <FireIcon class="w-5 h-5 text-brand-secondary" />
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                            Trending Topics
                        </h2>
                    </div>
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                        <div
                            v-for="tag in tags.filter(t => t.article_count >= 10).slice(0, 4)"
                            :key="tag.id"
                            class="group relative overflow-hidden"
                        >
                            <Link
                                :href="`/articles?tag=${tag.name}`"
                                class="block bg-gradient-to-br from-brand-primary/10 to-brand-primary/5 dark:from-brand-primary/20 dark:to-transparent border border-brand-primary/20 dark:border-brand-primary/30 rounded-xl p-5 hover:border-brand-primary/40 dark:hover:border-brand-primary/50 hover:shadow-sm transition-all duration-300"
                            >
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-2">
                                        <div class="p-1.5 rounded-md bg-brand-primary">
                                            <HashtagIcon class="w-3.5 h-3.5 text-white" />
                                        </div>
                                        <h3 class="font-medium text-gray-900 dark:text-white truncate">
                                            {{ tag.name }}
                                        </h3>
                                    </div>
                                    <div class="flex items-center gap-1.5 px-2 py-1 rounded-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                                        <ChartBarIcon class="w-3.5 h-3.5 text-gray-600 dark:text-gray-400" />
                                        <span class="text-xs font-semibold text-brand-primary">
                                            {{ tag.article_count }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                                    <span class="text-sm text-brand-primary dark:text-brand-primary/90 font-medium">
                                        Explore
                                    </span>
                                    <ArrowRightIcon class="w-4 h-4 text-brand-primary transform group-hover:translate-x-1 transition-transform" />
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- All Topics Section -->
                <div>
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                            All Topics
                        </h2>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ tags.length }} topics total
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                        <div
                            v-for="tag in tags"
                            :key="tag.id"
                            class="group"
                        >
                            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 hover:border-gray-300 dark:hover:border-gray-700 hover:shadow-sm transition-all duration-300 h-full">
                                <div class="flex items-start justify-between mb-3">
                                    <h3 class="font-medium text-gray-900 dark:text-white truncate">
                                        {{ tag.name }}
                                    </h3>
                                </div>

                                <div class="flex items-center gap-2 mb-4">
                                    <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                        <ChartBarIcon class="w-3.5 h-3.5" />
                                        <span>{{ tag.article_count }} articles</span>
                                    </div>
                                </div>

                                <Link
                                    :href="`/articles?tag=${tag.name}`"
                                    class="inline-flex items-center gap-2 w-full justify-center px-4 py-2.5 text-sm font-medium text-white bg-brand-secondary rounded-lg hover:bg-brand-secondary/90 transition-all duration-200 group-hover:shadow-sm"
                                >
                                    <span>Browse Articles</span>
                                    <ArrowRightIcon class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="tags.length === 0" class="text-center py-16">
                    <div class="w-16 h-16 mx-auto mb-6 rounded-full bg-gray-100 dark:bg-brand-primary/10 flex items-center justify-center">
                        <HashtagIcon class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                        No topics yet
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                        Topics will appear here as articles are categorized.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Smooth hover effects */
.group:hover .group-hover\:translate-x-1 {
    transform: translateX(4px);
}

/* Ensure consistent card heights */
.grid > div {
    display: flex;
}

.grid > div > * {
    flex: 1;
}
</style>
