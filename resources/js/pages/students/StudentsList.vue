<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />
    <div class="p-8">
      <h1 class="text-2xl font-bold mb-6">All Students</h1>
      
      <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Risk</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Added On</th>
              <th scope="col" class="relative px-6 py-3">
                <span class="sr-only">Edit</span>
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="student in students.data" :key="student.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ student.full_name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ student.age }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <span v-if="student.latest_prediction"
                      :class="getRiskBadgeColor(student.latest_prediction.prediction_result)"
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                  {{ formatRiskLevel(student.latest_prediction.prediction_result) }}
                </span>
                <span v-else class="text-gray-400">No Prediction</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(student.updated_at) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <Link :href="`/students/${student.id}`" class="text-indigo-600 hover:text-indigo-900">View/Edit</Link>
              </td>
            </tr>
            <tr v-if="students.data.length === 0">
              <td colspan="5" class="px-6 py-4 text-center text-gray-500">No students found.</td>
            </tr>
          </tbody>
        </table>
      </div>
  
      <!-- Pagination -->
      <div class="mt-6 flex justify-between items-center">
        <div v-if="students.links.length > 3">
          <Link v-for="(link, index) in students.links"
                :key="index"
                :href="link.url"
                class="px-3 py-2 mx-1 text-sm rounded-md"
                :class="{ 'bg-indigo-600 text-white': link.active, 'bg-white hover:bg-gray-100': !link.active, 'text-gray-400 cursor-not-allowed': !link.url }"
                :disabled="!link.url"
          />
        </div>
      </div>
    </div>
  </div>
</template>
  
  <script setup>
  import { Link } from '@inertiajs/vue3';
  import Navbar from '@/components/Navbar.vue';
  
  defineProps({
    students: Object,
  });
  
  const getRiskBadgeColor = (risk) => {
    switch (risk) {
      case 'dropout': return 'bg-red-100 text-red-800';
      case 'at_risk': return 'bg-orange-100 text-orange-800';
      case 'safe': return 'bg-green-100 text-green-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };
  
  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    });
  };

  const formatRiskLevel = (risk) => {
    if (risk === 'dropout') return 'High Risk'
    if (risk === 'at_risk') return 'At Risk'
    if (risk === 'safe') return 'Safe'
    return risk.replace('_', ' ').toUpperCase()
  };
  </script>