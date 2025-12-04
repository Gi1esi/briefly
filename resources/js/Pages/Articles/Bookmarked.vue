<script setup>
import { ref, computed } from 'vue'
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import NewsCard from "@/Components/NewsCard.vue";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
    articles: Array,
    pagination: Object
});

const page = ref(1);

const totalPages = computed(() => props.pagination.last_page || 1);


async function toggleLike(article) {
    const newState = !article.liked
    article.liked = newState
    if (newState) article.disliked = false

    await sendRating(article.id, newState ? true : null)
}

async function toggleDislike(article) {
    const newState = !article.disliked
    article.disliked = newState
    if (newState) article.liked = false

    await sendRating(article.id, newState ? false : null)
}

async function sendRating(articleId, rating) {
    try {
        const { data } = await axios.post('articles/rate', { // note web route path
            article_id: articleId,
            rating: rating === null ? false : rating,
        });
        console.log('Rating response:', data);
    } catch (e) {
        console.error('Rating failed', e);
    }
}

async function toggleFlag(article, flagName) {
    try {
        const { data } = await axios.post('/api/flags/toggle', {
            article_id: article.id,
            flag: flagName
        });

        article.bookmarked = data.is_bookmarked;
        article.readLater = data.is_read_later;
        article.archived = data.is_archived;
    } catch (err) {
        console.error('Failed to toggle flag:', err);
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="px-14 pt-10 bg-gray-50 dark:bg-neutral-darkBg min-h-screen">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-neutral-darkText mb-6">
                Bookmarked Articles
            </h2>

            <div class="grid md:grid-cols-3 gap-6">
                <NewsCard
                    v-for="article in articles"
                    :key="article.id"
                    :article="article"
                    :onToggleLike="toggleLike"
                    :onToggleDislike="toggleDislike"
                    :onToggleFlag="toggleFlag"
                />
            </div>

            <Pagination
                :total-pages="totalPages"
                :current-page="page"
                @update:page="page = $event"
            />
        </div>
    </AuthenticatedLayout>
</template>
