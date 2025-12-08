<script setup>
import { ref, computed, onMounted, nextTick } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import axios from 'axios';
import {
    InformationCircleIcon,
    GlobeAltIcon,
    ArrowUpRightIcon,
    ChatBubbleLeftRightIcon,
    ChevronDownIcon,
    SparklesIcon,
    PaperAirplaneIcon
} from '@heroicons/vue/24/outline';
import { marked } from "marked";
import { router } from '@inertiajs/vue3';

const API_URL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000';

const search = ref("");
const newMessage = ref("");
const useLiveSearch = ref(false);
const messagesEnd = ref(null);
const showScrollButton = ref(false);
const textareaRef = ref(null);

function scrollToBottom() {
    nextTick(() => {
        if (messagesEnd.value) {
            messagesEnd.value.scrollIntoView({ behavior: 'smooth' });
            showScrollButton.value = false;
        }
    });
}

function handleScroll(event) {
    const container = event.target;
    const isNearBottom = container.scrollHeight - container.scrollTop - container.clientHeight < 100;
    showScrollButton.value = !isNearBottom;
}

function autoGrow(event) {
    const el = event.target
    const minHeight = 56 // Minimum height
    const maxHeight = 200 // Maximum height
    el.style.height = 'auto'
    const newHeight = Math.min(Math.max(el.scrollHeight, minHeight), maxHeight)
    el.style.height = newHeight + 'px'
}

function adjustButtonPositions() {
    nextTick(() => {
        if (textareaRef.value) {
            const textareaHeight = textareaRef.value.clientHeight
            const buttons = document.querySelectorAll('.input-button')
            buttons.forEach(button => {
                button.style.bottom = Math.max(12, textareaHeight - 32) + 'px'
            })
        }
    })
}

function renderMarkdown(text) {
    if (!text) return "";
    return marked.parse(text, {
        breaks: true
    });
}

function selectArticle(chat) {
    router.visit(`/chat/${chat.article.id}`);
}

const props = defineProps({
    article: Object,
    chats: Array,
    messages: Array,
    conversation: Object,
})

console.log("chats", props.chats);
console.log("Messages", props.messages);

const selectedArticle = ref(props.article);
const articles = ref(props.chats || []);
const messages = ref(props.messages);
const conversationId = ref(props.conversation?.id);

console.log("article", selectedArticle)

const filteredArticles = computed(() =>
    articles.value.filter(a =>
        a.article?.title?.toLowerCase().includes(search.value.toLowerCase())
    )
);

const selectedArticleMessages = computed(() => messages.value);

const groupedChats = computed(() => {
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const sevenDaysAgo = new Date(today);
    sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
    const thirtyDaysAgo = new Date(today);
    thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);

    const groups = {
        'Today': [],
        'Last 7 days': [],
        'Last 30 days': [],
        'Older': []
    };

    filteredArticles.value.forEach(chat => {
        const chatDate = new Date(chat.last_message_at || chat.created_at);
        const lastMessageDate = new Date(chat.last_message_at || chat.created_at);

        // If last message is from today, put in Today group regardless of conversation start
        if (lastMessageDate >= today) {
            groups['Today'].push(chat);
        } else if (chatDate >= sevenDaysAgo) {
            groups['Last 7 days'].push(chat);
        } else if (chatDate >= thirtyDaysAgo) {
            groups['Last 30 days'].push(chat);
        } else {
            groups['Older'].push(chat);
        }
    });

    return groups;
});

async function saveToDB(conversationId, sender, text) {
    if (!conversationId || !text) return null;

    try {
        const res = await axios.post('/message/store', {
            conversation_id: conversationId,
            sender: sender === 'me' ? 'user' : 'ai',
            message: text
        });
        return res.data; // returns the saved message including its DB id
    } catch (err) {
        console.error("Failed to save message:", err);
        return null;
    }
}

async function sendMessage() {
    if (!newMessage.value || !selectedArticle.value) return;

    const userText = newMessage.value;
    const userMsg = {
        id: Date.now(),
        message: userText,
        sender: "user",
        live: useLiveSearch.value
    };

    messages.value.push(userMsg);
    nextTick(() => {
        scrollToBottom();
    });

    newMessage.value = "";
    useLiveSearch.value = false;

    // Reset textarea height
    nextTick(() => {
        if (textareaRef.value) {
            textareaRef.value.style.height = '56px'
            adjustButtonPositions()
        }
    });

    // Save user message to DB
    const savedUser = await saveToDB(conversationId.value, "me", userText);
    if (savedUser) {
        const index = messages.value.findIndex(m => m.id === userMsg.id)
        if (index !== -1) {
            messages.value[index].id = savedUser.id
        }
    }

    try {
        let cleanContent = selectedArticle.value.content || selectedArticle.value.summary || "";

        if (cleanContent.includes('<') || cleanContent.includes('window.addEventListener')) {
            try {
                // Use DOMParser to properly parse HTML
                const parser = new DOMParser();
                const doc = parser.parseFromString(cleanContent, 'text/html');

                // Remove script and style tags
                const scripts = doc.querySelectorAll('script, style');
                scripts.forEach(script => script.remove());

                cleanContent = doc.body.textContent || doc.body.innerText || "";

                // Clean up whitespace
                cleanContent = cleanContent
                    .replace(/\s+/g, ' ')
                    .trim();
            } catch (e) {
                console.error("Error parsing HTML:", e);
            }
        }

        // Limit length to prevent huge payloads
        const maxLength = 8000;
        if (cleanContent.length > maxLength) {
            cleanContent = cleanContent.substring(0, maxLength) + "...";
        }

        const payload = {
            article_title: selectedArticle.value.title,
            article_content: cleanContent,
            question: userText,
            useLiveSearch: userMsg.live,
            history: messages.value
                .slice(0, -1)
                .map(m => ({
                    role: m.sender === "user" ? "user" : "assistant",
                    content: m.message
                })),
        };

        console.log("Payload", payload);

        const res = await axios.post(`${API_URL}/chat`, payload, {
            headers: { "Content-Type": "application/json" }
        });

        console.log("Data: ", res.data)

        const aiText = res.data.answer;
        const aiMsg = {
            id: Date.now() + 1,
            message: aiText,
            sender: "ai"
        };

        messages.value.push(aiMsg);
        nextTick(() => {
            scrollToBottom();
        });

        // Save AI response to DB
        const savedAI = await saveToDB(conversationId.value, "them", aiText);
        if (savedAI) {
            aiMsg.id = savedAI.id;
        }
    } catch (err) {
        console.error("AI error:", err);
        messages.value.push({
            id: Date.now() + 2,
            message: `Error: ${err.response?.data?.detail || err.message || "Please try again"}`,
            sender: "ai"
        });
        nextTick(() => {
            scrollToBottom();
        });
    }
}

onMounted(() => {
    scrollToBottom();
    adjustButtonPositions()
});
</script>

<template>
    <AuthenticatedLayout>
        <div class="flex h-screen bg-gray-50 dark:bg-neutral-darkBg">
            <!-- Sidebar -->
            <aside class="w-60 border-r dark:border-r-gray-700 bg-white dark:bg-neutral-darkBg flex flex-col">
                <div class="p-4 font-semibold text-brand-primary text-lg">Chats</div>
                <div class="px-4 pb-2">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search Chats"
                        class="w-full text-sm dark:text-gray-300 px-3 py-2 rounded-xl border dark:border-brand-primary/20 focus:outline-none focus:ring-2 focus:ring-brand-primary dark:focus:ring-brand-primary dark:bg-brand-primary/10"
                    />
                </div>

                <!-- Chat List -->
                <div class="flex-1 overflow-y-auto px-2 pb-4">
                    <template v-for="(groupChats, groupName) in groupedChats" :key="groupName">
                        <template v-if="groupChats.length > 0">
                            <div class="px-4 py-2">
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">
                                    {{ groupName }}
                                </div>
                                <div class="space-y-1">
                                    <div
                                        v-for="chat in groupChats"
                                        :key="chat.id"
                                        @click="selectArticle(chat)"
                                        class="flex items-center justify-between px-3 py-2 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-brand-primary/10 transition-all group"
                                        :class="selectedArticle && selectedArticle.id === chat.article.id ? 'bg-gray-100 dark:bg-brand-primary/10' : ''"
                                    >
                                        <div class="flex-1 min-w-0 overflow-hidden">
                                            <div class="text-sm text-gray-600 dark:text-gray-400 truncate group-hover:text-gray-800 dark:group-hover:text-gray-300"
                                                 :title="chat.article?.title">
                                                {{ chat.article?.title }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </template>

                    <!-- Empty State -->
                    <div v-if="filteredArticles.length === 0" class="px-4 py-8 text-center">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 dark:bg-brand-primary/10 flex items-center justify-center">
                            <ChatBubbleLeftRightIcon class="w-6 h-6 text-gray-400 dark:text-gray-500" />
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">No conversations found</p>
                    </div>
                </div>
            </aside>

            <!-- Chat Area -->
            <main class="flex-1 flex flex-col">
                <!-- Chat header -->
                <div class="flex items-center gap-3 p-4 border-b dark:border-b-gray-700 bg-white dark:bg-neutral-darkBg">
                    <div v-if="selectedArticle" class="flex-1 min-w-0">
                        <div class="font-semibold text-brand-primary text-base truncate"
                             :title="selectedArticle.title">
                            {{ selectedArticle.title }}
                        </div>
                        <div class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400 mt-1">
                            <SparklesIcon class="w-3.5 h-3.5" />
                            <span>Chat with AI on this article</span>
                        </div>
                    </div>
                    <div v-else class="text-gray-400">Select an article to chat</div>
                </div>

                <!-- Messages Container -->
                <div class="flex-1 overflow-y-auto p-6" @scroll="handleScroll">
                    <div class="mx-auto" style="width: 42rem; max-width: 42rem;">
                        <!-- Empty Conversation State -->
                        <div v-if="messages.length === 0 && selectedArticle"
                             class="flex flex-col items-center justify-center py-16">
                            <div class="w-16 h-16 mb-4 rounded-full bg-gray-100 dark:bg-brand-primary/10 flex items-center justify-center">
                                <ChatBubbleLeftRightIcon class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                            </div>
                            <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Start a conversation
                            </h3>
                            <p class="text-gray-500 dark:text-gray-400 text-center max-w-md mb-6">
                                Ask questions about this article or request summaries. The AI will help you understand the content better.
                            </p>
                        </div>

                        <!-- Messages -->
                        <div class="space-y-4">
                            <div
                                v-for="msg in messages"
                                :key="msg.id"
                                class="flex"
                                :class="msg.sender === 'user' ? 'justify-end' : 'justify-start'"
                            >
                                <div
                                    :class="[
                                        'px-4 py-3 rounded-2xl text-sm prose prose-sm',
                                        msg.sender === 'user'
                                            ? 'bg-brand-primary dark:bg-brand-primary/50 text-white prose-invert'
                                            : 'bg-white dark:bg-brand-primary/10 border dark:border-brand-primary/10 text-gray-900 dark:text-gray-400'
                                    ]"
                                    :style="msg.sender === 'user' ? 'max-width: 28rem;' : 'max-width: 42rem;'"
                                    v-html="renderMarkdown(msg.message)"
                                ></div>
                            </div>
                        </div>

                        <div ref="messagesEnd" />
                    </div>

                    <!-- Scroll to Bottom Button -->
                    <button
                        v-if="showScrollButton"
                        @click="scrollToBottom"
                        class="fixed right-8 bottom-24 p-2 rounded-full bg-gray-800/80 dark:bg-gray-700/80 backdrop-blur-sm text-white shadow-lg hover:bg-gray-900/90 dark:hover:bg-gray-600/90 transition-all duration-200 z-10 border border-gray-700/20 dark:border-gray-600/20"
                    >
                        <ChevronDownIcon class="w-5 h-5" />
                    </button>
                </div>

                <!-- Input -->
                <div class="p-4 bg-white dark:bg-neutral-darkBg">
                    <div class="mx-auto" style="width: 42rem; max-width: 42rem;">
                        <div class="relative bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus-within:border-brand-primary/50 dark:focus-within:border-brand-primary/50 focus-within:ring-2 focus-within:ring-brand-primary/30 dark:focus-within:ring-brand-primary/50 transition-all duration-200 overflow-hidden">
                            <!-- Textarea - occupies most of the space -->
                            <textarea
                                ref="textareaRef"
                                v-model="newMessage"
                                @input="autoGrow"
                                @keydown.enter.exact.prevent="sendMessage"
                                rows="1"
                                placeholder="Type your message..."
                                class="w-full resize-none px-4 py-3 text-sm bg-transparent text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-0 overflow-hidden min-h-[56px] max-h-[200px] pb-12"
                                style="width: 42rem; max-width: 42rem;"
                            ></textarea>

                            <div class="absolute bottom-0 left-0 right-0 px-4 py-2 bg-gradient-to-t from-white/80 to-transparent dark:from-gray-900/80 dark:to-transparent backdrop-blur-sm">
                                <div class="flex items-center justify-between">
                                    <button
                                        @click="useLiveSearch = !useLiveSearch"
                                        class="flex items-center gap-1 px-3 py-1.5 rounded-lg transition-colors"
                                        :class="useLiveSearch
                            ? 'bg-brand-primary/10 dark:bg-brand-primary/20 text-brand-primary dark:text-brand-primary/90 border border-brand-primary/20 dark:border-brand-primary/30'
                            : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'"
                                    >
                                        <GlobeAltIcon class="w-4 h-4" />
                                        <span class="text-xs font-medium select-none">
                            {{ useLiveSearch ? 'Live' : 'Web' }}
                        </span>
                                    </button>

                                    <button
                                        @click="sendMessage"
                                        :disabled="!newMessage.trim().length"
                                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg transition-colors"
                                        :class="newMessage.trim().length
                            ? 'bg-brand-secondary text-white hover:bg-brand-secondary/90 shadow-sm'
                            : 'bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed'"
                                    >
                                        <span class="text-xs font-medium">Send</span>
                                        <PaperAirplaneIcon class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Live search indicator -->
                        <div v-if="useLiveSearch" class="flex items-center gap-1.5 mt-2 justify-end">
                            <div class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></div>
                            <span class="text-xs text-green-600 dark:text-green-400 font-medium">
                Live search is active
            </span>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Custom scrollbar for sidebar */
aside > div:last-child::-webkit-scrollbar {
    width: 4px;
}

aside > div:last-child::-webkit-scrollbar-track {
    background: transparent;
}

aside > div:last-child::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 2px;
}

.dark aside > div:last-child::-webkit-scrollbar-thumb {
    background: #4b5563;
}

/* Main chat area scrollbar - ChatGPT/DeepSeek style */
main > div:first-of-type::-webkit-scrollbar {
    width: 8px;
}

main > div:first-of-type::-webkit-scrollbar-track {
    background: transparent;
    margin: 4px 0;
}

main > div:first-of-type::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.3);
    border-radius: 4px;
    border: 2px solid transparent;
    background-clip: padding-box;
}

main > div:first-of-type::-webkit-scrollbar-thumb:hover {
    background: rgba(156, 163, 175, 0.5);
    border-radius: 4px;
    border: 2px solid transparent;
    background-clip: padding-box;
}

.dark main > div:first-of-type::-webkit-scrollbar-thumb {
    background: rgba(75, 85, 99, 0.4);
    border-radius: 4px;
    border: 2px solid transparent;
    background-clip: padding-box;
}

.dark main > div:first-of-type::-webkit-scrollbar-thumb:hover {
    background: rgba(75, 85, 99, 0.6);
    border-radius: 4px;
    border: 2px solid transparent;
    background-clip: padding-box;
}

/* FireFox support */
main > div:first-of-type {
    scrollbar-width: thin;
    scrollbar-color: rgba(156, 163, 175, 0.3) transparent;
}

.dark main > div:first-of-type {
    scrollbar-color: rgba(75, 85, 99, 0.4) transparent;
}

/* Textarea styling */
textarea {
    line-height: 1.5;
    padding-top: 16px;
    padding-bottom: 16px;
}

textarea::-webkit-scrollbar {
    width: 6px;
}

textarea::-webkit-scrollbar-track {
    background: transparent;
}

textarea::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.3);
    border-radius: 3px;
}
textarea {
    transition: height 0.2s ease-out;
}


.pb-12 {
    padding-bottom: 3rem;
}

.backdrop-blur-sm {
    backdrop-filter: blur(2px);
}
</style>
