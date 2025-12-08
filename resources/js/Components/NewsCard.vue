<script setup>
import { ref, computed } from 'vue'
import {
    HandThumbUpIcon,
    HandThumbDownIcon,
    BookmarkIcon,
    ClockIcon,
    ArchiveBoxIcon,
    ChatBubbleLeftEllipsisIcon,
    CalendarDaysIcon,
    LightBulbIcon,
    ChevronRightIcon,
    SparklesIcon
} from '@heroicons/vue/24/outline'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    article: Object,
    onToggleLike: Function,
    onToggleDislike: Function,
    onToggleFlag: Function,
    getWhyCare: Function
})

const placeholder = ref('https://images.pexels.com/photos/4057663/pexels-photo-4057663.jpeg')

const conversationLogo = ref('https://upload.wikimedia.org/wikipedia/commons/8/85/The_Conversation_logo.png')
const nyasaLogo = ref('https://www.nyasatimes.com/wp-content/uploads/2024/07/LOGO-1-2048x462.png') // Nyasa Times logo


// Check if source is The Conversation
const isConversationSource = computed(() => {
    return props.article.source &&
        (props.article.source.toLowerCase().includes('conversation') ||
            props.article.source.toLowerCase().includes('the conversation'))
})

const isNyasaSource = computed(() => {
    if (!props.article.source) return false;
    const sourceLower = props.article.source.toLowerCase();
    return sourceLower.includes('nyasa') ||
        sourceLower.includes('nyasatimes') ||
        sourceLower.includes('nyasa times') ||
        sourceLower.includes('malawi nyasa');
});

const isBBCSource = computed(() => {
    if (!props.article.source) return false;
    const sourceLower = props.article.source.toLowerCase();
    return sourceLower.includes('bbc') ||
        sourceLower.includes('british broadcasting corporation');
});

const formattedDate = computed(() => {
    if (!props.article.date) return '';

    const date = new Date(props.article.date);
    const now = new Date();
    const diffTime = Math.abs(now - date);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays === 0) return 'Today';
    if (diffDays === 1) return 'Yesterday';
    if (diffDays <= 7) return `${diffDays} days ago`;

    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
    });
})

const loading = ref(false)
const whyCareText = ref(null)

async function showWhyCare() {
    loading.value = true
    const reason = await props.getWhyCare(props.article.title)
    whyCareText.value = reason
    loading.value = false
}
</script>

<template>
    <div class="group bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden hover:border-brand-primary/30 dark:hover:border-brand-primary/50 transition-all duration-300 hover:shadow-sm">
        <!-- Image Section -->
        <div class="relative aspect-[16/10] overflow-hidden bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-900">
            <img
                :src="article.image_url || placeholder"
                :alt="article.title"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
            />

            <!-- Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>

            <!-- Top Badges -->
            <div class="absolute top-3 left-3 right-3 flex items-start justify-between">
                <!-- Source Badge -->
                <div class="flex items-center gap-1.5 bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm px-2.5 py-1 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="w-1.5 h-1.5 rounded-full bg-brand-primary"></div>

                    <!-- The Conversation Logo -->
                    <div v-if="isConversationSource" class="flex items-center gap-1.5">
                        <div class="bg-white p-0.5 rounded-sm flex items-center justify-center">
                            <img
                                :src="conversationLogo"
                                alt="The Conversation"
                                class="h-3.5 w-auto object-contain"
                            />
                        </div>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300 sr-only">
                            The Conversation
                        </span>
                    </div>

                    <!-- Nyasa Times Logo -->
                    <span v-else-if="isNyasaSource" class="text-xs font-medium text-gray-700 dark:text-gray-300">
                        Nyasa Times
                    </span>

                    <!-- Default Text Source -->
                    <span v-else class="text-xs font-medium text-gray-700 dark:text-gray-300">
                        {{ article.source }}
                    </span>
                </div>

                <!-- Date Badge -->
                <div class="flex items-center gap-1 bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm px-2.5 py-1 rounded-lg border border-gray-200 dark:border-gray-700">
                    <CalendarDaysIcon class="w-3 h-3 text-gray-600 dark:text-gray-400" />
                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                        {{ formattedDate }}
                    </span>
                </div>
            </div>

            <!-- Title Overlay -->
            <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black/70 via-black/30 to-transparent">
                <h3 class="text-sm font-semibold text-white line-clamp-2 leading-snug mb-1">
                    <a :href="article.source_url" target="_blank" class="hover:text-brand-primary/80 transition-colors">
                        {{ article.title }}
                    </a>
                </h3>
                <!-- Category Tag -->
                <div class="flex items-center gap-1">
                    <span class="text-[10px] font-medium text-brand-secondary bg-white/20 backdrop-blur-sm px-2 py-0.5 rounded-md uppercase tracking-wider">
                        {{ article.tags[0]?.name || 'Uncategorized' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Content Section -->
        <div class="p-4">
            <!-- Why This Matters Section -->
            <div class="mb-4">
                <div v-if="!whyCareText">
                    <button
                        @click="showWhyCare"
                        :disabled="loading"
                        :class="[
                            'flex items-center justify-between w-full px-3 py-2.5 rounded-lg border transition-all duration-200',
                            loading
                                ? 'bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 cursor-wait'
                                : 'bg-gray-50 dark:bg-gray-800 border-brand-primary/20 dark:border-brand-primary/30 hover:border-brand-primary/50 hover:bg-brand-primary/5 dark:hover:bg-brand-primary/10'
                        ]"
                    >
                        <div class="flex items-center gap-2">
                            <div class="p-1 rounded-md bg-brand-primary">
                                <SparklesIcon class="w-3.5 h-3.5 text-white" />
                            </div>
                            <div class="text-left">
                                <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Why this matters
                                </span>
                                <div v-if="loading" class="text-[10px] text-brand-primary dark:text-brand-primary/80 mt-0.5">
                                    Thinking...
                                </div>
                                <div v-else class="text-[10px] text-brand-primary dark:text-brand-primary/80 mt-0.5">
                                    Click for AI insights
                                </div>
                            </div>
                        </div>
                        <ChevronRightIcon class="w-4 h-4 text-gray-400 group-hover:text-brand-primary transition-colors" />
                    </button>
                </div>

                <!-- AI Response -->
                <div v-if="whyCareText" class="bg-brand-primary/5 dark:bg-brand-primary/10 border border-brand-primary/20 dark:border-brand-primary/30 rounded-lg p-3">
                    <div class="flex items-start gap-2 mb-2">
                        <div class="p-1 rounded-md bg-brand-primary">
                            <LightBulbIcon class="w-3.5 h-3.5 text-white" />
                        </div>
                        <span class="text-xs font-medium text-brand-primary dark:text-brand-primary/90">AI Insight</span>
                    </div>
                    <p class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed">
                        {{ whyCareText }}
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-800">
                <!-- Interactive Buttons -->
                <div class="flex items-center gap-0.5">
                    <!-- Like -->
                    <button
                        @click="onToggleLike(article)"
                        :class="[
                            'p-2 rounded-lg transition-colors',
                            article.liked
                                ? 'bg-brand-primary/10 text-brand-primary dark:bg-brand-primary/20 dark:text-brand-primary/90'
                                : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
                        ]"
                        title="Like"
                    >
                        <HandThumbUpIcon class="w-4 h-4" />
                    </button>

                    <!-- Dislike -->
                    <button
                        @click="onToggleDislike(article)"
                        :class="[
                            'p-2 rounded-lg transition-colors',
                            article.disliked
                                ? 'bg-brand-secondary/10 text-brand-secondary dark:bg-brand-secondary/20 dark:text-brand-secondary/90'
                                : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
                        ]"
                        title="Dislike"
                    >
                        <HandThumbDownIcon class="w-4 h-4" />
                    </button>

                    <!-- Bookmark -->
                    <button
                        @click="() => onToggleFlag(article, 'is_bookmarked')"
                        :class="[
                            'p-2 rounded-lg transition-colors',
                            article.bookmarked
                                ? 'bg-brand-primary/10 text-brand-primary dark:bg-brand-primary/20 dark:text-brand-primary/90'
                                : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
                        ]"
                        title="Bookmark"
                    >
                        <BookmarkIcon class="w-4 h-4" />
                    </button>

                    <!-- Read Later -->
                    <button
                        @click="() => onToggleFlag(article, 'is_read_later')"
                        :class="[
                            'p-2 rounded-lg transition-colors',
                            article.readLater
                                ? 'bg-brand-primary/10 text-brand-primary dark:bg-brand-primary/20 dark:text-brand-primary/90'
                                : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
                        ]"
                        title="Read Later"
                    >
                        <ClockIcon class="w-4 h-4" />
                    </button>

                    <!-- Archive -->
                    <button
                        @click="() => onToggleFlag(article, 'is_archived')"
                        :class="[
                            'p-2 rounded-lg transition-colors',
                            article.archived
                                ? 'bg-brand-primary/10 text-brand-primary dark:bg-brand-primary/20 dark:text-brand-primary/90'
                                : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
                        ]"
                        title="Archive"
                    >
                        <ArchiveBoxIcon class="w-4 h-4" />
                    </button>
                </div>

                <!-- Chat Button - Hidden for BBC articles -->
                <Link
                    v-if="!isBBCSource"
                    :href="route('chat.chat', article.id)"
                    class="flex items-center gap-2 px-3 py-2 text-xs font-medium bg-brand-secondary text-white rounded-lg hover:bg-brand-secondary/90 transition-all duration-200 shadow-sm hover:shadow"
                >
                    <ChatBubbleLeftEllipsisIcon class="w-3.5 h-3.5" />
                    <span>Chat</span>
                </Link>

                <!-- Empty div to maintain layout when chat button is hidden -->
                <div v-else class="w-20"></div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
}

img {
    will-change: transform;
}

button {
    transition-property: color, background-color, border-color, transform;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

.group:hover .group-hover\:scale-105 {
    transform: scale(1.05);
}
</style>
