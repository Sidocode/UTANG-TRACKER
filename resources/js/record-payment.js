import { initializePaymentDropdowns } from "./payment-dropdowns";
import { saveRequest, submissionKey } from "./backend";

export function initializeRecordPayment(signal) {
    const modal = document.querySelector(".record-payment-modal");
    const trigger = document.querySelector("[data-open-payment]");
    if (!modal || !trigger) return;
    const form = modal.querySelector("form");
    const feedback = modal.querySelector("[data-payment-feedback]");
    const debt = form.elements.debt;
    const confirmation = document.querySelector(".payment-confirmation-dialog");
    const refreshDropdowns = initializePaymentDropdowns(form, signal);
    const resetDebts = () => {
        debt.replaceChildren(new Option("select transaction", ""));
        debt.disabled = !form.elements.customer.value;
        form.elements.amount.removeAttribute("max");
    };
    trigger.addEventListener("click", () => modal.showModal(), { signal });
    form.elements.customer.addEventListener(
        "change",
        () => {
            resetDebts();
            modal
                .querySelector("[data-payment-debts]")
                .content.querySelectorAll("option")
                .forEach((option) => {
                    if (
                        option.dataset.customer === form.elements.customer.value
                    )
                        debt.append(option.cloneNode(true));
                });
            refreshDropdowns();
            feedback.hidden = true;
        },
        { signal },
    );
    debt.addEventListener(
        "change",
        () => {
            const remaining = debt.selectedOptions[0]?.dataset.remaining;
            if (remaining) form.elements.amount.max = remaining;
            else form.elements.amount.removeAttribute("max");
        },
        { signal },
    );
    modal.addEventListener(
        "click",
        (event) => {
            if (event.target.closest("[data-close-payment]")) modal.close();
            if (event.target === modal) {
                const r = modal.getBoundingClientRect();
                if (
                    event.clientX < r.left ||
                    event.clientX > r.right ||
                    event.clientY < r.top ||
                    event.clientY > r.bottom
                )
                    modal.close();
            }
        },
        { signal },
    );
    modal.addEventListener(
        "close",
        () => {
            form.reset();
            delete form.dataset.submissionKey;
            resetDebts();
            refreshDropdowns();
            feedback.hidden = true;
            trigger.focus({ preventScroll: true });
        },
        { signal },
    );
    form.addEventListener(
        "input",
        () => {
            feedback.hidden = true;
        },
        { signal },
    );
    form.addEventListener(
        "submit",
        (event) => {
            event.preventDefault();
            if (!form.reportValidity()) return;
            const amount = new Intl.NumberFormat("en-PH", {
                style: "currency",
                currency: "PHP",
            }).format(Number(form.elements.amount.value));
            confirmation.querySelector("h2").textContent =
                "Confirm Cash Payment?";
            confirmation.querySelector(
                "[data-payment-confirm-description]",
            ).textContent =
                "Are you sure you received " +
                amount +
                " in cash" +
                " from the selected customer? This amount will be applied to their outstanding balance.";
            confirmation.showModal();
        },
        { signal },
    );
    confirmation.addEventListener(
        "click",
        async (event) => {
            if (event.target.closest("[data-dismiss-payment-confirmation]"))
                confirmation.close();
            if (event.target.closest("[data-confirm-payment]")) {
                const payload = Object.fromEntries(new FormData(form));
                payload.submissionKey = submissionKey(form);
                const result = await saveRequest(
                    form.action,
                    payload,
                    feedback,
                    event.target.closest("[data-confirm-payment]"),
                );
                confirmation.close();
                if (result) window.location.assign(result.redirect);
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
        },
        { signal },
    );
    confirmation.addEventListener(
        "close",
        () => {
            if (modal.open)
                form.querySelector('[type="submit"]').focus({
                    preventScroll: true,
                });
        },
        { signal },
    );
    signal.addEventListener("abort", () => {
        if (confirmation.open) confirmation.close();
        if (modal.open) modal.close();
    });
}
