<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
// import { type BreadcrumbItem } from '@/types';

// import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import Navbar from '@/components/Navbar.vue';

// const breadcrumbItems: BreadcrumbItem[] = [
//     {
//         title: 'Appearance settings',
//         href: '/settings/appearance',
//     },
// ];
</script>

<template>
    <div class="min-h-screen bg-gray-50">
    <Navbar />
        <Head title="Appearance settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Appearance settings" description="Update your account's appearance settings" />
                <AppearanceTabs />
            </div>
        </SettingsLayout>
    </div>
</template>
