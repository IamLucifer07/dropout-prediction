<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />
    <div class="max-w-4xl mx-auto p-6">
    <div class="bg-white rounded-lg shadow-lg p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">
      {{ isEditing ? 'Edit Student' : 'Add New Student' }}
    </h2>
    
    <form @submit.prevent="submitForm" class="space-y-6">
      <!-- Personal Information Section -->
      <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Personal Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">
              Full Name *
            </label>
            <input
              id="full_name"
              v-model="form.full_name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.full_name }"
            />
            <span v-if="errors.full_name" class="text-red-500 text-sm">{{ errors.full_name[0] }}</span>
          </div>
          
          <div>
            <label for="age" class="block text-sm font-medium text-gray-700 mb-1">
              Age *
            </label>
            <input
              id="age"
              v-model="form.age"
              type="number"
              min="16"
              max="100"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.age }"
            />
            <span v-if="errors.age" class="text-red-500 text-sm">{{ errors.age[0] }}</span>
          </div>
          
          <div>
            <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">
              Gender *
            </label>
            <select
              id="gender"
              v-model="form.gender"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.gender }"
            >
              <option value="">Select Gender</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
              <option value="other">Other</option>
            </select>
            <span v-if="errors.gender" class="text-red-500 text-sm">{{ errors.gender[0] }}</span>
          </div>
          
          <!-- College Admin field removed - backend gets it from authenticated user -->
        </div>
      </div>

      <!-- Academic Information Section -->
      <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Academic Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="course_of_study" class="block text-sm font-medium text-gray-700 mb-1">
              Course of Study
            </label>
            <input
              id="course_of_study"
              v-model="form.course_of_study"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.course_of_study }"
            />
            <span v-if="errors.course_of_study" class="text-red-500 text-sm">{{ errors.course_of_study[0] }}</span>
          </div>
          
          <div>
            <label for="semester" class="block text-sm font-medium text-gray-700 mb-1">
              Current Semester
            </label>
            <input
              id="semester"
              v-model="form.semester"
              type="number"
              min="1"
              max="12"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.semester }"
            />
            <span v-if="errors.semester" class="text-red-500 text-sm">{{ errors.semester[0] }}</span>
          </div>
          
          <div>
            <label for="gpa" class="block text-sm font-medium text-gray-700 mb-1">
              GPA (0.00-4.00)
            </label>
            <input
              id="gpa"
              v-model="form.gpa"
              type="number"
              min="0"
              max="4"
              step="0.01"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.gpa }"
            />
            <span v-if="errors.gpa" class="text-red-500 text-sm">{{ errors.gpa[0] }}</span>
          </div>
          
          <div>
            <label for="attendance_rate" class="block text-sm font-medium text-gray-700 mb-1">
              Attendance Rate (%)
            </label>
            <input
              id="attendance_rate"
              v-model="form.attendance_rate"
              type="number"
              min="0"
              max="100"
              step="0.01"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.attendance_rate }"
            />
            <span v-if="errors.attendance_rate" class="text-red-500 text-sm">{{ errors.attendance_rate[0] }}</span>
          </div>
          
          <div>
            <label for="previous_failures" class="block text-sm font-medium text-gray-700 mb-1">
              Previous Failures
            </label>
            <input
              id="previous_failures"
              v-model="form.previous_failures"
              type="number"
              min="0"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.previous_failures }"
            />
            <span v-if="errors.previous_failures" class="text-red-500 text-sm">{{ errors.previous_failures[0] }}</span>
          </div>
          
          <div>
            <label for="study_hours_per_week" class="block text-sm font-medium text-gray-700 mb-1">
              Study Hours per Week
            </label>
            <input
              id="study_hours_per_week"
              v-model="form.study_hours_per_week"
              type="number"
              min="0"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.study_hours_per_week }"
            />
            <span v-if="errors.study_hours_per_week" class="text-red-500 text-sm">{{ errors.study_hours_per_week[0] }}</span>
          </div>
        </div>
      </div>

      <!-- Grades Section -->
      <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Grades</h3>
        <div class="space-y-4">
          <div v-for="(grade, index) in form.grades" :key="index" class="flex items-center space-x-4">
            <input
              v-model="grade.subject"
              type="text"
              placeholder="Subject"
              class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <input
              v-model="grade.grade"
              type="text"
              placeholder="Grade (A, B, C, etc.)"
              class="w-32 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <button
              type="button"
              @click="removeGrade(index)"
              class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600"
            >
              Remove
            </button>
          </div>
          <button
            type="button"
            @click="addGrade"
            class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600"
          >
            Add Grade
          </button>
        </div>
      </div>

      <!-- Family & Socioeconomic Information Section -->
      <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Family & Socioeconomic Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="parental_education_level" class="block text-sm font-medium text-gray-700 mb-1">
              Parental Education Level
            </label>
            <select
              id="parental_education_level"
              v-model="form.parental_education_level"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.parental_education_level }"
            >
              <option value="">Select Education Level</option>
              <option value="no_education">No Education</option>
              <option value="primary">Primary Education</option>
              <option value="secondary">Secondary Education</option>
              <option value="bachelors">Bachelor's Degree</option>
              <option value="masters">Master's Degree</option>
              <option value="phd">PhD</option>
            </select>
            <span v-if="errors.parental_education_level" class="text-red-500 text-sm">{{ errors.parental_education_level[0] }}</span>
          </div>
          
          <div>
            <label for="family_income" class="block text-sm font-medium text-gray-700 mb-1">
              Family Income (Annual)
            </label>
            <input
              id="family_income"
              v-model="form.family_income"
              type="number"
              min="0"
              step="0.01"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.family_income }"
            />
            <span v-if="errors.family_income" class="text-red-500 text-sm">{{ errors.family_income[0] }}</span>
          </div>
          
          <div>
            <label for="living_situation" class="block text-sm font-medium text-gray-700 mb-1">
              Living Situation
            </label>
            <select
              id="living_situation"
              v-model="form.living_situation"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.living_situation }"
            >
              <option value="">Select Living Situation</option>
              <option value="with_family">With Family</option>
              <option value="hostel">Hostel</option>
              <option value="rented_accommodation">Rented Accommodation</option>
              <option value="own_accommodation">Own Accommodation</option>
            </select>
            <span v-if="errors.living_situation" class="text-red-500 text-sm">{{ errors.living_situation[0] }}</span>
          </div>
          
          <div>
            <label for="distance_from_home" class="block text-sm font-medium text-gray-700 mb-1">
              Distance from Home (km)
            </label>
            <input
              id="distance_from_home"
              v-model="form.distance_from_home"
              type="number"
              min="0"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.distance_from_home }"
            />
            <span v-if="errors.distance_from_home" class="text-red-500 text-sm">{{ errors.distance_from_home[0] }}</span>
          </div>
          
          <div>
            <label for="mode_of_transport" class="block text-sm font-medium text-gray-700 mb-1">
              Mode of Transport
            </label>
            <select
              id="mode_of_transport"
              v-model="form.mode_of_transport"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.mode_of_transport }"
            >
              <option value="">Select Transport Mode</option>
              <option value="walking">Walking</option>
              <option value="bicycle">Bicycle</option>
              <option value="bus">Bus</option>
              <option value="car">Car</option>
              <option value="motorcycle">Motorcycle</option>
              <option value="other">Other</option>
            </select>
            <span v-if="errors.mode_of_transport" class="text-red-500 text-sm">{{ errors.mode_of_transport[0] }}</span>
          </div>
          
          <div>
            <label for="mental_health_score" class="block text-sm font-medium text-gray-700 mb-1">
              Mental Health Score (0-10)
            </label>
            <input
              id="mental_health_score"
              v-model="form.mental_health_score"
              type="number"
              min="0"
              max="10"
              step="0.01"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.mental_health_score }"
            />
            <span v-if="errors.mental_health_score" class="text-red-500 text-sm">{{ errors.mental_health_score[0] }}</span>
          </div>
        </div>
      </div>

      <!-- Boolean Fields Section -->
      <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Additional Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="flex items-center">
            <input
              id="internet_access"
              v-model="form.internet_access"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <label for="internet_access" class="ml-2 block text-sm text-gray-700">
              Has Internet Access
            </label>
          </div>
          
          <div class="flex items-center">
            <input
              id="extracurricular_involvement"
              v-model="form.extracurricular_involvement"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <label for="extracurricular_involvement" class="ml-2 block text-sm text-gray-700">
              Extracurricular Involvement
            </label>
          </div>
          
          <div class="flex items-center">
            <input
              id="part_time_job"
              v-model="form.part_time_job"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <label for="part_time_job" class="ml-2 block text-sm text-gray-700">
              Has Part-time Job
            </label>
          </div>
          
          <div class="flex items-center">
            <input
              id="financial_aid"
              v-model="form.financial_aid"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <label for="financial_aid" class="ml-2 block text-sm text-gray-700">
              Receives Financial Aid
            </label>
          </div>
        </div>
      </div>

      <!-- Additional Factors Section -->
      <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Additional Factors</h3>
        <div class="space-y-4">
          <div v-for="(factor, index) in form.additional_factors" :key="index" class="flex items-center space-x-4">
            <input
              v-model="factor.key"
              type="text"
              placeholder="Factor Name"
              class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <input
              v-model="factor.value"
              type="text"
              placeholder="Factor Value"
              class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <button
              type="button"
              @click="removeAdditionalFactor(index)"
              class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600"
            >
              Remove
            </button>
          </div>
          <button
            type="button"
            @click="addAdditionalFactor"
            class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600"
          >
            Add Additional Factor
          </button>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="flex justify-end space-x-4 pt-6">
        <button
          type="button"
          @click="$emit('cancel')"
          class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          Cancel
        </button>
        <button
          type="submit"
          :disabled="loading"
          class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
        >
          {{ loading ? 'Saving...' : (isEditing ? 'Update Student' : 'Create Student') }}
        </button>
      </div>
    </form>

    <!-- Prediction Section (shown after student is saved) -->
    <div v-if="savedStudent" class="mt-8 bg-white rounded-lg shadow-lg p-6">
      <h3 class="text-xl font-bold text-gray-800 mb-4">Make Dropout Prediction</h3>
      
      <div class="mb-4">
        <label for="model_selection" class="block text-sm font-medium text-gray-700 mb-2">
          Select ML Model
        </label>
        <select
          id="model_selection"
          v-model="selectedModel"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">Select a model</option>
          <option v-for="model in availableModels" :key="model.name" :value="model.name">
            {{ model.name.replace('.joblib', '').replace('_', ' ').toUpperCase() }}
          </option>
        </select>
      </div>

      <button
        @click="makePrediction"
        :disabled="predictionLoading || !selectedModel"
        class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        {{ predictionLoading ? 'Predicting...' : 'Predict Dropout Risk' }}
      </button>

      <!-- Prediction Results -->
      <div v-if="predictionResult" class="mt-6 p-4 rounded-lg" :class="getPredictionColorClass(predictionResult.prediction_result)">
        <h4 class="text-lg font-semibold mb-2">Prediction Result</h4>
        <div class="space-y-2">
          <p class="text-sm">
            <span class="font-medium">Risk Level:</span>
            <span class="ml-2 px-2 py-1 rounded text-sm font-semibold" :class="getRiskBadgeClass(predictionResult.prediction_result)">
              {{ formatRiskLevel(predictionResult.prediction_result) }}
            </span>
          </p>
          <p class="text-sm">
            <span class="font-medium">Confidence:</span>
            <span class="ml-2">{{ Math.round(predictionResult.confidence_score * 100) }}%</span>
          </p>
          <p class="text-sm">
            <span class="font-medium">Model Used:</span>
            <span class="ml-2">{{ predictionResult.model_version }}</span>
          </p>
          <div v-if="predictionResult.model_metadata && predictionResult.model_metadata.probabilities" class="mt-3">
            <p class="text-sm font-medium mb-1">Probability Distribution:</p>
            <div class="space-y-1">
              <div v-for="(prob, label) in predictionResult.model_metadata.probabilities" :key="label" class="flex items-center justify-between">
                <span class="text-xs text-gray-600">{{ formatRiskLevel(label) }}:</span>
                <span class="text-xs font-medium">{{ Math.round(prob * 100) }}%</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Reset Form Button -->
      <div class="mt-6 flex justify-end">
        <button
          @click="resetFormAndClearPrediction"
          class="px-6 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500"
        >
          Add Another Student
        </button>
      </div>
    </div>
    </div>
    </div>
  </div>
</template>

<script>
// import axios from 'axios'
import apiClient from '@/api/index.js'
import Navbar from '@/components/Navbar.vue'
import { router } from '@inertiajs/vue3'

// Configure axios to send credentials with requests
apiClient.defaults.withCredentials = true
apiClient.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
import { createToastInterface } from 'vue-toastification'
import 'vue-toastification/dist/index.css'

const toast = createToastInterface({
    transition: 'Vue-Toastification__bounce',
    maxToasts: 20,
    newestOnTop: true,
    position: 'top-right',
    timeout: 5000, // All toasts display for 5 seconds
    closeOnClick: false,
    closeButton: true,
    pauseOnFocusLoss: true,
    pauseOnHover: true,
    draggable: true,
    draggablePercent: 0.6,
    showCloseButtonOnHover: false,
    hideProgressBar: false,
    icon: true,
    rtl: false
})

export default {
  name: 'StudentForm',
  components: {
    Navbar
  },
  props: {
    student: {
      type: Object,
      default: null
    }
  },
  emits: ['student-saved', 'cancel', 'prediction-made'],
  data() {
    return {
      form: {
        full_name: '',
        age: '',
        gender: '',
        gpa: null,
        attendance_rate: 0,
        grades: [],
        parental_education_level: '',
        family_income: null,
        mode_of_transport: '',
        internet_access: true,
        previous_failures: 0,
        extracurricular_involvement: false,
        mental_health_score: null,
        study_hours_per_week: 0,
        part_time_job: false,
        living_situation: '',
        distance_from_home: 0,
        financial_aid: false,
        course_of_study: '',
        semester: 1,
        additional_factors: []
      },
      errors: {},
      loading: false,
      collegeAdmins: [], // Will be populated from API
      savedStudent: null, // Store saved student for prediction
      availableModels: [],
      selectedModel: '',
      predictionLoading: false,
      predictionResult: null
    }
  },
  computed: {
    isEditing() {
      return this.student !== null
    }
  },
  watch: {
    student: {
      handler(newStudent) {
        if (newStudent) {
          this.form = { 
            ...newStudent,
            grades: newStudent.grades || [],
            additional_factors: newStudent.additional_factors || []
          }
          // When editing, also set savedStudent so prediction section is available
          // This allows making predictions on existing students without resaving first
          this.savedStudent = newStudent
        } else {
          this.resetForm()
          this.savedStudent = null
          this.predictionResult = null
        }
      },
      immediate: true
    }
  },
  async mounted() {
    // Note: college_admin_id is no longer needed - backend gets it from authenticated user
    await this.fetchAvailableModels()
  },
  methods: {
    async fetchCollegeAdmins() {
      try {
        const response = await apiClient.get('/api/college-admins/active')
        this.collegeAdmins = response.data.data || response.data || []
      } catch (error) {
        console.error('Error fetching college admins:', error)
        // Try fallback endpoint
        try {
          const fallbackResponse = await apiClient.get('/api/college-admins')
          this.collegeAdmins = fallbackResponse.data.data || fallbackResponse.data || []
        } catch (fallbackError) {
          console.error('Error fetching college admins (fallback):', fallbackError)
        }
      }
    },

    async fetchAvailableModels() {
      try {
        const response = await apiClient.get('/api/ml-models')
        this.availableModels = response.data.models || []
        // Set default model if available
        if (this.availableModels.length > 0 && !this.selectedModel) {
          const defaultModel = this.availableModels.find(m => m.name === 'random_forest.joblib')
          this.selectedModel = defaultModel ? defaultModel.name : this.availableModels[0].name
        }
      } catch (error) {
        console.error('Error fetching available models:', error)
        // Set default models on error
        this.availableModels = [
          { name: 'random_forest.joblib' },
          { name: 'decision_tree.joblib' }
        ]
        this.selectedModel = 'random_forest.joblib'
      }
    },

    async makePrediction() {
      if (!this.savedStudent || !this.selectedModel) {
        return
      }

      this.predictionLoading = true
      this.predictionResult = null

      try {
        const response = await apiClient.post(`/api/students/${this.savedStudent.id}/predict`, {
          model: this.selectedModel
        })
        
        this.predictionResult = response.data
        // Emit event to refresh dashboard
        this.$emit('prediction-made', response.data)
        
        // Show success message via toast
        const riskLevel = this.formatRiskLevel(response.data.prediction_result)
        const confidence = Math.round(response.data.confidence_score * 100)
        toast.success(`Prediction completed! Risk: ${riskLevel}, Confidence: ${confidence}%`)
      } catch (error) {
        console.error('Error making prediction:', error)
        toast.error('Failed to make prediction. Please try again.')
      } finally {
        this.predictionLoading = false
      }
    },

    getPredictionColorClass(riskLevel) {
      switch (riskLevel) {
        case 'dropout': return 'bg-red-50 border border-red-200'
        case 'at_risk': return 'bg-orange-50 border border-orange-200'
        case 'safe': return 'bg-green-50 border border-green-200'
        default: return 'bg-gray-50 border border-gray-200'
      }
    },

    getRiskBadgeClass(riskLevel) {
      switch (riskLevel) {
        case 'dropout': return 'bg-red-100 text-red-800'
        case 'at_risk': return 'bg-orange-100 text-orange-800'
        case 'safe': return 'bg-green-100 text-green-800'
        default: return 'bg-gray-100 text-gray-800'
      }
    },

    formatRiskLevel(riskLevel) {
      return riskLevel.replace('_', ' ').toUpperCase().replace('AT RISK', 'AT RISK')
    },

    resetFormAndClearPrediction() {
      this.resetForm()
      this.savedStudent = null
      this.predictionResult = null
      this.selectedModel = this.availableModels.length > 0 ? this.availableModels[0].name : ''
    },
    
    addGrade() {
      this.form.grades.push({ subject: '', grade: '' })
    },
    
    removeGrade(index) {
      this.form.grades.splice(index, 1)
    },
    
    addAdditionalFactor() {
      this.form.additional_factors.push({ key: '', value: '' })
    },
    
    removeAdditionalFactor(index) {
      this.form.additional_factors.splice(index, 1)
    },
    
    async submitForm() {
      this.loading = true
      this.errors = {}
      
      try {
        // Convert boolean values to proper format and handle empty strings for optional numeric fields
        // Note: college_admin_id is automatically set by backend from authenticated user
        const formData = {
          ...this.form,
          // Convert empty strings to null for optional numeric fields
          gpa: this.form.gpa === '' ? null : this.form.gpa,
          family_income: this.form.family_income === '' ? null : this.form.family_income,
          mental_health_score: this.form.mental_health_score === '' ? null : this.form.mental_health_score,
          // Convert boolean values
          internet_access: Boolean(this.form.internet_access),
          extracurricular_involvement: Boolean(this.form.extracurricular_involvement),
          part_time_job: Boolean(this.form.part_time_job),
          financial_aid: Boolean(this.form.financial_aid),
          // Ensure numeric fields are numbers, not strings
          age: Number(this.form.age),
          attendance_rate: Number(this.form.attendance_rate) || 0,
          previous_failures: Number(this.form.previous_failures) || 0,
          study_hours_per_week: Number(this.form.study_hours_per_week) || 0,
          distance_from_home: Number(this.form.distance_from_home) || 0,
          semester: Number(this.form.semester) || 1
        }
        
        let response
        if (this.isEditing) {
          response = await apiClient.put(`/api/students/${this.student.id}`, formData)
        } else {
          response = await apiClient.post('/api/students', formData)
        }
        
        // Store saved student for prediction
        this.savedStudent = response.data.student || response.data
        
        this.$emit('student-saved', response.data)
        
        // Show success message via toast
        if (this.isEditing) {
          toast.success('Student updated successfully!')
          // Redirect to student detail page after edit
          router.visit(`/students/${this.savedStudent.id}`)
        } else {
          toast.success('Student created successfully!')
        }
        
        // Don't reset form immediately - allow user to make prediction
        // this.resetForm()
      } catch (error) {
        console.error('Error saving student:', error)
        if (error.response?.status === 422) {
          this.errors = error.response.data.errors || {}
          // Show validation errors via toast
          const errorMessages = Object.values(this.errors).flat().join('\n')
          toast.error('Validation errors:\n' + errorMessages)
        } else if (error.response?.status === 401) {
          toast.error('Authentication failed. Please login again.')
        } else {
          const errorMessage = error.response?.data?.message || error.message || 'Failed to save student. Please try again.'
          toast.error(errorMessage)
        }
      } finally {
        this.loading = false
      }
    },
    
    resetForm() {
      this.form = {
        full_name: '',
        age: '',
        gender: '',
        gpa: null,
        attendance_rate: 0,
        grades: [],
        parental_education_level: '',
        family_income: null,
        mode_of_transport: '',
        internet_access: true,
        previous_failures: 0,
        extracurricular_involvement: false,
        mental_health_score: null,
        study_hours_per_week: 0,
        part_time_job: false,
        living_situation: '',
        distance_from_home: 0,
        financial_aid: false,
        course_of_study: '',
        semester: 1,
        additional_factors: []
      }
      this.errors = {}
    }
  }
}
</script>

<style scoped>
/* .form-section {
  @apply  p-4 rounded-lg;
} 

.form-section h3 {
  @apply text-lg font-semibold text-gray-700 mb-4;
}

.form-grid {
  @apply grid grid-cols-1 md:grid-cols-2 gap-4;
}

.form-input {
  @apply w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500;
}

.form-input.error {
  @apply border-red-500;
}

.error-message {
  @apply text-red-500 text-sm;
 }  */
</style>