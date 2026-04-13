# Proposal: Split Login and Register

## Intent

Split the combined login/register view into two independent pages (`/login` and `/registro`) to fix responsiveness issues on mobile devices caused by the complex CSS overlay animation, while preserving the exact same visual card style.

## Scope

### In Scope
- Create separate routes and controllers for `/login` and `/registro` in `index.php`.
- Split `src/views/login.view.php` into two separate views: `login.view.php` and `registro.view.php`.
- Replace the CSS overlay sliding animation with simple anchor links (`<a href>`) between the two pages.
- Update frontend JS (`AuthController.js` and `LoginUIController.js`) to handle the forms independently without relying on the sliding UI logic.
- Maintain the existing visual design and styling for both cards.

### Out of Scope
- Modifying the underlying backend authentication logic or database schema.
- Redesigning the cards visually.
- Adding new authentication methods (e.g., OAuth).

## Capabilities

### New Capabilities
- `user-auth`: Core authentication capabilities for login and registration views and routing.

### Modified Capabilities
- None

## Approach

1.  **Routing**: Add `/registro` route to `index.php` pointing to a new `src/controllers/registro.php`.
2.  **Controllers**: Create `src/controllers/registro.php` mirroring `login.php` logic but pointing to a new view.
3.  **Views**:
    *   Modify `src/views/login.view.php`: Remove the register form and overlay. Keep only the login form. Update the "Registrarse" button to link to `/registro`.
    *   Create `src/views/registro.view.php`: Contain only the register form. Update the "Iniciar Sesión" button to link to `/login`.
4.  **Frontend JS**:
    *   Refactor `AuthController.js` and `LoginUIController.js` to initialize only the form present on the current page. Remove sliding logic.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `index.php` | Modified | Add new route for `/registro`. |
| `src/controllers/registro.php` | New | Controller for the registration page. |
| `src/views/login.view.php` | Modified | Remove register form and overlay, add link. |
| `src/views/registro.view.php` | New | New view containing only the register form. |
| `src/scripts/controllers/AuthController.js` | Modified | Remove dependencies on sliding animation UI. |
| `src/scripts/controllers/LoginUIController.js` | Modified/Removed | Remove overlay animation logic. |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Broken redirect after login/register | Low | Ensure the `redirect` URL parameter is still passed correctly in both new views. |
| Missing JS events on the new page | Low | Initialize JS controllers based on form existence (`document.getElementById`) instead of assuming both exist. |

## Rollback Plan

Revert the changes to `index.php`, `src/views/login.view.php`, and `src/scripts/controllers/AuthController.js` to their previous state. Delete the newly created files (`registro.php` and `registro.view.php`).

## Dependencies

- None

## Success Criteria

- [ ] Navigating to `/login` shows only the login form, styled correctly.
- [ ] Navigating to `/registro` shows only the register form, styled correctly.
- [ ] Clicking "Registrarse" on the login page redirects to `/registro`.
- [ ] Clicking "Iniciar Sesión" on the register page redirects to `/login`.
- [ ] Both pages render correctly and are fully responsive on mobile devices without overlapping elements.
- [ ] Authentication (login and register) still works successfully.