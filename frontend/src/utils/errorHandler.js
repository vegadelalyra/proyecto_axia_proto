export const handleError = error => {
  const errorDetails = {
    message: error.message || 'An unknown error occurred',
    status: error.status || 'N/A',
    code: error.code || 'N/A',
    timestamp: new Date().toISOString(),
  };

  // Log to console
  console.error('Error:', JSON.stringify(errorDetails, null, 2));

  // Save error info to localStorage (or send to an API for server-side storage)
  saveErrorToLocalStorage(errorDetails);
};

// Helper function to save error details to localStorage
const saveErrorToLocalStorage = errorDetails => {
  const errors = JSON.parse(localStorage.getItem('appErrors')) || [];
  errors.push(errorDetails);
  localStorage.setItem('appErrors', JSON.stringify(errors));
};

export const getErrorsFromLocalStorage = () => {
  const errors = JSON.parse(localStorage.getItem('appErrors')) || [];
  return errors;
};
