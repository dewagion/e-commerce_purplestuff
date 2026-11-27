  @if($notificationsEnabled && $notificationsRouteReady)
    <div id="adminRealtimeToast"
         class="pointer-events-none fixed bottom-5 right-5 z-[9999] hidden w-[340px] rounded-2xl border border-purple-200 bg-white/95 p-4 shadow-2xl backdrop-blur">
      <div class="flex items-start gap-3">
        <div class="mt-0.5 flex h-10 w-10 items-center justify-center rounded-full bg-purple-100 text-purple-700">
          <svg xmlns="http://www.w3.org/2000/svg"
               class="h-5 w-5"
               viewBox="0 0 24 24"
               fill="none"
               stroke="currentColor"
               stroke-width="1.8"
               stroke-linecap="round"
               stroke-linejoin="round">
            <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5"></path>
            <path d="M10 21a2 2 0 0 0 4 0"></path>
          </svg>
        </div>

        <div class="min-w-0 flex-1">
          <div class="text-sm font-bold text-slate-900">Notifikasi Baru</div>
          <div id="adminRealtimeToastMessage" class="mt-1 text-sm text-slate-600"></div>
          <a id="adminRealtimeToastLink"
             href="#"
             class="mt-3 inline-flex rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">
            Lihat Detail
          </a>
        </div>

        <button type="button"
                id="adminRealtimeToastClose"
                class="pointer-events-auto rounded-full p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
          <svg xmlns="http://www.w3.org/2000/svg"
               class="h-4 w-4"
               viewBox="0 0 24 24"
               fill="none"
               stroke="currentColor"
               stroke-width="2">
            <path d="M18 6 6 18"/>
            <path d="M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>

    <script type="module">
      (() => {
        const feedUrl = @json($notificationsFeedUrl);
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const pushEnabled = @json($firebasePushEnabled);
        const firebaseConfig = @json($firebaseWebConfig);
        const firebaseVapidKey = @json($firebaseVapidKey);
        const subscribeUrl = @json($adminPushSubscribeUrl);
        const unsubscribeUrl = @json($adminPushUnsubscribeUrl);
        const serviceWorkerUrl = @json($firebaseServiceWorkerUrl);
        const adminDefaultUrl = @json(route('admin.dashboard'));
        const adminPushTokenStorageKey = 'purplestuff_admin_fcm_token';
        const storageGet = (key) => {
          try {
            return window.localStorage?.getItem(key) || null;
          } catch (error) {
            return null;
          }
        };
        const storageSet = (key, value) => {
          try {
            window.localStorage?.setItem(key, value);
          } catch (error) {
            
          }
        };

        const badge = document.getElementById('adminNotificationBadge');
        const unreadText = document.getElementById('adminUnreadText');
        const list = document.getElementById('adminNotificationList');
        const readAllButton = document.getElementById('adminReadAllButton');
        const deleteAllButton = document.getElementById('adminDeleteAllButton');
        const pushStatus = document.getElementById('adminPushStatus');
        const pushToggleButton = document.getElementById('adminPushToggleButton');

        const toast = document.getElementById('adminRealtimeToast');
        const toastMessage = document.getElementById('adminRealtimeToastMessage');
        const toastLink = document.getElementById('adminRealtimeToastLink');
        const toastClose = document.getElementById('adminRealtimeToastClose');

        let lastNotificationId = null;
        let toastTimer = null;
        let currentFcmToken = storageGet(adminPushTokenStorageKey);
        let pushBusy = false;
        let foregroundHandlerRegistered = false;

        const escapeHtml = (value) =>
          String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

        const toSafeAdminLink = (value) => {
          try {
            const url = new URL(value || adminDefaultUrl, window.location.origin);
            const fallback = new URL(adminDefaultUrl, window.location.origin);

            if (url.origin !== window.location.origin || !url.pathname.startsWith('/admin')) {
              return fallback.href;
            }

            return url.href;
          } catch (error) {
            return adminDefaultUrl;
          }
        };

        const showDesktopNotification = (payload = {}) => {
          if (!('Notification' in window) || Notification.permission !== 'granted') return;

          const data = payload?.data || {};
          const notificationPayload = payload?.notification || {};
          const title = data.title || notificationPayload.title || 'Admin Purplestuff';
          const body = data.message || notificationPayload.body || 'Ada notifikasi baru.';
          const link = toSafeAdminLink(data.link || adminDefaultUrl);

          try {
            const notification = new Notification(title, {
              body,
              icon: data.icon || notificationPayload.icon || '/favicon.ico',
              tag: data.tag || data.notification_id || 'admin-notification',
              data: { link },
            });

            notification.onclick = () => {
              window.focus();
              window.location.href = notification.data?.link || adminDefaultUrl;
              notification.close();
            };
          } catch (error) {
            console.warn('Desktop notification tidak bisa ditampilkan.', error);
          }
        };

        const setPushStatus = (message, tone = 'muted') => {
          if (!pushStatus) return;
          pushStatus.textContent = message;
          pushStatus.className = 'mt-1 text-[11px]';

          if (tone === 'success') {
            pushStatus.classList.add('text-emerald-600');
          } else if (tone === 'error') {
            pushStatus.classList.add('text-rose-500');
          } else {
            pushStatus.classList.add('text-slate-500');
          }
        };

        const setPushButtonBusy = (busy, label = null) => {
          if (!pushToggleButton) return;
          pushBusy = busy;
          pushToggleButton.disabled = busy || !pushEnabled;
          pushToggleButton.classList.toggle('cursor-not-allowed', busy || !pushEnabled);
          pushToggleButton.classList.toggle('opacity-50', busy || !pushEnabled);

          if (label !== null) {
            pushToggleButton.textContent = label;
          }
        };

        const showToast = (message, link) => {
          if (!toast || !toastMessage || !toastLink) return;

          toastMessage.textContent = message || 'Ada notifikasi baru.';
          toastLink.href = toSafeAdminLink(link || adminDefaultUrl);
          toast.classList.remove('hidden');

          if (toastTimer) {
            clearTimeout(toastTimer);
          }

          toastTimer = setTimeout(() => {
            toast.classList.add('hidden');
          }, 5000);
        };

        toastClose?.addEventListener('click', () => {
          toast?.classList.add('hidden');
        });

        const renderNotifications = (notifications = []) => {
          if (!list) return;

          if (!notifications.length) {
            list.innerHTML = `
              <div id="adminNotificationEmpty"
                   class="rounded-2xl border border-dashed border-slate-200 px-4 py-10 text-center text-sm text-slate-500">
                Belum ada notifikasi.
              </div>
            `;
            return;
          }

          list.innerHTML = notifications.map((notification) => {
            const read = !!notification.read_at;
            const orderNumber = notification.order_number
              ? `<div class="mt-2 text-xs text-slate-600">Order: <span class="font-semibold">${escapeHtml(notification.order_number)}</span></div>`
              : '';

            const linkButton = notification.link
              ? `<a href="${escapeHtml(notification.link)}"
                    class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">
                    Lihat Order
                 </a>`
              : '';

            const readButton = !read
              ? `<form method="POST" action="${escapeHtml(notification.read_url)}">
                   <input type="hidden" name="_token" value="${escapeHtml(csrf)}">
                   <button type="submit"
                           class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-white">
                     Tandai dibaca
                   </button>
                 </form>`
              : '';

            const deleteButton = notification.delete_url
              ? `<form method="POST" action="${escapeHtml(notification.delete_url)}" onsubmit="return confirm('Hapus notifikasi ini?');">
                   <input type="hidden" name="_token" value="${escapeHtml(csrf)}">
                   <input type="hidden" name="_method" value="DELETE">
                   <button type="submit"
                           class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50">
                     Hapus
                   </button>
                 </form>`
              : '';

            return `
              <div class="mb-3 rounded-2xl border p-3 ${read ? 'border-slate-100 bg-slate-50' : 'border-purple-200 bg-purple-50/50'}">
                <div class="text-sm font-semibold text-slate-900">${escapeHtml(notification.message)}</div>
                <div class="mt-1 text-xs text-slate-500">${escapeHtml(notification.time_human || '')}</div>
                ${orderNumber}
                <div class="mt-3 flex flex-wrap items-center gap-2">
                  ${linkButton}
                  ${readButton}
                  ${deleteButton}
                </div>
              </div>
            `;
          }).join('');
        };

        const updateBadge = (count, total = null) => {
          if (!badge || !unreadText || !readAllButton) return;

          const totalNotifications = Number.isFinite(Number(total)) ? Number(total) : count;

          unreadText.textContent = count;
          badge.textContent = count;

          if (count > 0) {
            badge.classList.remove('hidden');
            badge.classList.add('flex');
            readAllButton.classList.remove('hidden');
          } else {
            badge.classList.add('hidden');
            badge.classList.remove('flex');
            readAllButton.classList.add('hidden');
          }

          if (deleteAllButton) {
            if (totalNotifications > 0) {
              deleteAllButton.classList.remove('hidden');
            } else {
              deleteAllButton.classList.add('hidden');
            }
          }
        };

        const fetchFeed = async (showNewToast = false) => {
          if (!feedUrl) return;

          try {
            const response = await fetch(feedUrl, {
              headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
              },
              credentials: 'same-origin',
            });

            if (!response.ok) return;

            const data = await response.json();

            renderNotifications(data.notifications || []);
            updateBadge(data.unread_count || 0, data.total_count ?? (data.notifications || []).length);

            const latest = data.notifications?.[0] || null;
            if (showNewToast && latest && lastNotificationId && latest.id !== lastNotificationId) {
              showToast(latest.message, latest.link);
            }

            lastNotificationId = data.latest_id || latest?.id || null;
          } catch (error) {
            console.error('Gagal mengambil notifikasi admin.', error);
          }
        };

        const unregisterLegacyRootFirebaseWorker = async () => {
          if (!('serviceWorker' in navigator)) return;

          try {
            const registration = await navigator.serviceWorker.getRegistration('/');
            const activeScript = registration?.active?.scriptURL || registration?.waiting?.scriptURL || registration?.installing?.scriptURL || '';
            const legacyScript = `${window.location.origin}/firebase-messaging-sw.js`;

            if (registration && activeScript === legacyScript) {
              await registration.unregister();
            }
          } catch (error) {
            console.warn('Gagal membersihkan service worker Firebase lama.', error);
          }
        };

        const bindForegroundMessaging = (messaging, onMessageHandler) => {
          if (foregroundHandlerRegistered || !messaging || !onMessageHandler) return;

          foregroundHandlerRegistered = true;

          onMessageHandler(messaging, (payload) => {
            const data = payload?.data || {};
            const notification = payload?.notification || {};
            const message = data.message || notification.body || 'Ada notifikasi baru.';
            const link = toSafeAdminLink(data.link || adminDefaultUrl);

            if (data.notification_id) {
              lastNotificationId = data.notification_id;
            }

            showToast(message, link);
            showDesktopNotification(payload);
            fetchFeed(false);
          });
        };

        const registerPush = async (silent = false) => {
          if (!pushEnabled || !pushToggleButton || !subscribeUrl || !serviceWorkerUrl) return;

          try {
            setPushButtonBusy(true, silent ? 'Memeriksa Notif...' : 'Memproses...');

            if (!silent) {
              setPushStatus('Menyiapkan push browser...', 'muted');
            }

            if (!window.isSecureContext) {
              throw new Error('Notifikasi browser membutuhkan HTTPS, localhost, atau 127.0.0.1.');
            }

            if (!('serviceWorker' in navigator)) {
              throw new Error('Browser tidak mendukung service worker.');
            }

            if (!('Notification' in window)) {
              throw new Error('Browser tidak mendukung notifikasi.');
            }

            const permission = await Notification.requestPermission();

            if (permission !== 'granted') {
              if (!silent) {
                setPushStatus('Izin notifikasi ditolak di browser ini.', 'error');
              }

              setPushButtonBusy(false, 'Aktifkan Notif Browser');
              return;
            }

            await unregisterLegacyRootFirebaseWorker();

            const registration = await navigator.serviceWorker.register(serviceWorkerUrl, { scope: '/admin/' });
            await navigator.serviceWorker.ready;

            const [{ initializeApp }, { getMessaging, getToken, onMessage, isSupported }] = await Promise.all([
              import('https://www.gstatic.com/firebasejs/10.13.2/firebase-app.js'),
              import('https://www.gstatic.com/firebasejs/10.13.2/firebase-messaging.js'),
            ]);

            const supported = await isSupported();
            if (!supported) {
              throw new Error('Firebase Messaging tidak didukung di browser ini.');
            }

            const app = initializeApp(firebaseConfig);
            const messaging = getMessaging(app);
            bindForegroundMessaging(messaging, onMessage);

            const token = await getToken(messaging, {
              vapidKey: firebaseVapidKey,
              serviceWorkerRegistration: registration,
            });

            if (!token) {
              throw new Error('Token notifikasi tidak berhasil dibuat.');
            }

            currentFcmToken = token;
            storageSet(adminPushTokenStorageKey, token);

            const response = await fetch(subscribeUrl, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
              },
              credentials: 'same-origin',
              body: JSON.stringify({ fcm_token: token }),
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok || !data.ok) {
              throw new Error(data.message || 'Gagal menyimpan token notifikasi.');
            }

            setPushStatus('Push browser aktif di perangkat ini.', 'success');
            setPushButtonBusy(false, 'Perbarui Notif Browser');
          } catch (error) {
            console.error('Gagal mengaktifkan push browser.', error);

            if (!silent) {
              setPushStatus(error.message || 'Gagal mengaktifkan push browser.', 'error');
            }

            setPushButtonBusy(false, currentFcmToken ? 'Perbarui Notif Browser' : 'Aktifkan Notif Browser');
          }
        };

        const unregisterPush = async () => {
          if (!unsubscribeUrl || !currentFcmToken) return;

          try {
            await fetch(unsubscribeUrl, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
              },
              credentials: 'same-origin',
              body: JSON.stringify({ fcm_token: currentFcmToken }),
            });
          } catch (error) {
            console.error('Gagal melepas token push browser.', error);
          }
        };

        pushToggleButton?.addEventListener('click', async () => {
          if (pushBusy) return;
          await registerPush(false);
        });

        if (pushEnabled && 'Notification' in window && Notification.permission === 'granted') {
          registerPush(true);
        }

        window.addEventListener('beforeunload', () => {
          // jangan auto unsubscribe supaya notifikasi tetap aktif
        });

        fetchFeed(false);
        setInterval(() => fetchFeed(true), 15000);
      })();
    </script>
  @endif
