'use strict';

self.addEventListener('install', (event) => {

});

self.addEventListener('fetch', event => {
// 	if (event.request.mode === 'navigate') {
// 		// See /web/fundamentals/getting-started/primers/async-functions
// 		// for an async/await primer.
// 		event.respondWith(async function() {
// 			// Optional: Normalize the incoming URL by removing query parameters.
// 			// Instead of https://example.com/page?key=value,
// 			// use https://example.com/page when reading and writing to the cache.
// 			// For static HTML documents, it's unlikely your query parameters will
// 			// affect the HTML returned. But if you do use query parameters that
// 			// uniquely determine your HTML, modify this code to retain them.
// 			const normalizedUrl = new URL(event.request.url);
// 			normalizedUrl.search = '';
//
// 			// Create promises for both the network response,
// 			// and a copy of the response that can be used in the cache.
// 			const fetchResponseP = fetch(normalizedUrl);
// 			const fetchResponseCloneP = fetchResponseP.then(r => r.clone());
//
// 			// event.waitUntil() ensures that the service worker is kept alive
// 			// long enough to complete the cache update.
// 			event.waitUntil(async function() {
// 				const cache = await caches.open('my-cache-name');
// 				await cache.put(normalizedUrl, await fetchResponseCloneP);
// 			}());
//
// 			// Prefer the cached response, falling back to the fetch response.
// 			return (await caches.match(normalizedUrl)) || fetchResponseP;
// 		}());
// 	}
});

// self.addEventListener('push', function (event) {
// 	console.log('[Service Worker] Push Received.');
// 	// console.log(`[Service Worker] Push had this data: "${event.data.text()}"`);
//
// 	let text = event.data.text();
// 	if (text) {
// 		let json = JSON.parse(text);
// 		if (json['title'] !== undefined && json['body'] !== undefined && json['data'] !== undefined) {
// 			const title = json.title;
// 			const options = {
// 				body: json.body,
// 				icon: 'images/icon.png',
// 				vibrate: [200, 100, 200, 100, 200, 100, 400],
// 				badge: 'images/badge.png',
// 				data: json.data
// 			};
//
// 			event.waitUntil(self.registration.showNotification(title, options));
// 		}
// 	}
// });
//
// self.addEventListener('notificationclick', function (event) {
// 	console.log('[Service Worker] Notification click Received.');
//
// 	let data = ''
// 	if (event.notification.data !== undefined) {
// 		data = event.notification.data;
// 	}
//
// 	event.notification.close();
//
// 	if (data.targetLink !== undefined) {
// 		event.waitUntil(clients.openWindow(data.targetLink));
// 	}
// });
//
// function setCookie(cname, cvalue, exdays) {
// 	var d = new Date();
// 	d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
// 	var expires = "expires=" + d.toUTCString();
// 	document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
// }
//
// // Listen to  `pushsubscriptionchange` event which is fired when
// // subscription expires. Subscribe again and register the new subscription
// // in the server by sending a POST request with endpoint. Real world
// // application would probably use also user identification.
// self.addEventListener('pushsubscriptionchange', function (event) {
// 	console.log('Subscription expired');
// 	event.waitUntil(
// 		self.registration.pushManager.subscribe({userVisibleOnly: true})
// 			.then(function (subscription) {
// 				// console.log('Subscribed after expiration', subscription.endpoint);
// 				const key = subscription.getKey('p256dh');
// 				const token = subscription.getKey('auth');
// 				const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];
//
// 				return fetch('/user-push-notification/', {
// 					method,
// 					body: JSON.stringify({
// 						endpoint: subscription.endpoint,
// 						publicKey: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
// 						authToken: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
// 						contentEncoding,
// 					}),
// 				})
// 					.then(response => response.json())
// 					.then(data => {
// 						if (data.hash !== undefined) {
// 							setCookie('push_notification_hash', data.hash, 1);
// 						}
// 					})
// 					.catch((error) => {
// 						console.error('Error:', error);
// 					});
// 			})
// 	);
// });