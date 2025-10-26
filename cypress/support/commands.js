// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************

// Login command
Cypress.Commands.add('login', (email) => {
  cy.visit('/');
  cy.window().then((win) => {
    win.localStorage.setItem('token', 'test-token');
    win.localStorage.setItem('userEmail', email);
  });
  cy.visit('/');
});

// Reset database command
Cypress.Commands.add('resetDb', () => {
  cy.task('db:reset');
  cy.task('db:seed');
});

// Wait for network idle
Cypress.Commands.add('waitForNetworkIdle', (timeout = 1000, minIdleTime = 50) => {
  let lastRequestTime = Date.now();
  let timeoutId;

  return new Promise((resolve, reject) => {
    const onRequest = () => {
      lastRequestTime = Date.now();
    };

    const checkIdle = () => {
      const now = Date.now();
      if (now - lastRequestTime >= minIdleTime) {
        resolve();
      } else {
        timeoutId = setTimeout(checkIdle, minIdleTime);
      }
    };

    cy.on('command:start', onRequest);
    timeoutId = setTimeout(checkIdle, minIdleTime);

    setTimeout(() => {
      clearTimeout(timeoutId);
      cy.off('command:start', onRequest);
      reject(new Error('Network idle timeout'));
    }, timeout);
  });
});

// Get by data-test attribute
Cypress.Commands.add('getByTest', (selector) => {
  return cy.get(`[data-test="${selector}"]`);
});

// Upload file
Cypress.Commands.add('uploadFile', { prevSubject: 'element' }, (subject, fileName, fileType = '') => {
  cy.fixture(fileName, 'base64').then(content => {
    const blob = Cypress.Blob.base64StringToBlob(content, fileType);
    const testFile = new File([blob], fileName, { type: fileType });
    const dataTransfer = new DataTransfer();
    dataTransfer.items.add(testFile);

    return cy.wrap(subject).trigger('change', {
      force: true,
      bubbles: true,
      cancelable: true,
      target: {
        files: dataTransfer.files
      }
    });
  });
});