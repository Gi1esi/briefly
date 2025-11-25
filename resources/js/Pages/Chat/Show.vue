<script setup>
import { ref, computed } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000';

const search = ref("");
const newMessage = ref("");



import { router } from '@inertiajs/vue3';

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
    const userMsg = {
        id: Date.now(),
        message: userText,
        sender: "user"
    };

    messages.value.push(userMsg);

    newMessage.value = "";

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
        <div class="flex h-screen bg-gray-50 px-14 pt-10">
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

                        <div class="text-xs text-gray-400 flex-shrink-0 ml-2 group-hover:text-gray-500">
                            2:58pm
                        </div>
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
                <div class="flex-1 overflow-y-auto p-6 space-y-4">
                    <div
                        v-for="msg in messages"
                        :key="msg.id"
                        class="flex"
                        :class="msg.sender === 'user' ? 'justify-end' : 'justify-center'"
                    >
                        <div
                            class="max-w-lg px-4 py-2 rounded-2xl break-words text-sm"
                            :class="msg.sender === 'user' ? 'bg-brand-primary text-white' : 'bg-white border'"
                        >
                            {{ msg.message }}
                        </div>
                    </div>
                </div>

                <!-- Input -->
                <div class="p-4 border-t bg-white flex items-center gap-3">
                    <input
                        v-model="newMessage"
                        @keyup.enter="sendMessage"
                        type="text"
                        placeholder="Write your message..."
                        class="flex-1 px-4 py-2 text-sm rounded-xl border focus:outline-none focus:ring-2 focus:ring-brand-primary"
                        :disabled="!selectedArticle"
                    />
                    <button
                        @click="sendMessage"
                        class="px-4 py-2 text-sm bg-brand-secondary text-white rounded-xl"
                        :disabled="!selectedArticle"
                    >
                        Send
                    </button>
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
