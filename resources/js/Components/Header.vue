<script setup>
import { Link } from '@inertiajs/vue3'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
</script>

<template>
    <header class="bg-neutral-bg shadow-sm border-b border-gray-200 dark:bg-neutral-darkBg dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center h-16">
            <div class="flex items-center space-x-6">
                <Link :href="route('dashboard')" class="flex items-center space-x-2">
                    <img src="/images/logos/logo.png" alt="Brief-Me" class="h-auto w-20" />
                </Link>

                <nav class="hidden sm:flex space-x-6">
                    <Link :href="route('dashboard')" class="text-neutral-text/70 hover:text-brand-primary dark:text-neutral-darkText/70 dark:hover:text-brand-primary">Home</Link>
                    <Link href="#" class="text-neutral-text/70 hover:text-brand-primary dark:text-neutral-darkText/70 dark:hover:text-brand-primary">Topics</Link>
                    <Link href="#" class="text-neutral-text/70 hover:text-brand-primary dark:text-neutral-darkText/70 dark:hover:text-brand-primary">Read Later</Link>
                </nav>
            </div>

            <div class="flex items-center space-x-4">
                <div class="relative">
                    <input
                        type="text"
                        placeholder="Search news..."
                        class="rounded-md border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-primary"
                    />
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute right-3 top-2.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                    </svg>
                </div>

                <Dropdown align="right" width="48">
                    <template #trigger>
                        <button
                            class="inline-flex items-center text-neutral-text/80 dark:text-neutral-darkText/80 text-sm font-medium hover:text-brand-primary"
                        >
                            {{ $page.props.auth.user.name }}
                            <svg class="ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.23 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </template>

                    <template #content>
                        <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                        <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                    </template>
                </Dropdown>
            </div>
        </div>
    </header>
</template>
