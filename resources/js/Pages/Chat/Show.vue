<script setup>
import { ref, computed } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import axios from 'axios';
import { InformationCircleIcon, GlobeAltIcon,  ArrowUpRightIcon } from '@heroicons/vue/24/outline';
import { marked } from "marked";
import { router } from '@inertiajs/vue3';

const API_URL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000';

const search = ref("");
const newMessage = ref("");
const useLiveSearch = ref(false);


function autoGrow(event) {
    const el = event.target
    const minHeight = 50
    el.style.height = 'auto'
    el.style.height = Math.max(el.scrollHeight, minHeight) + 'px'
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

// async function selectArticle(chat) {
//     selectedArticle.value = chat.article;
//
//     const res = await axios.post('/chat/conversation', {
//         article_id: chat.article.id
//     });
//
//     conversationId.value = res.data.id;
//
//
//     const msgsRes = await axios.get(`/conversation/${conversationId.value}/messages`);
//     messages.value[chat.article.id] = msgsRes.data.map(m => ({
//         id: m.id,
//         text: m.message,
//         from: m.sender === 'user' ? 'me' : 'them'
//     }));
//
//     console.log('MSG',msgsRes)
// }

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
    const userMsg = { id: Date.now(),
        message: userText,
        sender: "user",
        live: useLiveSearch.value
    };

    messages.value.push(userMsg);

    newMessage.value = "";
    useLiveSearch.value = false;

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
    }
}


</script>

<template>
    <AuthenticatedLayout>
        <div class="flex h-screen bg-gray-50 px-14 pt-5">
            <!-- Sidebar -->
            <aside class="w-60 border-r bg-white overflow-y-auto">
                <div class="p-4 font-semibold text-brand-primary text-lg">Chats</div>
                <div class="px-2">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search Chats"
                        class="w-full text-sm px-2 py-1 rounded-xl border focus:outline-none focus:ring-2 focus:ring-brand-primary"
                    />
                </div>
                <div class="mt-4">
                    <div
                        v-for="chat in filteredArticles"
                        :key="chat.id"
                        @click="selectArticle(chat)"
                        class="flex items-center gap-3 px-4 py-2 cursor-pointer hover:bg-gray-100 transition-all group"
                        :class="selectedArticle && selectedArticle.id === chat.id ? 'bg-gray-100' : ''"
                    >
                        <div class="flex-1 min-w-0 overflow-hidden">
                            <div class="text-sm text-gray-400 truncate group-hover:text-gray-600"
                                 :title="chat.article?.title">
                                {{ chat.article?.title }}
                            </div>
                        </div>

<!--                        <div class="text-xs text-gray-400 flex-shrink-0 ml-2 group-hover:text-gray-500">-->
<!--                            2:58pm-->
<!--                        </div>-->
                    </div>
                </div>
            </aside>

            <!-- Chat Area -->
            <main class="flex-1 flex flex-col">
                <!-- Chat header -->
                <div class="flex items-center gap-3 p-4 border-b bg-white">
                    <div
                        v-if="selectedArticle"
                        class="font-semibold text-brand-primary text-base truncate"
                        :title="selectedArticle.title"
                    >
                        {{ selectedArticle.title }}
                    </div>
                    <div v-else class="text-gray-400">Select an article to chat</div>
                </div>

                <!-- Messages -->
                <div class="flex-1 overflow-y-auto p-6 space-y-4 ">
                    <div
                        v-for="msg in messages"
                        :key="msg.id"
                        class="flex"
                        :class="msg.sender === 'user' ? 'justify-end' : 'justify-center'"
                    >
                        <div
                            :class="[
            'px-4 py-2 rounded-2xl text-sm prose prose-sm',
            msg.sender === 'user'
                ? 'bg-brand-primary text-white prose-invert max-w-md'
                : 'bg-white border text-gray-900 max-w-2xl'
        ]"
                            v-html="renderMarkdown(msg.message)"
                        ></div>
                    </div>


                </div>

                <!-- Input -->
                <div class="p-4 border-t bg-white flex items-end gap-3 mb-8 justify-center">
                    <div class="relative w-full max-w-2xl">

                        <!-- Auto-growing textarea -->
                        <textarea
                            v-model="newMessage"
                            @input="autoGrow"
                            @keyup.enter="sendMessage"
                            rows="3"
                            placeholder="Write your message..."
                            class="w-full resize-none px-4 py-2 text-sm rounded-xl border
                            focus:outline-none focus:ring-2 focus:ring-brand-primary overflow-hidden"
                        ></textarea>

                        <!-- Live search toggle -->
                        <button
                            @click="useLiveSearch = !useLiveSearch"
                            class="absolute left-4 bottom-4 flex items-center gap-1 px-2 py-1 rounded
                            hover:bg-gray-100 bg-brand-primary/10"
                        >
                            <GlobeAltIcon class="w-5 h-5" :class="useLiveSearch ? 'text-brand-primary' : 'text-gray-400'" />
                            <span class="text-xs font-medium select-none" :class="useLiveSearch ? 'text-brand-primary' : 'text-gray-700'">Live Search</span>
                        </button>

                        <!-- Send -->
                        <button
                            @click="sendMessage"
                            :disabled="!newMessage.trim().length"
                            class="absolute right-4 bottom-4 px-3 py-1.5 text-sm rounded"
                            :class="newMessage.trim().length
                            ? 'bg-brand-secondary text-white'
                            : 'bg-brand-secondary/30 text-gray-50 cursor-not-allowed'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" />
                            </svg>


                        </button>

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
</style>
