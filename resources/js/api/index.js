// resources/js/api/index.js

import axios from 'axios';

// Create a separate, clean instance specifically for the CSRF cookie request.
// This instance does NOT have the interceptor, which prevents the infinite loop.
const csrfClient = axios.create({
    baseURL: 'http://dropout-prediction.test',
    withCredentials: true,
});

// Create your main API client for the rest of the app.
const apiClient = axios.create({
    baseURL: 'http://dropout-prediction.test',
    withCredentials: true,
});

// A variable to ensure we only fetch the cookie once.
let csrfTokenInitialized = false;

// The function to perform the initial CSRF request.
// It now uses the clean `csrfClient`.
export const initializeCsrfToken = async () => {
    if (csrfTokenInitialized) {
        return;
    }
    try {
        await csrfClient.get('/sanctum/csrf-cookie');
        csrfTokenInitialized = true;
        console.log('CSRF token initialized successfully.');
    } catch (error) {
        console.error('Could not initialize CSRF token:', error);
        // We throw the error to prevent subsequent requests from failing silently.
        throw error;
    }
};

// Add the interceptor ONLY to the main apiClient.
apiClient.interceptors.request.use(
    async (config) => {
        // Before any real API call, ensure the CSRF cookie is set.
        if (!csrfTokenInitialized) {
            await initializeCsrfToken();
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    },
);

export default apiClient;
