<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />
    <div class="p-8 max-w-4xl mx-auto">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">{{ student.full_name }}</h1>
        <Link :href="`/students/${student.id}/edit`" 
              class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
          Edit Student
        </Link>
      </div>
  
      <!-- Main Student Details -->
      <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4 border-b pb-2">Student Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
          <div><span class="font-semibold text-gray-600">Age:</span> {{ student.age }}</div>
          <div><span class="font-semibold text-gray-600">Gender:</span> <span class="capitalize">{{ student.gender }}</span></div>
          <div><span class="font-semibold text-gray-600">Course:</span> {{ student.course_of_study || 'N/A' }}</div>
          <div><span class="font-semibold text-gray-600">Semester:</span> {{ student.semester }}</div>
          <div><span class="font-semibold text-gray-600">GPA:</span> {{ student.gpa || 'N/A' }}</div>
          <div><span class="font-semibold text-gray-600">Attendance:</span> {{ student.attendance_rate }}%</div>
          <div><span class="font-semibold text-gray-600">Previous Failures:</span> {{ student.previous_failures }}</div>
          <div><span class="font-semibold text-gray-600">Study Hours/Week:</span> {{ student.study_hours_per_week }}</div>
        </div>
      </div>
  
      <!-- Prediction History -->
      <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4 border-b pb-2">Prediction History</h2>
        <div v-if="student.predictions && student.predictions.length > 0">
          <ul class="space-y-4">
            <li v-for="prediction in student.predictions" :key="prediction.id" class="p-4 border rounded-md flex justify-between items-center">
              <div>
                <p class="font-semibold">Predicted on: <span class="font-normal">{{ formatDate(prediction.created_at) }}</span></p>
                <p class="text-sm text-gray-600">Model Used: <span class="font-mono text-xs">{{ prediction.model_version }}</span></p>
              </div>
              <div class="text-right">
                <span :class="getRiskBadgeColor(prediction.prediction_result)"
                      class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium">
                  {{ prediction.prediction_result.replace('_', ' ').toUpperCase() }}
                </span>
                <p class="text-sm text-gray-500 mt-1">{{ Math.round(prediction.confidence_score * 100) }}% Confidence</p>
              </div>
            </li>
          </ul>
        </div>
        <div v-else class="text-center text-gray-500 py-4">
          <p>No predictions have been made for this student yet.</p>
        </div>
      </div>
    </div>
    </div>
  </template>
  
  <script setup>
  import { Link } from '@inertiajs/vue3';
  import Navbar from '@/components/Navbar.vue';
  
  defineProps({
    student: Object,
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
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };
  </script>