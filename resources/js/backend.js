export function submissionKey(form) {
    if (!form.dataset.submissionKey) {
        const bytes = crypto.getRandomValues(new Uint8Array(16));
        bytes[6] = (bytes[6] & 15) | 64;
        bytes[8] = (bytes[8] & 63) | 128;
        const hex = [...bytes]
            .map((byte) => byte.toString(16).padStart(2, "0"))
            .join("");
        form.dataset.submissionKey = `${hex.slice(0, 8)}-${hex.slice(8, 12)}-${hex.slice(12, 16)}-${hex.slice(16, 20)}-${hex.slice(20)}`;
    }
    return form.dataset.submissionKey;
}

export async function saveRequest(
    url,
    payload,
    feedback,
    button,
    method = "POST",
) {
    if (button?.dataset.saving) return null;
    if (button) {
        button.dataset.saving = "true";
        button.disabled = true;
    }
    try {
        const multipart = payload instanceof FormData;
        const response = await fetch(url, {
            method,
            credentials: "same-origin",
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN":
                    document.querySelector('meta[name="csrf-token"]')
                        ?.content ||
                    document.querySelector('input[name="_token"]')?.value ||
                    "",
                ...(multipart ? {} : { "Content-Type": "application/json" }),
            },
            body: multipart ? payload : JSON.stringify(payload),
        });
        if (
            !response.headers.get("content-type")?.includes("application/json")
        ) {
            throw new Error(
                "Your session may have expired. Reload the page and sign in again before retrying.",
            );
        }
        const result = await response.json();
        if (!response.ok) {
            const message = Object.values(result.errors || {})
                .flat()
                .join(" ");
            throw new Error(
                message ||
                    (response.status === 419
                        ? "Your session expired. Reload the page and sign in again."
                        : result.message ||
                          "Could not save. Please try again."),
            );
        }
        return result;
    } catch (error) {
        if (feedback) {
            feedback.textContent = error.message;
            feedback.hidden = false;
        }
        return null;
    } finally {
        if (button) {
            delete button.dataset.saving;
            button.disabled = false;
        }
    }
}
