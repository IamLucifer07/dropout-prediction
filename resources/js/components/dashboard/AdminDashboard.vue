<template>
  <div class="min-h-screen bg-gray-50">
    <nav class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <h1 class="text-xl font-semibold text-gray-900">Dropout Prediction Dashboard</h1>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-600">{{ user?.name }} - {{ user?.college_name }}</span>
            <button
              @click="logout"
              class="text-sm text-gray-500 hover:text-gray-700"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Students</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ dashboardData.total_students }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">High Risk</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ dashboardData.risk_distribution?.dropout || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">At Risk</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ dashboardData.risk_distribution?.at_risk || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Safe</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ dashboardData.risk_distribution?.safe || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <!-- Risk Distribution Chart -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Risk Distribution</h3>
              <RiskDistributionChart :data="dashboardData.risk_distribution" />
            </div>
          </div>

          <!-- Monthly Trends Chart -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Monthly Trends</h3>
              <MonthlyTrendsChart :data="dashboardData.monthly_trends" />
            </div>
          </div>
        </div>

        <!-- Recent Predictions -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Predictions</h3>
            <div class="flow-root">
              <ul class="-my-5 divide-y divide-gray-200">
                <li v-for="prediction in dashboardData.recent_predictions" :key="prediction.id" class="py-4">
                  <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                      <div :class="[
                        'inline-flex items-center justify-center h-8 w-8 rounded-full',
                        getRiskColor(prediction.prediction_result)
                      ]">
                        <span class="text-sm font-medium text-white">
                          {{ prediction.student.full_name.charAt(0) }}
                        </span>
                      </div>
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium text-gray-900 truncate">
                        {{ prediction.student.full_name }}
                      </p>
                      <p class="text-sm text-gray-500">
                        {{ formatDate(prediction.created_at) }}
                      </p>
                    </div>
                    <div class="flex-shrink-0">
                      <span :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        getRiskBadgeColor(prediction.prediction_result)
                      ]">
                        {{ prediction.prediction_result.replace('_', ' ').toUpperCase() }}
                      </span>
                    </div>
                    <div class="flex-shrink-0">
                      <span class="text-sm text-gray-500">
                        {{ Math.round(prediction.confidence_score * 100) }}% confidence
                      </span>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
            <div class="mt-6">
              <Link
                href="/students-list"
                class="w-full flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                View all students
              </Link>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
          <Link
            href="/students"
            class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg p-6 text-center transition-colors"
          >
            <svg class="mx-auto h-12 w-12 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium">Add New Student</h3>
            <p class="mt-1 text-sm text-indigo-200">Input student data for risk prediction</p>
          </Link>

          <Link
            href="/students-list"
            class="bg-gray-600 hover:bg-gray-700 text-white rounded-lg p-6 text-center transition-colors"
          >
            <svg class="mx-auto h-12 w-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium">View All Students</h3>
            <p class="mt-1 text-sm text-gray-200">Browse and manage student records</p>
          </Link>

          <Link
            href="/external-data"
            class="bg-green-600 hover:bg-green-700 text-white rounded-lg p-6 text-center transition-colors"
          >
            <svg class="mx-auto h-12 w-12 text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium">External Data</h3>
            <p class="mt-1 text-sm text-green-200">Access open datasets and research</p>
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="js">
import { ref, onMounted, computed } from 'vue'
import { usePage, router, Link } from '@inertiajs/vue3'
// import { dashboardApi } from '@/api/dashboard'
// import StudentForm from '@/pages/students/StudentForm.vue'
import RiskDistributionChart from '@/components/charts/RiskDistributionChart.vue'
import MonthlyTrendsChart from '@/components/charts/MonthlyTrendsChart.vue' 

const dashboardData = ref({
  total_students: 0,
  recent_predictions: [],
  risk_distribution: {},
  monthly_trends: {}
})

const page = usePage()
const user = computed(() => page.props.auth.user)

onMounted(async () => {
  try {
    const response = await dashboardApi.getDashboardData()
    dashboardData.value = response.data
  } catch (error) {
    console.error('Failed to load dashboard data:', error)
  }
})

const logout = async () => {
  router.post('/logout')
}

const getRiskColor = (risk) => {
  switch (risk) {
    case 'dropout': return 'bg-red-500'
    case 'at_risk': return 'bg-orange-500'
    case 'safe': return 'bg-green-500'
    default: return 'bg-gray-500'
  }
}

const getRiskBadgeColor = (risk) => {
  switch (risk) {
    case 'dropout': return 'bg-red-100 text-red-800'
    case 'at_risk': return 'bg-orange-100 text-orange-800'
    case 'safe': return 'bg-green-100 text-green-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}
</script>