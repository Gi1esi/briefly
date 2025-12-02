<script setup>
import {onMounted, ref} from 'vue'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import NavLink from '@/Components/NavLink.vue'
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue'
import { Link, usePage } from '@inertiajs/vue3'
import Footer from '@/Components/Footer.vue'
import {
    ClockIcon,
    BookmarkIcon,

} from '@heroicons/vue/24/outline'

const showingNavigationDropdown = ref(false)
const page = usePage()

const isDark = ref(true)

const toggleDark = () => {
    const html = document.documentElement
    const darkNow = html.classList.toggle('dark')
    localStorage.theme = darkNow ? 'dark' : 'light'
    isDark.value = darkNow
}

onMounted(() => {
    const html = document.documentElement
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
    const shouldBeDark = localStorage.theme === 'dark' || (!localStorage.theme && prefersDark)

    if (shouldBeDark) html.classList.add('dark')
    isDark.value = shouldBeDark
})
</script>

<template>
    <!-- Use flex column layout to pin footer to bottom -->
    <div class="flex flex-col min-h-screen bg-neutral-bg dark:bg-neutral-darkBg">

        <!-- Navigation -->
        <nav class="border-b border-gray-100 bg-neutral-bg dark:border-gray-700 dark:bg-neutral-darkBg">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between items-center">
                    <!-- Left: Logo + main links -->
                    <div class="flex items-center">
                        <div class="flex shrink-0 items-center">
                            <Link :href="route('dashboard')" class="flex items-center gap-3">
                                <ApplicationLogo class="block h-20 w-auto fill-current text-neutral-text dark:text-neutral-darkText" />
                            </Link>
                        </div>

                        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                            <NavLink :href="route('dashboard')" :active="route().current('dashboard')">Home</NavLink>
                            <NavLink href="#" :active="false">Topics</NavLink>
                            <NavLink href="#" :active="false">Read Later</NavLink>
                        </div>
                    </div>

                    <!-- Right: search + profile dropdown -->
                    <div class="hidden sm:ms-6 sm:flex sm:items-center sm:space-x-4">
                        <!-- Search bar -->
                        <div class="relative">
                            <input
                                type="text"
                                placeholder="Search news..."
                                class="rounded-md border border-gray-300 dark:border-brand-primary/10 bg-gray-50 dark:bg-brand-primary/10 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-primary"
                            />
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute right-3 top-2.5 text-gray-400 text-brand-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                            </svg>
                        </div>

                        <!-- Dropdown -->
                        <div class="relative ms-3">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <span class="inline-flex rounded-md">
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-md border border-transparent bg-neutral-bg px-3 py-2 text-sm font-medium leading-4 text-neutral-text/80 hover:text-brand-primary focus:outline-none dark:bg-neutral-darkBg dark:text-neutral-darkText/80 dark:hover:text-brand-primary"
                                        >
                                            {{ page.props.auth.user.name }}
                                            <svg class="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </span>
                                </template>

                                <template #content>
                                    <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>

                    <!-- Mobile Hamburger -->
                    <div class="-me-2 flex items-center sm:hidden">
                        <button
                            @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none dark:text-gray-400 dark:hover:bg-gray-900 dark:hover:text-gray-300"
                        >
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Responsive Dropdown Menu -->
            <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden">
                <div class="space-y-1 pb-3 pt-2">
                    <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">Home</ResponsiveNavLink>
                    <ResponsiveNavLink href="#">Topics</ResponsiveNavLink>
                    <ResponsiveNavLink href="#">Read Later</ResponsiveNavLink>
                </div>

                <div class="border-t border-gray-200 pb-1 pt-4 dark:border-gray-600">
                    <div class="px-4">
                        <div class="text-base font-medium text-neutral-text dark:text-neutral-darkText">{{ page.props.auth.user.name }}</div>
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ page.props.auth.user.email }}</div>
                    </div>

                    <div class="mt-3 space-y-1">
                        <ResponsiveNavLink :href="route('profile.edit')">Profile</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('logout')" method="post" as="button">Log Out</ResponsiveNavLink>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Header -->
        <header class="bg-white shadow dark:bg-gray-800" v-if="$slots.header">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Main Content expands to fill space -->
        <main class="flex-grow">
            <slot />
        </main>

        <!-- Footer always at bottom -->
        <Footer />
    </div>
</template>
