<script setup>
import { ref, computed } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const search = ref("");

const newMessage = ref("");

const props = defineProps({
    article: Object,
    chats: Array
})
const selectedArticle = ref(props.article);
const articles = ref(props.chats);

const messages = ref({
    1: [
        { id: 1, text: "What do you think about this AI approach?", from: "them" },
        {
            id: 2,
            text: "It seems promising but might need more localized data.",
            from: "me",
        },
    ],
    2: [{ id: 1, text: "This could change the energy landscape!", from: "them" }],
});

const filteredArticles = computed(() =>
    articles.value.filter((a) =>
        a.title.toLowerCase().includes(search.value.toLowerCase())
    )
);

const selectedArticleMessages = computed(() => {
    if (!selectedArticle.value) return [];
    return messages.value[selectedArticle.value.id] || [];
});

function selectArticle(article) {
    selectedArticle.value = article;
}

async function sendMessage() {
    if (!newMessage.value || !selectedArticle.value) return;

    const articleId = selectedArticle.value.id;

    // Ensure this article has a message array
    if (!messages.value[articleId]) {
        messages.value[articleId] = [];
    }

    // User message
    const userMsg = {
        id: Date.now(),
        text: newMessage.value,
        from: "me"
    };

    messages.value[articleId].push(userMsg);

    const userText = newMessage.value;
    newMessage.value = "";

    try {
        const payload = {
            article_title: selectedArticle.value.title,
            article_content: selectedArticle.value.content || selectedArticle.value.summary || "",
            question: userText,
            history: messages.value[articleId]
                .slice(0, -1) // Exclude the current message we just added
                .map(m => ({
                    role: m.from === "me" ? "user" : "assistant",
                    content: m.text
                })),
        };

        console.log("Sending payload:", payload);

        const res = await axios.post("http://127.0.0.1:8000/chat", payload, {
            headers: { "Content-Type": "application/json" }
        });

        messages.value[articleId].push({
            id: Date.now() + 1,
            text: res.data.answer,
            from: "them"
        });

    } catch (err) {
        console.error("Full Chat error:", err);
        console.error("Error response:", err.response);
        console.error("Error message:", err.message);
        console.error("Error status:", err.response?.status);
        console.error("Error data:", err.response?.data);

        // Show error message to user
        messages.value[articleId].push({
            id: Date.now() + 1,
            text: `Error: ${err.response?.data?.detail || err.message || "Please try again"}`,
            from: "them"
        });
    }
}


</script>

<template>
    <AuthenticatedLayout>
        <div class="flex h-screen bg-gray-50 px-14 pt-10">
            <!-- Sidebar -->
            <aside class="w-60 border-r bg-white overflow-y-auto">
                <div class="p-4 font-semibold text-brand-primary text-lg">Articles</div>
                <div class="px-2">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search articles"
                        class="w-full text-sm px-2 py-1 rounded-xl border focus:outline-none focus:ring-2 focus:ring-brand-primary"
                    />
                </div>
                <div class="mt-4">
                    <div
                        v-for="article in filteredArticles"
                        :key="article.id"
                        @click="selectArticle(article)"
                        class="flex items-center gap-3 px-4 py-2 cursor-pointer hover:bg-gray-100 transition-all group"
                        :class="selectedArticle && selectedArticle.id === article.id ? 'bg-gray-100' : ''"
                    >
                        <!-- Title container with proper truncation -->
                        <div class="flex-1 min-w-0 overflow-hidden">
                            <div class="text-sm text-gray-400 truncate group-hover:text-gray-600" :title="article.title">
                                {{ article.title }}
                            </div>
                        </div>
                        <!-- Time stays visible -->
                        <div class="text-xs text-gray-400 flex-shrink-0 ml-2 group-hover:text-gray-500">2:58pm</div>
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
                        v-for="msg in selectedArticleMessages"
                        :key="msg.id"
                        class="flex"
                        :class="msg.from === 'me' ? 'justify-end' : 'justify-start'"
                    >
                        <div
                            class="max-w-sm px-4 py-2 rounded-2xl break-words text-sm"
                            :class="msg.from === 'me' ? 'bg-brand-primary text-white' : 'bg-white border'"
                        >
                            {{ msg.text }}
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
