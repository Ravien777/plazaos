<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import CalendarGrid from '@/Components/CalendarGrid.vue';
import TodaysFocus from '@/Components/TodaysFocus.vue';
import { Head } from '@inertiajs/vue3';
import type { Meeting, User } from '@/Types';

defineProps<{
    teamMembers: User[];
    todayMeetings: Meeting[];
}>();
</script>

<template>
    <Head title="Calendar" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Calendar" />
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
                    <div class="lg:col-span-3">
                        <CalendarGrid :team-members="teamMembers" />
                    </div>
                    <div class="lg:col-span-1">
                        <TodaysFocus :meetings="todayMeetings" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
