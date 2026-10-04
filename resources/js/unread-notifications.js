let busy = false;
export async function refreshUnreadNotifications() {
    const icon = document.querySelector("[data-unread-url]");
    if (!icon || busy || document.hidden) return;
    busy = true;
    try {
        const response = await fetch(icon.dataset.unreadUrl, {
            headers: { Accept: "application/json" },
            cache: "no-store",
        });
        if (!response.ok) return;
        const { unread } = await response.json();
        if (!icon.isConnected) return;
        icon.querySelector("[data-unread-dot]").hidden = unread === 0;
        icon.setAttribute(
            "aria-label",
            unread ? `Notifications, ${unread} unread` : "Notifications",
        );
    } catch {
        /* Keep the last known unread state when offline. */
    } finally {
        busy = false;
    }
}
setInterval(refreshUnreadNotifications, 15000);
document.addEventListener("visibilitychange", refreshUnreadNotifications);
window.addEventListener("focus", refreshUnreadNotifications);
