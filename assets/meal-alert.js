(() => {
    const schedule = [
        { start: '08:00', end: '09:00', name: 'Breakfast' },
        { start: '10:30', end: '11:00', name: 'Morning Snack' },
        { start: '12:00', end: '13:00', name: 'Lunch' },
        { start: '16:30', end: '17:00', name: 'Evening Snack' },
        { start: '20:00', end: '21:00', name: 'During Duty' },
        { start: '22:15', end: '22:45', name: 'Dinner' }
    ];

    const pad = n => String(n).padStart(2, '0');
    const todayKey = () => {
        const now = new Date();
        return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
    };

    const toMinutes = value => {
        const [h, m] = value.split(':').map(Number);
        return h * 60 + m;
    };

    function currentMeal() {
        const now = new Date();
        const current = now.getHours() * 60 + now.getMinutes();
        return schedule.find(meal => current >= toMinutes(meal.start) && current <= toMinutes(meal.end));
    }

    function requestPermission() {
        if (!('Notification' in window)) return Promise.resolve('unsupported');
        if (Notification.permission === 'default') {
            return Notification.requestPermission().catch(() => 'denied');
        }
        return Promise.resolve(Notification.permission);
    }

    let audioContext = null;

    function enableAlarm() {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return false;
            audioContext = audioContext || new AudioCtx();
            if (audioContext.state === 'suspended') audioContext.resume();
            localStorage.setItem('meal-alarm-enabled', '1');
            return true;
        } catch (_) {
            return false;
        }
    }

    function playAlarm() {
        if (localStorage.getItem('meal-alarm-enabled') !== '1') return;
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            audioContext = audioContext || new AudioCtx();
            if (audioContext.state === 'suspended') audioContext.resume();

            const start = audioContext.currentTime;
            for (let i = 0; i < 4; i++) {
                const oscillator = audioContext.createOscillator();
                const gain = audioContext.createGain();
                oscillator.type = 'sine';
                oscillator.frequency.value = i % 2 ? 880 : 660;
                gain.gain.setValueAtTime(0.0001, start + i * 0.35);
                gain.gain.exponentialRampToValueAtTime(0.22, start + i * 0.35 + 0.03);
                gain.gain.exponentialRampToValueAtTime(0.0001, start + i * 0.35 + 0.28);
                oscillator.connect(gain).connect(audioContext.destination);
                oscillator.start(start + i * 0.35);
                oscillator.stop(start + i * 0.35 + 0.3);
            }
        } catch (_) {}
    }

    function showReminder(meal) {
        const key = `meal-reminder-${todayKey()}-${meal.name}`;
        if (localStorage.getItem(key)) return;

        const message = `It's time for ${meal.name}. Don't forget to record your food.`;
        const banner = document.getElementById('mealAlertBanner');
        if (banner) {
            banner.textContent = `🔔 ${meal.name}: ${message}`;
            banner.classList.add('show');
            window.setTimeout(() => banner.classList.remove('show'), 12000);
        }

        playAlarm();

        if ('Notification' in window && Notification.permission === 'granted') {
            try {
                new Notification(`🍽️ ${meal.name} Reminder`, {
                    body: message,
                    tag: `meal-${meal.name}`,
                    renotify: true
                });
            } catch (_) {}
        }

        localStorage.setItem(key, '1');
    }

    function checkSchedule() {
        const meal = currentMeal();
        if (meal) showReminder(meal);
    }

    async function enableMealReminders() {
        enableAlarm();
        await requestPermission();
        localStorage.setItem('meal-reminders-enabled', '1');
        checkSchedule();
        const button = document.getElementById('mealNotifyButton');
        if (button) button.textContent = '🔔 Meal Notifications + Alarm Enabled';
    }

    window.mealSchedule = schedule;
    window.requestMealNotifications = requestPermission;
    window.enableMealReminders = enableMealReminders;
    window.enableMealAlarm = enableAlarm;
    window.checkMealSchedule = checkSchedule;

    checkSchedule();
    window.setInterval(checkSchedule, 15000);
})();
