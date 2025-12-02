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
    const res = await fetch('/api/articles')
    const data = await res.json()
    console.log("Articles", data)

    articles.value = data.map(a => ({
        ...a,
        liked: a.user_rating?.rating === true,
        disliked: a.user_rating?.rating === false,
    }))
}

async function fetchNextArticles() {
    const url = new URL('/api/articles/paginate', window.location.origin)
    if (selectedTag.value) url.searchParams.set('tag', selectedTag.value)
    url.searchParams.set('page', page.value)

    const res = await fetch(url)
    const data = await res.json()

    console.log("Next article", data)

    nextArticles.value = data.data.map(a => ({
        ...a,
        liked: a.user_rating?.rating === true,
        disliked: a.user_rating?.rating === false,
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



</script>
<template>
    <AuthenticatedLayout>
        <div class="px-14 pt-10 bg-gray-50 min-h-screen">
            <div class="flex items-center justify-between pb-10">
                <h2 class="text-lg font-semibold text-gray-800">Trending News</h2>
                <a
                    href="#"
                    class="flex items-center text-sm text-brand-secondary font-medium hover:underline"
                >
                    <ClockIcon class="w-4 h-4 mr-1" />
                    Read Later List
                </a>
            </div>
            <div class="grid md:grid-cols-3 gap-6">
                <div
                    v-for="(article) in articles"
                    :key="article.id"
                    class="bg-white rounded-2xl shadow-sm overflow-hidden hover:shadow-md transition-all duration-300"
                >
                    <!-- Image + Overlay -->
                    <div class="relative h-44">
                        <img
                            :src="article.image_url ? article.image_url: 'https://www.muva.de/fileadmin/_processed_/f/b/csm_AdobeStock-201227953-News_4f340aa6d7.jpg'"
                            alt="News image"
                            class="w-full h-full object-cover"
                        />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"
                        ></div>
                        <div class="absolute bottom-3 left-3 right-3 text-white">
                            <h3 class="text-sm font-semibold leading-tight">
                                {{ article.title }}
                            </h3>
                            <p class="text-xs text-gray-200 mt-1">{{ article.source }}</p>
                        </div>
                        <span
                            class="absolute top-3 right-3 bg-white/90 text-[11px] font-semibold px-3 py-1 rounded-md text-brand-secondary uppercase tracking-wide"
                        >
            {{ article.tags[0]?.name || 'Uncategorized' }}
          </span>
                    </div>


                    <!-- Bottom content -->
                    <div class="p-4 flex flex-col justify-between h-36">
                        <p class="text-xs text-gray-700 mb-4">
                            <span class="font-semibold text-gray-800">Why you should care:</span>
                            {{ article.title }}
                        </p>

                        <div class="flex justify-between items-center">
                            <a
                                :href="article.source_url"
                                target="_blank"
                                class="text-xs text-gray-500 hover:text-brand-secondary font-medium"
                            >Read full article</a
                            >
                            <button
                                class="flex items-center text-xs bg-brand-secondary text-white px-3 py-1.5 rounded-full hover:bg-brand-primary transition-colors duration-300"
                            >
                                <Link :href="route('chat.chat', article.id)" class="flex items-center">
                                    <ChatBubbleLeftEllipsisIcon class="w-4 h-4 mr-1" />
                                    Chat
                                </Link>
                            </button>
                        </div>
                    </div>
                </div>
            </div>



            <section class="mt-14">
                <!-- Filter Section -->
                <div class="flex flex-wrap items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-800">All News</h2>

                    <div class="flex items-center gap-3 flex-wrap">
                        <button
                            v-for="tag in tags"
                            :key="tag.id"
                            @click="toggleTag(tag.name)"
                            class="px-3 py-1.5 text-sm rounded-full border transition-colors duration-200"
                            :class="selectedTag === tag.name
    ? 'bg-brand-secondary text-white border-brand-secondary'
    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                        >
                            {{ tag.name }}
                        </button>
                    </div>
                </div>

                <!-- News Cards Grid -->
                <div class="grid md:grid-cols-3 gap-6">
                    <div
                        v-for="(article) in nextArticles"
                        :key="article.id"
                        class="bg-white rounded-2xl shadow-sm overflow-hidden hover:shadow-md transition-all duration-300"
                    >
                        <!-- Image & Overlay -->
                        <div class="relative h-44">
                            <img
                                :src="article.image_url? article.image_url: 'https://cdn.create.vista.com/api/media/small/251043176/stock-photo-selective-focus-laptop-blank-screen-business-newspapers-glasses-pen-paper'"
                                alt="News image"
                                class="w-full h-full object-cover"
                            />
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"
                            ></div>
                            <div class="absolute bottom-3 left-3 right-3 text-white">
                                <a class="text-sm font-bold leading-tight" :href="article.source_url">
                                    {{ article.title }}
                                </a>
                                <p class="text-xs text-gray-200 mt-1">{{ article.source }}</p>
                            </div>
                            <span
                                class="absolute top-3 right-3 bg-white/90 text-[11px] font-semibold px-3 py-1 rounded-md text-brand-secondary uppercase tracking-wide"
                            >
              {{ article.tags[0]?.name || 'Uncategorized' }}
          </span>
                        </div>

                        <!-- Bottom Content -->
                        <div class="p-4 flex flex-col justify-between h-44">
                            <p class="text-xs text-gray-700 mb-3">
                                <span class="font-semibold text-gray-800">Why you should care:</span>
                                {{ article.title }}
                            </p>

                            <!-- Action Icons -->
                            <div class="flex justify-between items-center mt-auto">
                                <div class="flex items-center gap-3 text-gray-500">
                                    <button
                                        @click="toggleLike(article)"
                                        class="hover:text-brand-secondary"
                                    >
                                        <HandThumbUpIcon
                                            :class="[
                    'w-4 h-4',
                    article.liked ? 'text-brand-secondary' : 'text-gray-400',
                  ]"
                                        />
                                    </button>
                                    <button
                                        @click="toggleDislike(article)"
                                        class="hover:text-brand-secondary"
                                    >
                                        <HandThumbDownIcon
                                            :class="[
                    'w-4 h-4',
                    article.disliked ? 'text-brand-secondary' : 'text-gray-400',
                  ]"
                                        />
                                    </button>
                                    <button @click="article.readLater = !article.readLater" class="hover:text-brand-secondary">
                                        <ClockIcon
                                            :class="[
                    'w-4 h-4',
                    article.readLater ? 'text-brand-secondary' : 'text-gray-400',
                  ]"
                                        />
                                    </button>
                                    <button @click="article.bookmarked = !article.bookmarked" class="hover:text-brand-secondary">
                                        <BookmarkIcon
                                            :class="[
                    'w-4 h-4',
                    article.bookmarked ? 'text-brand-secondary' : 'text-gray-400',
                  ]"
                                        />
                                    </button>
                                    <button @click="article.archived = !article.archived" class="hover:text-brand-secondary">
                                        <ArchiveBoxIcon
                                            :class="[
                    'w-4 h-4',
                    article.archived ? 'text-brand-secondary' : 'text-gray-400',
                  ]"
                                        />
                                    </button>
                                </div>
                                <button
                                    class="flex items-center text-xs bg-brand-secondary text-white px-3 py-1.5 rounded-full hover:bg-brand-primary transition-colors duration-300"
                                >
                                    <Link :href="route('chat.chat', article.id)" class="flex items-center">
                                        <ChatBubbleLeftEllipsisIcon class="w-4 h-4 mr-1" />
                                        Chat
                                    </Link>
                                </button>
                            </div>
                        </div>
                    </div>
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
.text-brand-secondary {
    color: #ff644f;
}
.bg-brand-secondary {
    background-color: #ff644f;
}
.hover\:bg-brand-primary:hover {
    background-color: #208888;
}
</style>
