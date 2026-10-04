import "./profile-dialogs";
import { refreshUnreadNotifications } from "./unread-notifications";
import { saveRequest, submissionKey } from "./backend";
import { initializeAccountConfirmation } from "./account-confirmation";
import { initializeGcashSettings } from "./gcash-settings";
import { initializePaymentReview } from "./payment-review";
import { startOwnerNavigation } from "./owner-navigation";
import { initializeTransactionDetails } from "./transaction-details";
import { initializeRegistrationReview } from "./registration-review";
import { initializeCustomerRegistration } from "./customer-registration";
import { initializeRecordPayment } from "./record-payment";

function initializePage() {
    const lifecycle = new AbortController();
    document.querySelectorAll("[data-owner-notification]").forEach((card) => {
        const read = async () => {
            if (!card.classList.contains("notification-card-new")) return;
            const result = await saveRequest(
                card.dataset.readUrl,
                {},
                card.querySelector(".notification-copy p"),
                card,
            );
            if (result) {
                card.classList.remove("notification-card-new");
                card.querySelector(".notification-marker").replaceChildren();
                refreshUnreadNotifications();
            }
        };
        card.addEventListener("click", read, { signal: lifecycle.signal });
        card.addEventListener(
            "keydown",
            (event) => {
                if (event.key === "Enter" || event.key === " ") {
                    event.preventDefault();
                    read();
                }
            },
            { signal: lifecycle.signal },
        );
    });
    initializeRecordPayment(lifecycle.signal);
    initializeGcashSettings(lifecycle.signal);
    initializeAccountConfirmation(lifecycle.signal);
    initializePaymentReview(lifecycle.signal);
    initializeCustomerRegistration(lifecycle.signal);
    initializeRegistrationReview(lifecycle.signal);
    const openTransactionDetails = initializeTransactionDetails(
        lifecycle.signal,
    );
    const dialog = document.querySelector("#preview-dialog");
    const dropdowns = [...document.querySelectorAll("[data-dropdown]")];
    const closeDropdown = (dropdown) => {
        dropdown
            .querySelector("[data-dropdown-trigger]")
            .setAttribute("aria-expanded", "false");
        dropdown.querySelector(".customer-dropdown-list").hidden = true;
    };
    dropdowns.forEach((dropdown) => {
        const trigger = dropdown.querySelector("[data-dropdown-trigger]");
        const list = dropdown.querySelector(".customer-dropdown-list");
        const options = [...list.querySelectorAll("button")];
        const open = () => {
            dropdowns.forEach(closeDropdown);
            list.hidden = false;
            trigger.setAttribute("aria-expanded", "true");
        };
        trigger.addEventListener("click", () => {
            if (list.hidden) open();
            else closeDropdown(dropdown);
        });
        trigger.addEventListener("keydown", (event) => {
            if (event.key === "ArrowDown" || event.key === "ArrowUp") {
                event.preventDefault();
                open();
                options[
                    event.key === "ArrowDown" ? 0 : options.length - 1
                ].focus();
            }
        });
        options.forEach((option, index) => {
            option.addEventListener("click", () => {
                const input = dropdown.querySelector("input");
                input.value =
                    input.value === option.dataset.dropdownValue
                        ? ""
                        : option.dataset.dropdownValue;
                options.forEach((item) => {
                    const selected = item.dataset.dropdownValue === input.value;
                    item.setAttribute("aria-pressed", String(selected));
                    item.title = selected
                        ? "Select again to clear this filter"
                        : item.textContent.trim();
                });
                closeDropdown(dropdown);
                trigger.focus({ preventScroll: true });
                input.form.requestSubmit();
            });
            option.addEventListener("keydown", (event) => {
                const direction =
                    event.key === "ArrowDown"
                        ? 1
                        : event.key === "ArrowUp"
                          ? -1
                          : 0;
                if (direction) {
                    event.preventDefault();
                    options[
                        (index + direction + options.length) % options.length
                    ].focus();
                }
            });
        });
        dropdown.addEventListener("keydown", (event) => {
            if (event.key === "Escape" && !list.hidden) {
                event.preventDefault();
                closeDropdown(dropdown);
                trigger.focus();
            }
        });
        dropdown.addEventListener("focusout", (event) => {
            if (!dropdown.contains(event.relatedTarget))
                closeDropdown(dropdown);
        });
    });
    document.addEventListener(
        "pointerdown",
        (event) => {
            dropdowns.forEach((dropdown) => {
                if (!dropdown.contains(event.target)) closeDropdown(dropdown);
            });
        },
        { signal: lifecycle.signal },
    );
    document.querySelectorAll("[data-preview]").forEach((button) => {
        button.addEventListener(
            "click",
            () => {
                document.querySelector("#preview-title").textContent =
                    button.dataset.preview;
                document.querySelector("#preview-message").textContent =
                    button.dataset.message;
                dialog.showModal();
            },
            { signal: lifecycle.signal },
        );
    });
    dialog?.addEventListener(
        "click",
        (event) => {
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
        { signal: lifecycle.signal },
    );

    // Select a record on click or keyboard activation; native swiping stays available.
    function initializeRowSelection(body) {
        const rows = [...body.rows].filter(
            (row) => !row.querySelector(".empty-state"),
        );
        const selectRow = (selected) => {
            rows.forEach((row) => {
                row.classList.toggle("is-selected", row === selected);
                if (row === selected) row.setAttribute("aria-current", "true");
                else row.removeAttribute("aria-current");
            });
        };
        rows.forEach((row) => {
            if (row.hasAttribute("data-registration-review")) return;
            if (row.hasAttribute("data-transaction-details")) {
                row.tabIndex = 0;
                row.setAttribute("aria-haspopup", "dialog");
                row.addEventListener("click", () =>
                    openTransactionDetails(row),
                );
                row.addEventListener("keydown", (event) => {
                    if (event.key === "Enter" || event.key === " ") {
                        event.preventDefault();
                        openTransactionDetails(row);
                    }
                });
                return;
            }
            if (
                row.hasAttribute("data-customer-row") ||
                row.hasAttribute("data-detail-row")
            ) {
                row.addEventListener("click", (event) => {
                    if (!event.target.closest("a, button"))
                        row.querySelector("a").click();
                });
                return;
            }
            row.tabIndex = 0;
            row.addEventListener("click", () => selectRow(row));
            row.addEventListener("keydown", (event) => {
                if (event.key === "Enter" || event.key === " ") {
                    event.preventDefault();
                    selectRow(row);
                }
            });
        });
    }
    document
        .querySelectorAll(".table-scroll tbody")
        .forEach(initializeRowSelection);

    const customerSearch = document.querySelector(".customer-search input");
    if (customerSearch) {
        let timer;
        let controller;
        let revision = 0;
        lifecycle.signal.addEventListener("abort", () => {
            clearTimeout(timer);
            controller?.abort();
            revision++;
        });
        const status = document.querySelector("#customer-search-status");
        const table = document.querySelector(
            ".customers-table, .transactions-table, .utang-table, .payment-records-table, .audit-table",
        );
        const tableSelector = table.classList.contains("audit-table")
            ? ".audit-table"
            : table.classList.contains("payment-records-table")
              ? ".payment-records-table"
              : table.classList.contains("utang-table")
                ? ".utang-table"
                : table.classList.contains("transactions-table")
                  ? ".transactions-table"
                  : ".customers-table";
        const recordName =
            tableSelector === ".audit-table"
                ? "activity"
                : tableSelector === ".payment-records-table"
                  ? "payment"
                  : tableSelector === ".utang-table"
                    ? "debt"
                    : tableSelector === ".transactions-table"
                      ? "transaction"
                      : "customer";
        const updateResults = (delay = 250) => {
            clearTimeout(timer);
            controller?.abort();
            const currentRevision = ++revision;
            table.setAttribute("aria-busy", "true");
            status.textContent = "Searching…";
            timer = setTimeout(async () => {
                controller = new AbortController();
                const url = new URL(customerSearch.form.action);
                url.search = new URLSearchParams(
                    new FormData(customerSearch.form),
                ).toString();
                try {
                    const response = await fetch(url, {
                        signal: controller.signal,
                    });
                    if (!response.ok) throw new Error("Search failed");
                    const page = new DOMParser().parseFromString(
                        await response.text(),
                        "text/html",
                    );
                    const body = page.querySelector(`${tableSelector} tbody`);
                    if (!body) throw new Error("Missing search results");
                    if (currentRevision !== revision) return;
                    table.querySelector("tbody").replaceWith(body);
                    initializeRowSelection(body);
                    const count = body.querySelectorAll(
                        "tr:not(:has(.empty-state))",
                    ).length;
                    status.textContent = count
                        ? `${count} ${recordName}${count === 1 ? "" : "s"} found.`
                        : `No ${recordName}s found.`;
                    history.replaceState(null, "", url);
                } catch (error) {
                    if (
                        error.name !== "AbortError" &&
                        currentRevision === revision
                    ) {
                        status.textContent =
                            "Search could not update. Press Enter to retry.";
                    }
                } finally {
                    if (currentRevision === revision)
                        table.removeAttribute("aria-busy");
                }
            }, delay);
        };
        customerSearch.addEventListener("input", () => updateResults(), {
            signal: lifecycle.signal,
        });
        customerSearch.form.addEventListener(
            "submit",
            (event) => {
                event.preventDefault();
                updateResults(0);
            },
            { signal: lifecycle.signal },
        );
    }

    const dateInput = document.querySelector("#transaction-date");
    if (dateInput) {
        dateInput.addEventListener("click", () => {
            if (typeof dateInput.showPicker === "function")
                dateInput.showPicker();
        });
        dateInput.addEventListener("change", () =>
            dateInput.form.requestSubmit(),
        );
    }

    const entryForm = document.querySelector("#transaction-entry-form");
    if (entryForm) {
        const confirmation = document.querySelector("#entry-confirmation");
        const items = document.querySelector("#entry-items");
        const template = document.querySelector("#entry-item-template");
        const feedback = document.querySelector("#entry-feedback");
        const money = (cents) =>
            "₱" +
            (cents / 100).toLocaleString("en-PH", {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        const update = () => {
            let total = 0;
            items.querySelectorAll(".entry-item-row").forEach((row) => {
                const quantity = row.querySelector("[data-item-quantity]");
                const price = row.querySelector("[data-item-price]");
                const cents =
                    quantity.validity.valid && price.validity.valid
                        ? Math.round(Number(price.value) * 100) *
                          Number(quantity.value)
                        : 0;
                row.querySelector("[data-item-subtotal]").value = money(cents);
                total += cents;
            });
            document.querySelector("#entry-total").value = money(total);
            feedback.textContent = "";
        };
        const addItem = (focus = false, item = null) => {
            const row = template.content.firstElementChild.cloneNode(true);
            if (item) {
                row.querySelector("[data-item-name]").value = item.itemName;
                row.querySelector("[data-item-quantity]").value = item.quantity;
                row.querySelector("[data-item-price]").value = item.price;
            }
            items.append(row);
            row.querySelector(".entry-remove").addEventListener("click", () => {
                row.remove();
                if (!items.children.length) addItem();
                update();
            });
            row.querySelectorAll("[data-step]").forEach((button) =>
                button.addEventListener("click", () => {
                    const input = row.querySelector("[data-item-quantity]");
                    input.value = Math.min(
                        9999,
                        Math.max(
                            0,
                            Math.trunc(Number(input.value) || 0) +
                                Number(button.dataset.step),
                        ),
                    );
                    input.setCustomValidity("");
                    update();
                }),
            );
            if (focus) row.querySelector("[data-item-name]").focus();
        };
        const existingItems = JSON.parse(
            document.querySelector("#entry-existing-items").textContent,
        );
        if (existingItems.length)
            existingItems.forEach((item) => addItem(false, item));
        else for (let index = 0; index < 5; index++) addItem();
        update();
        if (entryForm.dataset.saveMethod === "PATCH")
            document.querySelector("#entry-customer").disabled = true;
        confirmation.addEventListener("click", async (event) => {
            if (event.target.closest("[data-entry-cancel]"))
                confirmation.close();
            const button = event.target.closest("[data-entry-save]");
            if (!button) return;
            const payload = {
                customer: document.querySelector("#entry-customer").value,
                submissionKey: submissionKey(entryForm),
                items: [...items.children]
                    .filter((row) =>
                        row.querySelector("[data-item-name]").value.trim(),
                    )
                    .map((row) => ({
                        name: row
                            .querySelector("[data-item-name]")
                            .value.trim(),
                        quantity: row.querySelector("[data-item-quantity]")
                            .value,
                        price: row.querySelector("[data-item-price]").value,
                    })),
            };
            const result = await saveRequest(
                entryForm.action,
                payload,
                confirmation.querySelector("[data-entry-save-feedback]"),
                button,
                entryForm.dataset.saveMethod,
            );
            if (result) window.location.assign(result.redirect);
        });
        document
            .querySelector("#entry-add")
            .addEventListener("click", () => addItem(true));
        entryForm.addEventListener("input", (event) => {
            event.target.setCustomValidity?.("");
            update();
        });
        entryForm.addEventListener("submit", (event) => {
            event.preventDefault();
            let used = 0;
            for (const row of items.children) {
                const name = row.querySelector("[data-item-name]");
                const quantity = row.querySelector("[data-item-quantity]");
                const price = row.querySelector("[data-item-price]");
                if (
                    !name.value.trim() &&
                    !Number(quantity.value) &&
                    !Number(price.value)
                )
                    continue;
                used++;
                name.setCustomValidity(
                    name.value.trim() ? "" : "Enter an item name.",
                );
                quantity.setCustomValidity(
                    Number(quantity.value) > 0
                        ? ""
                        : "Quantity must be at least 1.",
                );
                price.setCustomValidity(
                    Number(price.value) > 0
                        ? ""
                        : "Enter a price greater than zero.",
                );
            }
            if (!used) {
                feedback.textContent =
                    "Add at least one item with a quantity and price.";
                items.querySelector("[data-item-name]").focus();
                return;
            }
            if (!entryForm.reportValidity()) return;
            confirmation.querySelector(
                "[data-entry-confirm-description]",
            ).textContent =
                `Save this transaction for ${document.querySelector("#entry-customer").selectedOptions[0].textContent} with a total of ${document.querySelector("#entry-total").value}?`;
            confirmation.querySelector("[data-entry-save-feedback]").hidden =
                true;
            confirmation.showModal();
        });
    }

    return () => lifecycle.abort();
}

startOwnerNavigation(initializePage);
