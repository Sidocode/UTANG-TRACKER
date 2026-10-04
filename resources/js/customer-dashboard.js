import "./profile-dialogs";
import { refreshUnreadNotifications } from "./unread-notifications";
import "../css/customer-dashboard.css";
import { saveRequest, submissionKey } from "./backend";
import "../css/customer-confirmation.css";

const preview = document.querySelector(".customer-feature-preview");
document.addEventListener("click", (event) => {
    if (event.target.closest("[data-open-customer-logout]")) {
        document.querySelector(".customer-logout-dialog")?.showModal();
    }
    if (event.target.closest("[data-cancel-customer-logout]")) {
        document.querySelector(".customer-logout-dialog")?.close();
    }
    if (event.target.matches(".customer-logout-dialog")) {
        const rect = event.target.getBoundingClientRect();
        if (
            event.clientX < rect.left ||
            event.clientX > rect.right ||
            event.clientY < rect.top ||
            event.clientY > rect.bottom
        )
            event.target.close();
    }
    const button = event.target.closest("[data-customer-preview]");
    if (button) {
        preview.querySelector("h2").textContent =
            button.dataset.customerPreview;
        preview.showModal();
    }
});

// Keep the background and navigation mounted while changing customer screens.
const main = document.querySelector(".customer-dashboard-main");
const navigation = document.querySelector(".customer-bottom-nav");
let pending;
let revision = 0;

async function navigate(url, fromHistory = false) {
    pending?.abort();
    pending = new AbortController();
    const current = ++revision;
    main.setAttribute("aria-busy", "true");
    try {
        const response = await fetch(url, { signal: pending.signal });
        if (!response.ok) throw new Error("Navigation failed");
        const page = new DOMParser().parseFromString(
            await response.text(),
            "text/html",
        );
        const next = page.querySelector(".customer-dashboard-main");
        const header = page.querySelector(".customer-dashboard-header-inner");
        const nextNavigation = page.querySelector(".customer-bottom-nav");
        if (!next || !header || !nextNavigation)
            throw new Error("Full navigation required");
        if (current !== revision) return;
        // Decode incoming icons before swapping their sources to avoid an empty frame.
        await Promise.all(
            [...nextNavigation.querySelectorAll("a img")].map(async (icon) => {
                const image = new Image();
                image.src = icon.src;
                try {
                    await image.decode();
                } catch {
                    /* Native loading remains available. */
                }
            }),
        );
        if (current !== revision) return;
        main.replaceChildren(...next.childNodes);
        document.body.dataset.customerScreen = page.body.dataset.customerScreen;
        restoreNotificationReads();
        document
            .querySelector(".customer-header-copy")
            .replaceChildren(
                ...header.querySelector(".customer-header-copy").childNodes,
            );
        navigation.querySelectorAll("a").forEach((link) => {
            const replacement = [...nextNavigation.querySelectorAll("a")].find(
                (item) =>
                    item.getAttribute("href") === link.getAttribute("href"),
            );
            if (!replacement) return;
            if (replacement.hasAttribute("aria-current"))
                link.setAttribute("aria-current", "page");
            else link.removeAttribute("aria-current");
            link.querySelector("img").src =
                replacement.querySelector("img").src;
        });
        document.title = page.title;
        if (!fromHistory) history.pushState(null, "", response.url);
        main.tabIndex = -1;
        main.focus({ preventScroll: true });
    } catch (error) {
        if (error.name !== "AbortError" && current === revision)
            location.assign(url);
    } finally {
        if (current === revision) main.removeAttribute("aria-busy");
    }
}

document.addEventListener("click", (event) => {
    const link = event.target.closest(
        ".customer-bottom-nav a[href], .customer-notification-link, [data-customer-navigation]",
    );
    if (
        !link ||
        event.defaultPrevented ||
        event.button !== 0 ||
        event.metaKey ||
        event.ctrlKey ||
        event.shiftKey ||
        event.altKey ||
        link.target ||
        link.hasAttribute("download")
    )
        return;
    const url = new URL(link.href);
    if (url.origin !== location.origin || url.hash) return;
    event.preventDefault();
    // Also cancel an in-flight switch when the user taps the current page.
    if (url.href === location.href) {
        pending?.abort();
        revision++;
        main.removeAttribute("aria-busy");
        return;
    }
    navigate(url);
});
window.addEventListener("popstate", () => navigate(location.href, true));

// Delegation also handles forms inserted by customer navigation.
document.addEventListener("submit", (event) => {
    if (!event.target.matches("[data-customer-payment-form]")) return;
    event.preventDefault();
    const form = event.target;
    const dialog = document.querySelector("[data-payment-confirmation]");
    if (
        !dialog ||
        !form.reportValidity() ||
        !form.querySelector("[data-payment-debt]").value
    )
        return;
    const amount = Number(form.querySelector("[data-payment-amount]").value);
    dialog.querySelector("[data-confirm-payment-amount]").textContent =
        amount.toLocaleString("en-PH", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    dialog.showModal();
});
document.addEventListener("click", async (event) => {
    const dialog = document.querySelector("[data-payment-confirmation]");
    if (!dialog) return;
    if (event.target.closest("[data-cancel-payment-confirmation]"))
        dialog.close();
    if (event.target.closest("[data-confirm-payment-preview]")) {
        const form = document.querySelector("[data-customer-payment-form]");
        const payload = Object.fromEntries(new FormData(form));
        payload.submissionKey = submissionKey(form);
        const result = await saveRequest(
            form.action,
            payload,
            form.querySelector("#payment-preview-notice"),
            event.target.closest("[data-confirm-payment-preview]"),
        );
        dialog.close();
        if (result) navigate(result.redirect);
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
});
document.addEventListener("change", (event) => {
    if (!event.target.matches("[data-payment-debt]")) return;
    const form = event.target.closest("form");
    const remaining = event.target.selectedOptions[0]?.dataset.remaining;
    const amount = form.querySelector("[data-payment-amount]");
    const feedback = form.querySelector("[data-payment-remaining]");
    if (remaining) {
        amount.max = remaining;
        feedback.textContent = `Remaining balance: ₱${Number(remaining).toLocaleString("en-PH", { minimumFractionDigits: 2 })}`;
    } else {
        amount.removeAttribute("max");
        feedback.textContent = "";
    }
});

function restoreNotificationReads() {
    document.querySelectorAll("[data-notification-key]").forEach((card) => {
        const read = card.dataset.read === "true";
        card.classList.toggle("is-read", read);
        card.setAttribute(
            "aria-label",
            `${card.querySelector(".customer-notification-title").textContent}. ${read ? "Read" : "Mark as read"}`,
        );
    });
}
restoreNotificationReads();
document.addEventListener("click", async (event) => {
    const card = event.target.closest("[data-notification-key]");
    if (!card) return;
    if (card.dataset.read === "true") return;
    const result = await saveRequest(
        card.dataset.readUrl,
        {},
        card.querySelector(".customer-notification-message"),
        card,
    );
    if (result) {
        card.dataset.read = "true";
        restoreNotificationReads();
        refreshUnreadNotifications();
    }
});
