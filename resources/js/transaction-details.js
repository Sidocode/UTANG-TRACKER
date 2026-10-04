export function initializeTransactionDetails(signal) {
    const modal = document.createElement("dialog");
    modal.className = "transaction-details-modal recorded-transaction-modal";
    modal.setAttribute("aria-label", "Transaction details");
    document.body.append(modal);
    const confirmation = document.createElement("dialog");
    confirmation.className =
        "transaction-details-modal transaction-save-confirmation";
    confirmation.setAttribute("aria-labelledby", "save-confirmation-title");
    confirmation.setAttribute(
        "aria-describedby",
        "save-confirmation-description",
    );
    document.body.append(confirmation);
    let confirmationAction = "save";
    confirmation.addEventListener("close", () => {
        if (modal.open)
            modal
                .querySelector(
                    `[data-transaction-preview="${confirmationAction}"]`,
                )
                ?.focus({ preventScroll: true });
    });
    confirmation.addEventListener("click", (event) => {
        if (event.target.closest("[data-cancel-save]")) confirmation.close();
        if (event.target.closest("[data-confirm-save]")) {
            confirmation.close();
            const feedback = modal.querySelector("[data-transaction-feedback]");
            feedback.hidden = false;
            if (confirmationAction === "modify") {
                window.location.assign(
                    modal.querySelector(".transaction-details-body").dataset
                        .editUrl,
                );
                return;
            }
            feedback.textContent = "This transaction is already saved.";
        }
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
    });
    let request;
    let trigger;
    modal.addEventListener("close", () => {
        request?.abort();
        trigger?.focus({ preventScroll: true });
    });
    modal.addEventListener("click", (event) => {
        const action = event.target.closest("[data-transaction-preview]");
        if (action) {
            confirmationAction = action.dataset.transactionPreview;
            {
                confirmation.classList.toggle(
                    "transaction-modify-confirmation",
                    confirmationAction === "modify",
                );
                confirmation.replaceChildren(
                    modal
                        .querySelector(
                            `[data-${confirmationAction}-confirmation]`,
                        )
                        .content.cloneNode(true),
                );
                confirmation.showModal();
                confirmation.querySelector("[autofocus]").focus();
                return;
            }
        }
        if (event.target.closest("[data-close-transaction]")) modal.close();
        if (event.target === modal) {
            const rect = modal.getBoundingClientRect();
            if (
                event.clientX < rect.left ||
                event.clientX > rect.right ||
                event.clientY < rect.top ||
                event.clientY > rect.bottom
            )
                modal.close();
        }
    });
    signal.addEventListener("abort", () => {
        request?.abort();
        modal.remove();
        confirmation.remove();
    });
    return async (row) => {
        request?.abort();
        const current = new AbortController();
        request = current;
        trigger = row;
        const message = document.createElement("p");
        message.setAttribute("role", "status");
        message.textContent = "Loading transaction…";
        const close = document.createElement("button");
        close.type = "button";
        close.dataset.closeTransaction = "";
        close.className = "transaction-modal-dismiss";
        close.textContent = "Close";
        modal.replaceChildren(message, close);
        modal.showModal();
        try {
            const response = await fetch(row.dataset.transactionDetails, {
                signal: current.signal,
            });
            if (!response.ok) throw new Error("Unable to load transaction");
            const page = new DOMParser().parseFromString(
                await response.text(),
                "text/html",
            );
            const content = page.querySelector(".transaction-details-body");
            if (!content) throw new Error("Missing transaction details");
            if (current.signal.aborted) return;
            modal.replaceChildren(content);
            modal
                .querySelector("[data-close-transaction]")
                .focus({ preventScroll: true });
        } catch (error) {
            if (error.name !== "AbortError")
                message.textContent =
                    "Could not load this transaction. Close and tap the row to retry.";
        }
    };
}
