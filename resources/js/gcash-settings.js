import { saveRequest } from "./backend";

export function initializeGcashSettings(signal) {
    const modal = document.querySelector(".gcash-settings-modal");
    const trigger = document.querySelector("[data-open-gcash-settings]");
    if (!modal || !trigger) return;
    const form = modal.querySelector("form");
    const fileInput = form.elements.qrImage;
    const preview = modal.querySelector("[data-qr-preview]");
    const placeholder = modal.querySelector("[data-qr-placeholder]");
    const feedback = modal.querySelector("[data-gcash-settings-feedback]");
    let imageUrl;
    const clearImage = () => {
        preview.hidden = !preview.dataset.savedSrc;
        if (preview.dataset.savedSrc) preview.src = preview.dataset.savedSrc;
        else preview.removeAttribute("src");
        placeholder.hidden = Boolean(preview.dataset.savedSrc);
        if (imageUrl) URL.revokeObjectURL(imageUrl);
        imageUrl = undefined;
    };
    trigger.addEventListener("click", () => modal.showModal(), { signal });
    fileInput.addEventListener(
        "change",
        () => {
            clearImage();
            fileInput.setCustomValidity("");
            const file = fileInput.files[0];
            if (!file) return;
            if (
                !["image/png", "image/jpeg", "image/webp"].includes(
                    file.type,
                ) ||
                file.size > 5 * 1024 * 1024
            ) {
                fileInput.setCustomValidity(
                    "Choose a PNG, JPG, or WebP image no larger than 5 MB.",
                );
                fileInput.reportValidity();
                return;
            }
            imageUrl = URL.createObjectURL(file);
            preview.src = imageUrl;
            preview.hidden = false;
            placeholder.hidden = true;
        },
        { signal },
    );
    preview.addEventListener(
        "error",
        () => {
            clearImage();
            fileInput.setCustomValidity(
                "This image could not be opened. Please choose another image.",
            );
            fileInput.reportValidity();
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
        async (event) => {
            event.preventDefault();
            if (!form.reportValidity()) return;
            const result = await saveRequest(
                form.action,
                new FormData(form),
                feedback,
                form.querySelector('[type="submit"]'),
            );
            if (result) window.location.reload();
        },
        { signal },
    );
    modal.addEventListener(
        "click",
        (event) => {
            if (event.target.closest("[data-close-gcash-settings]"))
                modal.close();
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
        },
        { signal },
    );
    modal.addEventListener(
        "close",
        () => {
            form.reset();
            fileInput.setCustomValidity("");
            clearImage();
            feedback.hidden = true;
            trigger.focus({ preventScroll: true });
        },
        { signal },
    );
    signal.addEventListener(
        "abort",
        () => {
            clearImage();
            if (modal.open) modal.close();
        },
        { once: true },
    );
}
