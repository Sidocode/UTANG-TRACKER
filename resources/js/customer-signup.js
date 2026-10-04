import "../css/customer-registration.css";
import "../css/customer-confirmation.css";
import { saveRequest } from "./backend";

const form = document.querySelector(
    "#customer-registration-form, #customer-login-form",
);
if (form) {
    const isLogin = form.id === "customer-login-form";
    const fields = [...form.querySelectorAll('input:not([type="hidden"])')];
    const feedback = form.querySelector('[role="status"]');
    const confirmation = document.querySelector(".registration-confirmation");
    let registrationConfirmed = false;
    confirmation
        ?.querySelector("[data-cancel-registration]")
        .addEventListener("click", () => confirmation.close());
    confirmation
        ?.querySelector("[data-confirm-registration]")
        .addEventListener("click", () => {
            confirmation.close();
            registrationConfirmed = true;
            form.requestSubmit();
            registrationConfirmed = false;
        });
    confirmation?.addEventListener("click", (event) => {
        if (event.target !== confirmation) return;
        const rect = confirmation.getBoundingClientRect();
        if (
            event.clientX < rect.left ||
            event.clientX > rect.right ||
            event.clientY < rect.top ||
            event.clientY > rect.bottom
        )
            confirmation.close();
    });

    function validate(field) {
        let message = "";
        if (!field.value.trim()) message = "Please complete this field.";
        else if (field.id === "mobileNumber" && !/^09\d{9}$/.test(field.value))
            message = "Enter an 11-digit mobile number starting with 09.";
        else if (
            !isLogin &&
            field.type === "password" &&
            field.value.length < 8
        )
            message = "Use at least 8 characters.";
        else if (
            field.id === "password_confirmation" &&
            field.value !== form.querySelector("#password").value
        )
            message = "Passwords do not match.";
        const error = document.getElementById(`${field.id}-error`);
        error.textContent = message;
        error.hidden = !message;
        field.setAttribute("aria-invalid", String(Boolean(message)));
        return !message;
    }

    form.addEventListener("input", (event) => {
        feedback.hidden = true;
        if (event.target.getAttribute("aria-invalid") === "true")
            validate(event.target);
        if (
            !isLogin &&
            event.target.id === "password" &&
            form.querySelector("#password_confirmation").value
        )
            validate(form.querySelector("#password_confirmation"));
    });
    form.addEventListener("submit", async (event) => {
        event.preventDefault();
        const invalid = fields.filter((field) => !validate(field));
        if (invalid.length) {
            feedback.hidden = true;
            invalid[0].focus();
            return;
        }
        if (isLogin) {
            HTMLFormElement.prototype.submit.call(form);
            return;
        }
        if (!isLogin) {
            if (!registrationConfirmed && confirmation) {
                confirmation.showModal();
                return;
            }
            const result = await saveRequest(
                form.action,
                new FormData(form),
                feedback,
                form.querySelector('[type="submit"]'),
            );
            if (result) window.location.assign(result.redirect);
            return;
        }
    });
    form.querySelector("[data-forgot-password]")?.addEventListener(
        "click",
        () => {
            feedback.textContent = "Password recovery is not connected yet.";
            feedback.hidden = false;
        },
    );
}
