// Regular expressions for validation
const patterns = {
  email: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
  name: /^[a-zA-Z\s'-]{2,50}$/,
  phone: /^\+?[\d\s-]{10,}$/,
  password: /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]{8,}$/,
};

// Field requirements with helpful messages
export const fieldRequirements = {
  name: {
    required: true,
    minLength: 2,
    maxLength: 50,
    pattern: patterns.name,
    message: 'Name should be 2-50 characters long and contain only letters, spaces, hyphens, and apostrophes',
    hint: 'Enter the full name (e.g., "John Smith")'
  },
  email: {
    required: true,
    pattern: patterns.email,
    message: 'Please enter a valid email address',
    hint: 'Enter your email address (e.g., "user@example.com")'
  },
  phone: {
    required: false,
    pattern: patterns.phone,
    message: 'Please enter a valid phone number',
    hint: 'Enter phone number with country code (e.g., "+1 555-123-4567")'
  },
  password: {
    required: true,
    minLength: 8,
    pattern: patterns.password,
    message: 'Password must be at least 8 characters long and include both letters and numbers',
    hint: 'Use a mix of letters and numbers for better security'
  },
  taskName: {
    required: true,
    minLength: 3,
    maxLength: 100,
    message: 'Task name should be 3-100 characters long',
    hint: 'Enter a clear, descriptive name for the task'
  },
  taskDescription: {
    required: false,
    maxLength: 500,
    message: 'Description should not exceed 500 characters',
    hint: 'Provide detailed instructions or requirements'
  },
  points: {
    required: true,
    min: 0,
    max: 100,
    message: 'Points should be between 0 and 100',
    hint: 'Assign points based on task difficulty'
  },
  allowanceRate: {
    required: true,
    min: 0,
    max: 1000,
    message: 'Weekly allowance rate should be between $0 and $1000',
    hint: 'Set the base weekly allowance amount'
  }
};

// Validation functions
export const validateField = (value, requirements) => {
  const errors = [];

  // Check required
  if (requirements.required && !value) {
    errors.push('This field is required');
    return { isValid: false, errors };
  }

  // Skip other validations if field is empty and not required
  if (!requirements.required && !value) {
    return { isValid: true, errors };
  }

  // Check minLength
  if (requirements.minLength && value.length < requirements.minLength) {
    errors.push(\`Must be at least \${requirements.minLength} characters\`);
  }

  // Check maxLength
  if (requirements.maxLength && value.length > requirements.maxLength) {
    errors.push(\`Must not exceed \${requirements.maxLength} characters\`);
  }

  // Check pattern
  if (requirements.pattern && !requirements.pattern.test(value)) {
    errors.push(requirements.message);
  }

  // Check min value for numbers
  if (requirements.min !== undefined && Number(value) < requirements.min) {
    errors.push(\`Must be at least \${requirements.min}\`);
  }

  // Check max value for numbers
  if (requirements.max !== undefined && Number(value) > requirements.max) {
    errors.push(\`Must not exceed \${requirements.max}\`);
  }

  return {
    isValid: errors.length === 0,
    errors
  };
};

// Form validation
export const validateForm = (formData, schema) => {
  const errors = {};
  let isValid = true;

  Object.keys(schema).forEach(field => {
    if (field in formData) {
      const result = validateField(formData[field], schema[field]);
      if (!result.isValid) {
        errors[field] = result.errors;
        isValid = false;
      }
    } else if (schema[field].required) {
      errors[field] = ['This field is required'];
      isValid = false;
    }
  });

  return { isValid, errors };
};

// Helper to get field state classes
export const getFieldStateClasses = (fieldName, errors, touched) => {
  const baseClasses = 'appearance-none block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 sm:text-sm';
  
  if (!touched || !touched[fieldName]) {
    return \`\${baseClasses} border-gray-300 focus:ring-indigo-500 focus:border-indigo-500\`;
  }

  if (errors && errors[fieldName]) {
    return \`\${baseClasses} border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500\`;
  }

  return \`\${baseClasses} border-green-300 focus:ring-green-500 focus:border-green-500\`;
};

// Helper to format validation messages
export const getValidationMessage = (fieldName, errors, schema) => {
  if (errors && errors[fieldName]) {
    return {
      type: 'error',
      message: errors[fieldName][0],
      icon: 'exclamation-circle',
      color: 'text-red-600'
    };
  }

  return {
    type: 'hint',
    message: schema[fieldName].hint,
    icon: 'information-circle',
    color: 'text-gray-500'
  };
};

