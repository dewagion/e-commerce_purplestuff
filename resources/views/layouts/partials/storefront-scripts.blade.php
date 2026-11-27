  <script>
    //`/admin/`.
    (() => {
      if (!('serviceWorker' in navigator)) return;

      window.addEventListener('load', () => {
        navigator.serviceWorker.getRegistration('/').then((registration) => {
          const activeScript = registration?.active?.scriptURL || registration?.waiting?.scriptURL || registration?.installing?.scriptURL || '';
          const legacyScript = `${window.location.origin}/firebase-messaging-sw.js`;

          if (registration && activeScript === legacyScript) {
            return registration.unregister();
          }
        }).catch(() => {
          
        });
      });
    })();
  </script>
