<script setup lang="ts">
// import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import AdminDashboard from '@/components/dashboard/AdminDashboard.vue';
import Navbar from '@/components/Navbar.vue';

// const breadcrumbs: BreadcrumbItem[] = [
//     {
//         title: 'Dashboard',
//         href: '/dashboard',
//     },
// ];
</script>

<template>
    <Head title="Dashboard" />

    <div class="min-h-screen bg-gray-50">
        <Navbar />
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <AdminDashboard />
        </div>
    </div>
</template>
