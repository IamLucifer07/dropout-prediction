// resources/js/api/dashboard.js (or .ts)

import apiClient from './index'; // Import the centralized client

export const dashboardApi = {
  async getDashboardData() {
    // The interceptor in apiClient will handle the CSRF token automatically
    const response = await apiClient.get('/api/dashboard');
    return response; // Axios nests the data in a `data` property
  }
};