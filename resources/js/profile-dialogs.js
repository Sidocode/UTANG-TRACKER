async function saveProfile(form, data, password = false) {
    if (form.dataset.saving) return;
    form.dataset.saving = "true";
    const feedback = form.querySelector('[role="status"]');
    try {
        const response = await fetch(form.dataset.url, {
            method: "PATCH",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-CSRF-TOKEN": form.querySelector('[name="_token"]').value,
            },
            body: JSON.stringify(data),
        });
        const result = await response.json();
        if (!response.ok)
            throw new Error(
                Object.values(result.errors || {}).flat()[0] ||
                    result.message ||
                    "Could not save your changes.",
            );
        if (password) {
            form.reset();
            feedback.textContent = result.message;
        } else {
            location.reload();
        }
    } catch (error) {
        feedback.textContent = error.message;
    } finally {
        delete form.dataset.saving;
        feedback.hidden = false;
    }
}
document.addEventListener("click", (event) => {
    const dialog = document.querySelector(".customer-profile-edit-dialog");
    if (!dialog) return;
    if (event.target.closest("[data-open-profile-edit]")) {
        dialog.querySelector("form").reset();
        dialog
            .querySelectorAll("input")
            .forEach((input) => input.setCustomValidity(""));
        dialog.querySelector('[role="status"]').hidden = true;
        dialog.showModal();
    }
    if (event.target.closest("[data-cancel-profile-edit]")) dialog.close();
    if (event.target === dialog) {
        const rect = dialog.getBoundingClientRect();
        if (
            event.clientX < rect.left ||
            event.clientX > rect.right ||
            event.clientY < rect.top ||
            event.clientY > rect.bottom
        )
            dialog.close();
    }
});
document.addEventListener("input", (event) => {
    if (!event.target.matches("[data-profile-field]")) return;
    event.target.setCustomValidity(
        event.target.value.trim() ? "" : "Please complete this field.",
    );
    event.target.closest("form").querySelector('[role="status"]').hidden = true;
});
document.addEventListener("submit", (event) => {
    if (!event.target.matches("[data-profile-edit-form]")) return;
    event.preventDefault();
    const form = event.target;
    form.querySelectorAll("input").forEach((input) =>
        input.setCustomValidity(
            input.value.trim() ? "" : "Please complete this field.",
        ),
    );
    if (!form.reportValidity()) return;
    form.querySelector('[role="status"]').hidden = true;
    document.querySelector("[data-profile-save-confirmation]").showModal();
});
document.addEventListener("click", (event) => {
    const confirmation = document.querySelector(
        "[data-profile-save-confirmation]",
    );
    if (!confirmation) return;
    if (event.target.closest("[data-cancel-profile-save]"))
        confirmation.close();
    if (event.target === confirmation) {
        const rect = confirmation.getBoundingClientRect();
        if (
            event.clientX < rect.left ||
            event.clientX > rect.right ||
            event.clientY < rect.top ||
            event.clientY > rect.bottom
        )
            confirmation.close();
    }
    if (!event.target.closest("[data-confirm-profile-save]")) return;
    confirmation.close();
    const form = document.querySelector("[data-profile-edit-form]");
    if (!form.reportValidity()) return;
    const feedback = form.querySelector('[role="status"]');
    saveProfile(
        form,
        Object.fromEntries(
            [...form.querySelectorAll("[data-profile-field]")].map((input) => [
                input.dataset.profileField,
                input.value.trim(),
            ]),
        ),
    );
});

function validatePasswordPreview(form) {
    const current = form.querySelector('[data-password-field="current"]');
    const next = form.querySelector('[data-password-field="new"]');
    const confirmation = form.querySelector('[data-password-field="confirm"]');
    current.setCustomValidity(
        current.value ? "" : "Please enter your current password.",
    );
    next.setCustomValidity(
        next.value.length >= 8 ? "" : "Use at least 8 characters.",
    );
    confirmation.setCustomValidity(
        confirmation.value === next.value ? "" : "Passwords do not match.",
    );
    return form.reportValidity();
}
document.addEventListener("click", (event) => {
    const dialog = document.querySelector(".customer-password-dialog");
    const confirmation = document.querySelector("[data-password-confirmation]");
    if (!dialog || !confirmation) return;
    const form = dialog.querySelector("form");
    if (event.target.closest("[data-open-password-edit]")) {
        form.reset();
        form.querySelectorAll("input").forEach((input) =>
            input.setCustomValidity(""),
        );
        form.querySelector('[role="status"]').hidden = true;
        dialog.showModal();
    }
    if (event.target.closest("[data-cancel-password-edit]")) dialog.close();
    if (event.target.closest("[data-cancel-password-confirmation]"))
        confirmation.close();
    if (event.target === dialog || event.target === confirmation) {
        const rect = event.target.getBoundingClientRect();
        if (
            event.clientX < rect.left ||
            event.clientX > rect.right ||
            event.clientY < rect.top ||
            event.clientY > rect.bottom
        )
            event.target.close();
    }
    if (event.target.closest("[data-confirm-password]")) {
        confirmation.close();
        if (!validatePasswordPreview(form)) return;
        saveProfile(
            form,
            {
                current_password: form.querySelector(
                    '[data-password-field="current"]',
                ).value,
                password: form.querySelector('[data-password-field="new"]')
                    .value,
                password_confirmation: form.querySelector(
                    '[data-password-field="confirm"]',
                ).value,
            },
            true,
        );
    }
});
document.addEventListener("input", (event) => {
    if (!event.target.matches("[data-password-field]")) return;
    const form = event.target.closest("form");
    form.querySelectorAll("input").forEach((input) =>
        input.setCustomValidity(""),
    );
    form.querySelector('[role="status"]').hidden = true;
});
document.addEventListener("submit", (event) => {
    if (!event.target.matches("[data-password-edit-form]")) return;
    event.preventDefault();
    if (validatePasswordPreview(event.target))
        document.querySelector("[data-password-confirmation]").showModal();
});
document.addEventListener(
    "close",
    (event) => {
        if (event.target.matches(".customer-password-dialog")) {
            event.target.querySelector("form").reset();
            event.target
                .querySelectorAll("input")
                .forEach((input) => input.setCustomValidity(""));
        }
    },
    true,
);
