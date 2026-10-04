import { saveRequest } from "./backend";

export function initializeRegistrationReview(signal) {
    const dialog = document.querySelector(".registration-review-modal");
    if (!dialog) return;
    const approval = document.querySelector(".registration-approval-modal");
    const rejection = document.querySelector(".registration-rejection-modal");
    let trigger;
    rejection.addEventListener(
        "close",
        () => {
            if (dialog.open)
                dialog
                    .querySelector('[data-registration-action="Rejection"]')
                    .focus({ preventScroll: true });
        },
        { signal },
    );
    rejection.addEventListener(
        "click",
        (event) => {
            if (event.target.closest("[data-cancel-rejection]"))
                rejection.close();
            if (event.target === rejection) {
                const bounds = rejection.getBoundingClientRect();
                if (
                    event.clientX < bounds.left ||
                    event.clientX > bounds.right ||
                    event.clientY < bounds.top ||
                    event.clientY > bounds.bottom
                )
                    rejection.close();
            }
        },
        { signal },
    );
    approval.addEventListener(
        "close",
        () => {
            if (dialog.open)
                dialog
                    .querySelector('[data-registration-action="Approval"]')
                    .focus({ preventScroll: true });
        },
        { signal },
    );
    approval.addEventListener(
        "click",
        async (event) => {
            if (event.target.closest("[data-cancel-approval]"))
                approval.close();
            if (event.target.closest("[data-confirm-approval]")) {
                const feedback = dialog.querySelector(
                    "[data-registration-feedback]",
                );
                const result = await saveRequest(
                    trigger.dataset.approveUrl,
                    {},
                    feedback,
                    event.target.closest("[data-confirm-approval]"),
                );
                approval.close();
                if (result) window.location.assign(result.redirect);
            }
            if (event.target === approval) {
                const bounds = approval.getBoundingClientRect();
                if (
                    event.clientX < bounds.left ||
                    event.clientX > bounds.right ||
                    event.clientY < bounds.top ||
                    event.clientY > bounds.bottom
                )
                    approval.close();
            }
        },
        { signal },
    );
    document.querySelectorAll("[data-registration-review]").forEach((row) => {
        row.tabIndex = 0;
        row.setAttribute("aria-haspopup", "dialog");
        const open = () => {
            trigger = row;
            dialog
                .querySelector("[data-registration-content]")
                .replaceChildren(
                    document
                        .getElementById(row.dataset.registrationReview)
                        .content.cloneNode(true),
                );
            dialog.querySelector("[data-registration-feedback]").hidden = true;
            dialog.showModal();
        };
        row.addEventListener("click", open, { signal });
        row.addEventListener(
            "keydown",
            (event) => {
                if (event.key === "Enter" || event.key === " ") {
                    event.preventDefault();
                    open();
                }
            },
            { signal },
        );
    });
    dialog.addEventListener(
        "click",
        (event) => {
            if (event.target.closest("[data-close-review]")) dialog.close();
            const action = event.target.closest("[data-registration-action]");
            if (action) {
                if (action.dataset.registrationAction === "Approval") {
                    approval.querySelector(
                        "#registration-approval-description",
                    ).textContent =
                        `Are you sure you want to approve ${trigger.dataset.applicantName}? They will be able to access their account after approval.`;
                    approval.showModal();
                    approval.querySelector("[autofocus]").focus();
                    return;
                }
                rejection.querySelector(
                    "#registration-rejection-description",
                ).textContent =
                    `Are you sure you want to reject ${trigger.dataset.applicantName}? Their registration request will be permanently removed. No customer account will be created.`;
                rejection.querySelector("[data-rejection-form]").action =
                    trigger.dataset.rejectUrl;
                rejection.showModal();
                rejection.querySelector("[autofocus]").focus();
            }
            if (event.target === dialog) {
                const bounds = dialog.getBoundingClientRect();
                if (
                    event.clientX < bounds.left ||
                    event.clientX > bounds.right ||
                    event.clientY < bounds.top ||
                    event.clientY > bounds.bottom
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
    signal.addEventListener("abort", () => {
        if (approval.open) approval.close();
        if (rejection.open) rejection.close();
        if (dialog.open) dialog.close();
    });
}
