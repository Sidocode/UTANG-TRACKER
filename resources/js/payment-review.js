import { saveRequest } from "./backend";

export function initializePaymentReview(signal) {
    const dialog = document.querySelector("[data-payment-review-dialog]");
    if (!dialog) return;
    const feedback = document.querySelector("[data-payment-review-feedback]");
    let trigger;
    let action;
    document.querySelectorAll("[data-payment-review]").forEach((button) => {
        button.addEventListener(
            "click",
            () => {
                trigger = button;
                action = button.dataset.paymentReview;
                const rejecting = action === "reject";
                dialog.querySelector("h2").textContent = rejecting
                    ? "Reject GCash Payment?"
                    : "Verify GCash Payment?";
                dialog.querySelector(
                    "#payment-review-description",
                ).textContent = dialog
                    .querySelector("[data-review-" + action + "]")
                    .content.textContent.trim();
                dialog.querySelector(
                    "[data-confirm-payment-review] span",
                ).textContent = rejecting ? "Reject payment" : "Verify payment";
                feedback.hidden = true;
                dialog.showModal();
            },
            { signal },
        );
    });
    dialog.addEventListener(
        "click",
        async (event) => {
            if (event.target.closest("[data-dismiss-payment-review]"))
                dialog.close();
            if (event.target.closest("[data-confirm-payment-review]")) {
                const result = await saveRequest(
                    dialog.dataset.reviewUrl,
                    { action },
                    feedback,
                    event.target.closest("[data-confirm-payment-review]"),
                );
                dialog.close();
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
