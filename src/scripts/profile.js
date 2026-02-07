/**
 * VIVA - Profile scripts
 * Handles the automatic upload when a profile picture is selected
 */

document.addEventListener('DOMContentLoaded', () => {
    const avatarContainer = document.getElementById('avatar-container');
    const profileInput = document.getElementById('profile-image-input');
    const profileForm = document.getElementById('profile-upload-form');

    if (avatarContainer && profileInput) {
        avatarContainer.addEventListener('click', () => {
            profileInput.click();
        });

        profileInput.addEventListener('change', () => {
            if (profileInput.files && profileInput.files[0]) {
                // Submit form automatically
                profileForm.submit();
            }
        });
    }
});
