<script setup>
import {ref, computed, onMounted, watch} from 'vue'
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {
    ChatBubbleLeftEllipsisIcon,
    ClockIcon,
    HandThumbUpIcon,
    HandThumbDownIcon,
    BookmarkIcon,
    ArchiveBoxIcon,
} from '@heroicons/vue/24/outline'
import {Link, usePage} from "@inertiajs/vue3";
import Pagination from "@/Components/Pagination.vue";
import NewsCard from "@/Components/NewsCard.vue";

const articles = ref([])
const nextArticles = ref([])
const tags = ref([])
const selectedTag = ref('')
const page = ref(1)
const pagination = ref({})

function toggleTag(tagName) {
    selectedTag.value = selectedTag.value === tagName ? '' : tagName
    page.value = 1
    fetchNextArticles()
}


async function fetchTopArticles() {
    await axios.get('/sanctum/csrf-cookie');
    const res = await axios.get('/api/articles')
    const data = await res.data
    console.log("Articles", data)

    articles.value = data.map(a => ({
        ...a,
        liked: a.user_rating?.rating === 1,
        disliked: a.user_rating?.rating === 0,
        bookmarked: a.user_flag?.is_bookmarked || false,
        readLater: a.user_flag?.is_read_later || false,
        archived: a.user_flag?.is_archived || false,
    }))
}

async function fetchNextArticles() {
    await axios.get('/sanctum/csrf-cookie');
    const url = new URL('/api/articles/paginate', window.location.origin)
    if (selectedTag.value) url.searchParams.set('tag', selectedTag.value)
    url.searchParams.set('page', page.value)

    const res = await axios.get(url)
    const data = await res.data

    console.log("Next article", data)

    nextArticles.value = data.data.map(a => ({
        ...a,
        liked: a.user_rating?.rating === 1,
        disliked: a.user_rating?.rating === 0,
        bookmarked: a.user_flag?.is_bookmarked || false,
        readLater: a.user_flag?.is_read_later || false,
        archived: a.user_flag?.is_archived || false,
    }))
    pagination.value = data
}

async function fetchTags() {
    const res = await fetch('/api/tags')
    tags.value = await res.json()
}


onMounted(async () => {
    await Promise.all([fetchTopArticles(), fetchNextArticles(), fetchTags()])
})

watch(page, () => {
    fetchNextArticles()
})


const totalPages = computed(() => pagination.value.last_page || 1)

const filterTag = ref(null)



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
            <div class="flex items-center justify-between pb-10">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-neutral-darkText">Top News</h2>
                <a
                    href="#"
                    class="flex items-center text-sm text-brand-secondary font-medium hover:underline"
                >
                    <ClockIcon class="w-4 h-4 mr-1" />
                    Read Later List
                </a>
            </div>
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



            <section class="mt-14">
                <!-- Filter Section -->
                <div class="flex flex-wrap items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-neutral-darkText">All News</h2>

                    <div class="flex items-center gap-3 flex-wrap">
                        <button
                            v-for="tag in tags"
                            :key="tag.id"
                            @click="toggleTag(tag.name)"
                            class="px-3 py-1.5 text-sm rounded-full border transition-colors duration-200"
                            :class="selectedTag === tag.name
    ? 'bg-brand-secondary text-white border-brand-secondary'
    : 'bg-white dark:bg-brand-primary/10 text-gray-700 dark:text-gray-400 border-gray-300 dark:border-bg-brand-primary/10 hover:bg-gray-50 dark:hover:bg-brand-primary/30'"
                        >
                            {{ tag.name }}
                        </button>
                    </div>
                </div>

                <!-- News Cards Grid -->
                <div class="grid md:grid-cols-3 gap-6">
                    <NewsCard
                        v-for="article in nextArticles"
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

            </section>
        </div>
    </AuthenticatedLayout>
</template>


<style scoped>

</style>
