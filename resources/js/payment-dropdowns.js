export function initializePaymentDropdowns(form, signal) {
    const controls = [];
    const closeAll = () =>
        controls.forEach(({ button, panel }) => {
            panel.hidden = true;
            button.setAttribute("aria-expanded", "false");
        });
    form.querySelectorAll("select").forEach((select) => {
        const label = select.closest("label");
        const name = label.firstChild.textContent.trim();
        const wrapper = document.createElement("div");
        wrapper.className = "payment-custom-select";
        const button = document.createElement("button");
        button.type = "button";
        button.className = "payment-select-trigger";
        button.setAttribute("aria-label", name);
        button.setAttribute("aria-haspopup", "listbox");
        button.setAttribute("aria-expanded", "false");
        const panel = document.createElement("div");
        panel.className = "payment-select-panel";
        panel.id = `payment-options-${select.name}`;
        panel.setAttribute("role", "listbox");
        panel.setAttribute("aria-label", name);
        panel.hidden = true;
        button.setAttribute("aria-controls", panel.id);
        // Place interactive controls outside the native select's wrapping label.
        const field = document.createElement("div");
        field.className = "payment-select-field";
        label.before(field);
        field.append(label, wrapper);
        wrapper.append(button, panel);
        select.hidden = true;
        const refresh = () => {
            button.textContent = select.selectedOptions[0]?.textContent || "";
            button.disabled = select.disabled;
            button.classList.toggle("is-placeholder", !select.value);
            panel.replaceChildren();
            [...select.options]
                .filter((option) => option.value)
                .forEach((option) => {
                    const item = document.createElement("button");
                    item.type = "button";
                    item.textContent = option.textContent;
                    item.setAttribute("role", "option");
                    item.setAttribute("aria-selected", String(option.selected));
                    item.addEventListener("click", () => {
                        select.value = option.value;
                        select.dispatchEvent(
                            new Event("change", { bubbles: true }),
                        );
                        refresh();
                        closeAll();
                        button.focus();
                    });
                    panel.append(item);
                });
        };
        const open = () => {
            closeAll();
            refresh();
            panel.hidden = false;
            button.setAttribute("aria-expanded", "true");
        };
        button.addEventListener(
            "click",
            () => {
                if (panel.hidden) open();
                else closeAll();
            },
            { signal },
        );
        button.addEventListener(
            "keydown",
            (event) => {
                if (event.key === "ArrowDown" || event.key === "ArrowUp") {
                    event.preventDefault();
                    open();
                    (event.key === "ArrowDown"
                        ? panel.firstElementChild
                        : panel.lastElementChild
                    )?.focus();
                }
            },
            { signal },
        );
        wrapper.addEventListener(
            "keydown",
            (event) => {
                if (event.key === "Escape" && !panel.hidden) {
                    event.preventDefault();
                    event.stopPropagation();
                    closeAll();
                    button.focus();
                }
                if (event.target === button) return;
                const items = [...panel.children];
                const index = items.indexOf(event.target);
                if (
                    ["ArrowDown", "ArrowUp", "Home", "End"].includes(
                        event.key,
                    ) &&
                    items.length
                ) {
                    event.preventDefault();
                    const next =
                        event.key === "Home"
                            ? 0
                            : event.key === "End"
                              ? items.length - 1
                              : (index +
                                    (event.key === "ArrowDown" ? 1 : -1) +
                                    items.length) %
                                items.length;
                    items[next].focus();
                }
            },
            { signal },
        );
        wrapper.addEventListener(
            "focusout",
            (event) => {
                if (!wrapper.contains(event.relatedTarget)) closeAll();
            },
            { signal },
        );
        select.addEventListener(
            "invalid",
            (event) => {
                event.preventDefault();
                if (form.querySelector("select:invalid") === select) {
                    button.focus();
                    open();
                    const feedback = form.querySelector(
                        "[data-payment-feedback]",
                    );
                    feedback.hidden = false;
                    feedback.textContent = `Please select ${name.toLowerCase()}.`;
                }
            },
            { signal },
        );
        controls.push({ button, panel, refresh });
        refresh();
    });
    document.addEventListener(
        "pointerdown",
        (event) => {
            if (!event.target.closest(".payment-custom-select")) closeAll();
        },
        { signal },
    );
    return () => {
        closeAll();
        controls.forEach((control) => control.refresh());
    };
}
