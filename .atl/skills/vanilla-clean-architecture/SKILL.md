---
name: vanilla-clean-architecture
description: >
  Clean Code and Clean Architecture rules for Vanilla JavaScript without frameworks.
  Trigger: When writing or refactoring Vanilla JavaScript code, handling API calls, separating concerns, or structuring JS files.
license: Apache-2.0
metadata:
  author: gentleman-programming
  version: "1.0"
---

## When to Use

- Writing or refactoring Vanilla JavaScript code.
- Structuring API calls and DOM manipulation.
- Organizing business logic, network requests, and UI updates in pure JS without libraries like React or Angular.

## Critical Patterns

### Separation of Concerns (SRP)
- **NEVER** mix DOM manipulation (`document.getElementById`) with network logic (`fetch`) or pure business logic (validations).
- Keep HTML knowledge strictly contained to UI components.

### Layered Architecture
Must implement at least 3 distinct logical layers:
1. **Services**: Responsible ONLY for HTTP requests (fetch/Axios). They fetch data, return it, or throw errors. They have ZERO knowledge of HTML or DOM.
2. **Domain/Validators**: Pure business functions. Handle calculations, Regex, formatting, without side effects.
3. **UI/Controllers**: The ONLY layer authorized to listen to DOM events, read inputs, and modify HTML (e.g., updating UI elements, showing Toasts).

### Error Handling (Clean Code)
- Network errors MUST be caught in the Services layer and thrown/passed down to the Controllers.
- The UI/Controller layer is solely responsible for deciding HOW to display the error (e.g., alert, Toast) to the user.

### Encapsulation (No Global Variables)
- Avoid polluting the `window` object.
- Use **ES6 Modules** (`import`/`export`) as the standard.
- If modules are not available, use **IIFE** (Immediately Invoked Function Expressions) to encapsulate logic.
- Only expose globals if strictly necessary and deliberate (e.g., a global `window.showToast` utility).

### GGA Standards
- Code must be written in **English** (variables, functions, classes), or be strictly consistent with the project's legacy language context.
- **Strict Naming Conventions**:
  - `camelCase` for variables and functions.
  - `PascalCase` for Classes and File names.

## Code Examples

### Service Layer (Network Only)
```javascript
// UserService.js
export const fetchUserData = async (userId) => {
  try {
    const response = await fetch(`/api/users/${userId}`);
    if (!response.ok) throw new Error('Failed to fetch user');
    return await response.json();
  } catch (error) {
    console.error('Service Error:', error);
    throw error; // Let the controller handle the UI representation
  }
};
```

### Domain Layer (Pure Functions)
```javascript
// UserValidator.js
export const isValidEmail = (email) => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
};
```

### UI/Controller Layer (DOM Only)
```javascript
// UserController.js
import { fetchUserData } from './UserService.js';
import { isValidEmail } from './UserValidator.js';

export class UserController {
  constructor(formElement, resultElement) {
    this.form = formElement;
    this.result = resultElement;
    this.bindEvents();
  }

  bindEvents() {
    this.form.addEventListener('submit', this.handleSubmit.bind(this));
  }

  async handleSubmit(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const emailInput = formData.get('email');

    if (!isValidEmail(emailInput)) {
      this.showError('Invalid email format');
      return;
    }

    try {
      const userData = await fetchUserData(emailInput);
      this.renderUser(userData);
    } catch (error) {
      this.showError(error.message);
    }
  }

  renderUser(data) {
    this.result.innerHTML = `<p class="text-green-500">Welcome, ${data.name}</p>`;
  }

  showError(message) {
    this.result.innerHTML = `<p class="text-red-500">${message}</p>`;
  }
}
```

## Commands

```bash
# Register this skill using skill-registry if necessary
/skill-registry
```
