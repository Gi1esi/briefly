<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import {Link} from '@inertiajs/vue3'

const props = defineProps({
    tags: Array
})
</script>

<template>
    <AuthenticatedLayout>
        <div class="px-14 pt-10 bg-gray-50 dark:bg-neutral-darkBg min-h-screen">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-neutral-darkText mb-6">Topics</h2>

            <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <div v-for="tag in tags" :key="tag.id" class="bg-white dark:bg-brand-primary/10 rounded-xl shadow p-4 flex flex-col justify-between hover:shadow-md transition">
                    <div class="flex justify-between items-center">
                        <h3 class="font-semibold text-gray-800 dark:text-white">{{ tag.name }}</h3>
                        <span class="text-xs text-gray-500">{{ tag.article_count }}</span>
                    </div>
                    <Link :href="`/articles?tag=${tag.name}`" class="mt-3 text-sm text-brand-secondary hover:underline">
                        Explore Articles
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
</style>
