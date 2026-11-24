self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil((async () => {
        const windows = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
        const app = windows.find((client) => client.url.startsWith(self.registration.scope));
        if (app) {
            await app.focus();
        } else {
            await self.clients.openWindow(new URL('index.php?page=notifications', self.registration.scope).href);
        }
    })());
});
