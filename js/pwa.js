// This will handle the PWA installation prompt
let deferredPrompt;
const pwaBanner = document.getElementById('pwaInstallBanner');
const installBtnFloat = document.getElementById('installPwa');
const installBtn = document.getElementById('installPwaStatic');
const closeBtn = document.getElementById('closePwaBanner');

// Only show the install banner if the PWA is not already installed
if (pwaBanner) {
    // Hide the banner by default
    pwaBanner.style.display = 'none';
    // Show the banner when the PWA can be installed
    window.addEventListener('beforeinstallprompt', (e) => {
        // console.clear();
        // console.log('%c🎉 ThisWeekPool PWA Install Banner', 'background: #222; color: #bada55');

        // Prevent Chrome 67 and earlier from automatically showing the prompt
        e.preventDefault();
        // Stash the event so it can be triggered later
        deferredPrompt = e;

        // check the time set in localStorage to show the banner again as elapsed before showing the banner again
        const showPwaBannerAfter = localStorage.getItem('showPwaBannerAfter');
        if (showPwaBannerAfter && Date.now() < showPwaBannerAfter) {
            console.log('PWA banner already shown');
            // return;
        } else {
            // Show the banner
            pwaBanner.style.display = 'block';
            // Trigger the slide-up animation after a short delay to allow for rendering
            setTimeout(() => pwaBanner.classList.add('show'), 100);
        }


        // Handle install button click
        installBtn.addEventListener('click', () => {
            // Show the install prompt
            deferredPrompt.prompt();
            // Wait for the user to respond to the prompt
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('User accepted the install prompt');
                    // askForNotificationPermission();
                } else {
                    console.log('User dismissed the install prompt');
                }
                // Hide the banner regardless of user's choice
                hideBanner();
                // Clear the saved prompt since it can't be used again
                deferredPrompt = null;
            });
        });

        // Handle install button click
        installBtnFloat.addEventListener('click', () => {
            // Show the install prompt
            deferredPrompt.prompt();
            // Wait for the user to respond to the prompt
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('User accepted the install prompt');
                    // askForNotificationPermission();
                } else {
                    console.log('User dismissed the install prompt');
                }
                // Hide the banner regardless of user's choice
                hideBanner();
                // Clear the saved prompt since it can't be used again
                deferredPrompt = null;
            });
        });
    });

    // Close button handler
    if (closeBtn) {
        closeBtn.addEventListener('click', hideBanner);
    }

    // Hide the banner when the app is installed
    window.addEventListener('appinstalled', (evt) => {
        console.log('PWA was installed');
        hideBanner();
    });

    // Function to hide the banner
    function hideBanner() {
        if (pwaBanner) {
            pwaBanner.classList.remove('show');
            // Wait for the animation to complete before hiding completely
            setTimeout(() => {
                pwaBanner.style.display = 'none';
            }, 300);

            // if the close button is clicked, don't show the banner again for the next 24 hours. Set variable in localStorage
            if (closeBtn) {
                localStorage.setItem('showPwaBannerAfter', Date.now() + 12 * 60 * 60 * 1000); // 12 hours in milliseconds', );
            }

        }
    }
}


const askForNotificationPermission = () => {
    Notification.requestPermission().then((result) => {
        if (result === 'granted') {
            console.log('Notification permission granted');

            // Check if service workers are supported
            if (!('serviceWorker' in navigator)) {
                console.error('Service workers are not supported in this browser');
                return;
            }

            // Check if push manager is available
            if (!('PushManager' in window)) {
                console.error('Push notifications are not supported in this browser');
                return;
            }

            navigator.serviceWorker.ready.then((sw) => {

                // Try to get existing subscription first
                sw.pushManager.getSubscription().then((subscription) => {
                    if (subscription) {
                        console.log('User is already subscribed:', subscription);
                        return subscription;
                    }

                    // If no existing subscription, create a new one
                    return sw.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array('BA24dnziNq9xwd7ag0KgpdIrX9XgSFP2EUGwyxKORKGyTAei8hkXHP-fqiwOK2o5uIBhVW94EO7pZsjKNlUINwY')
                    });
                })
                    .then((subscription) => {
                        // Here you would typically send the subscription to your server
                        return fetch('/api/push-subscribe', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(subscription)
                        });
                    })
                    .catch((error) => {
                        console.error('Error during push subscription:', error);
                        if (Notification.permission === 'denied') {
                            console.warn('Notifications were denied');
                        }
                    });
            }).catch(error => {
                console.error('Service Worker registration error:', error);
            });
        } else {
            console.log('Notification permission denied');
        }
    });
};

// Helper function to convert VAPID key
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}
