import { saveRequest } from "./backend";

export function initializeAccountConfirmation(signal) {
    const dialog = document.querySelector(".account-confirmation");
    if (!dialog) return;
    const feedback = dialog.querySelector("[data-account-feedback]");
    const proceed = dialog.querySelector("[data-account-proceed]");
    let trigger;
    let logout;
    document.querySelectorAll("[data-account-confirm]").forEach((button) => {
        button.addEventListener(
            "click",
            () => {
                trigger = button;
                logout = button.dataset.accountConfirm === "logout";
                const activating = button.dataset.accountConfirm === "activate";
                dialog.classList.toggle("account-confirmation-logout", logout);
                dialog.querySelector("h2").textContent = logout
                    ? "Log Out?"
                    : activating
                      ? "Activate Customer?"
                      : "Deactivate Customer?";
                dialog.querySelector(
                    "#account-confirm-description",
                ).textContent = logout
                    ? "Are you sure you want to log out of your account?"
                    : "Are you sure you want to " +
                      (activating ? "activate " : "deactivate ") +
                      button.dataset.customerName +
                      (activating
                          ? "? They will be able to access their account again."
                          : "? They will no longer be able to access their account.");
                proceed.querySelector("span").textContent = logout
                    ? "Log out"
                    : activating
                      ? "Activate"
                      : "Deactivate";
                proceed.disabled = false;
                feedback.hidden = true;
                dialog.showModal();
            },
            { signal },
        );
    });
    dialog.addEventListener(
        "click",
        async (event) => {
            if (event.target.closest("[data-account-dismiss]")) dialog.close();
            if (event.target.closest("[data-account-proceed]")) {
                if (logout) {
                    proceed.disabled = true;
                    document
                        .querySelector("#owner-logout-form")
                        .requestSubmit();
                    return;
                }
                const result = await saveRequest(
                    trigger.dataset.accountUrl,
                    {},
                    feedback,
                    proceed,
                );
                if (result) window.location.assign(result.redirect);
            }
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
        },
        { signal },
    );
    dialog.addEventListener(
        "close",
        () => trigger?.focus({ preventScroll: true }),
        { signal },
    );
    signal.addEventListener(
        "abort",
        () => {
            if (dialog.open) dialog.close();
        },
        { once: true },
    );
}
