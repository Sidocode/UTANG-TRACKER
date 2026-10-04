export function startOwnerNavigation(initializePage) {
    let cleanup = initializePage();
    let pending;
    let revision = 0;

    async function navigate(url, replace = false) {
        pending?.abort();
        pending = new AbortController();
        const current = ++revision;
        const main = document.querySelector("#main-content");
        main.setAttribute("aria-busy", "true");
        try {
            const response = await fetch(url, { signal: pending.signal });
            if (!response.ok) throw new Error("Navigation failed");
            const page = new DOMParser().parseFromString(
                await response.text(),
                "text/html",
            );
            const next = page.querySelector("#main-content");
            if (!next || !page.querySelector(".owner-sidebar"))
                throw new Error("Full navigation required");
            if (current !== revision) return;
            cleanup();
            main.replaceChildren(...next.childNodes);
            document.title = page.title;
            document
                .querySelectorAll(".owner-sidebar .nav-item[href]")
                .forEach((link) => {
                    const active = [
                        ...page.querySelectorAll(
                            '.owner-sidebar [aria-current="page"]',
                        ),
                    ].some(
                        (item) =>
                            item.getAttribute("href") ===
                            link.getAttribute("href"),
                    );
                    link.classList.toggle("is-active", active);
                    if (active) link.setAttribute("aria-current", "page");
                    else link.removeAttribute("aria-current");
                });
            if (!replace) history.pushState(null, "", response.url);
            cleanup = initializePage();
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
        const link = event.target.closest("a[href]");
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
        if (
            url.origin !== location.origin ||
            !url.pathname.startsWith("/owner/") ||
            url.hash
        )
            return;
        event.preventDefault();
        if (url.href !== location.href) navigate(url);
    });
    window.addEventListener("popstate", () => navigate(location.href, true));
}
