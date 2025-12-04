<script setup>
import { ref } from 'vue'
import {
    HandThumbUpIcon,
    HandThumbDownIcon,
    BookmarkIcon,
    ClockIcon,
    ArchiveBoxIcon,
    ChatBubbleLeftEllipsisIcon
} from '@heroicons/vue/24/outline'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    article: Object,
    onToggleLike: Function,
    onToggleDislike: Function,
    onToggleFlag: Function
})
</script>

<template>
    <div class="bg-white dark:bg-brand-primary/10 rounded-2xl shadow-sm overflow-hidden hover:shadow-md transition-all duration-300">
        <div class="relative h-44">
            <img :src="article.image_url || placeholder" alt="News image" class="w-full h-full object-cover"/>
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
            <div class="absolute bottom-3 left-3 right-3 text-white">
                <a :href="article.source_url" class="text-sm font-bold leading-tight">{{ article.title }}</a>
                <p class="text-xs text-gray-200 mt-1">{{ article.source }}</p>
            </div>
            <span class="absolute top-3 right-3 bg-white/90 text-[11px] font-semibold px-3 py-1 rounded-md text-brand-secondary uppercase tracking-wide">
        {{ article.tags[0]?.name || 'Uncategorized' }}
      </span>
        </div>
        <div class="p-4 flex flex-col justify-between h-36">
            <p class="text-xs text-gray-700 dark:text-gray-400 mb-4">
                <span class="font-semibold text-gray-800 dark:text-neutral-darkText">Why you should care:</span>
                {{ article.title }}
            </p>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3 text-gray-500">
                    <button @click="onToggleLike(article)" class="hover:text-brand-secondary">
                        <HandThumbUpIcon :class="['w-4 h-4', article.liked ? 'text-brand-secondary' : 'text-gray-400']"/>
                    </button>
                    <button @click="onToggleDislike(article)" class="hover:text-brand-secondary">
                        <HandThumbDownIcon :class="['w-4 h-4', article.disliked ? 'text-brand-secondary' : 'text-gray-400']"/>
                    </button>
                    <button @click="() => onToggleFlag(article, 'is_bookmarked')" class="hover:text-brand-secondary">
                        <BookmarkIcon :class="['w-4 h-4', article.bookmarked ? 'text-brand-secondary' : 'text-gray-400']"/>
                    </button>
                    <button @click="() => onToggleFlag(article, 'is_read_later')" class="hover:text-brand-secondary">
                        <ClockIcon :class="['w-4 h-4', article.readLater ? 'text-brand-secondary' : 'text-gray-400']"/>
                    </button>
                    <button @click="() => onToggleFlag(article, 'is_archived')" class="hover:text-brand-secondary">
                        <ArchiveBoxIcon :class="['w-4 h-4', article.archived ? 'text-brand-secondary' : 'text-gray-400']"/>
                    </button>
                </div>
                <button class="flex items-center text-xs bg-brand-secondary text-white px-3 py-1.5 rounded-full hover:bg-brand-primary transition-colors duration-300">
                    <Link :href="route('chat.chat', article.id)" class="flex items-center">
                        <ChatBubbleLeftEllipsisIcon class="w-4 h-4 mr-1"/> Chat
                    </Link>
                </button>
            </div>
        </div>
    </div>
</template>
