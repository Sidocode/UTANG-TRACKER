import { saveRequest } from "./backend";

export function initializeCustomerRegistration(signal) {
    const dialog = document.querySelector(".register-customer-modal");
    if (!dialog) return;
    const form = dialog.querySelector("[data-customer-registration]");
    const feedback = dialog.querySelector("[data-registration-form-feedback]");
    const password = form.elements.password;
    const confirmation = form.elements.password_confirmation;
    document.querySelectorAll("[data-open-registration]").forEach((trigger) => {
        trigger.addEventListener(
            "click",
            () => {
                form.reset();
                confirmation.setCustomValidity("");
                feedback.hidden = true;
                dialog.showModal();
            },
            { signal },
        );
    });
    dialog
        .querySelectorAll("[data-close-registration]")
        .forEach((button) =>
            button.addEventListener("click", () => dialog.close(), { signal }),
        );
    dialog.addEventListener(
        "click",
        (event) => {
            if (event.target !== dialog) return;
            const bounds = dialog.getBoundingClientRect();
            if (
                event.clientX < bounds.left ||
                event.clientX > bounds.right ||
                event.clientY < bounds.top ||
                event.clientY > bounds.bottom
            )
                dialog.close();
        },
        { signal },
    );
    const validatePassword = () =>
        confirmation.setCustomValidity(
            confirmation.value !== password.value
                ? "Passwords do not match."
                : "",
        );
    password.addEventListener("input", validatePassword, { signal });
    confirmation.addEventListener("input", validatePassword, { signal });
    form.addEventListener(
        "submit",
        async (event) => {
            event.preventDefault();
            validatePassword();
            if (!form.reportValidity()) return;
            const result = await saveRequest(
                form.action,
                new FormData(form),
                feedback,
                form.querySelector('[type="submit"]'),
            );
            if (result) window.location.assign(result.redirect);
        },
        { signal },
    );
}
