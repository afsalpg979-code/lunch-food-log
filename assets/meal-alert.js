(() => {
    const schedule = [
        { start: '08:00', end: '09:00', name: 'Breakfast' },
        { start: '10:30', end: '11:00', name: 'Morning Snack' },
        { start: '12:00', end: '13:00', name: 'Lunch' },
        { start: '16:30', end: '17:00', name: 'Evening Snack' },
        { start: '20:00', end: '21:00', name: 'During Duty' },
        { start: '22:15', end: '22:45', name: 'Dinner' }
    ];

    const todayKey = () => {
        const now = new Date();
        return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
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
        if ('Notification' in window && Notification.permission === 'default') {
            return Notification.requestPermission().catch(() => 'denied');
        }
        return Promise.resolve(Notification?.permission || 'denied');
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

        if ('Notification' in window && Notification.permission === 'granted') {
            try {
                new Notification(`🍽️ ${meal.name} Reminder`, { body: message });
            } catch (_) {}
        }

        localStorage.setItem(key, '1');
    }

    function checkSchedule() {
        const meal = currentMeal();
        if (meal) showReminder(meal);
    }

    window.mealSchedule = schedule;
    window.requestMealNotifications = requestPermission;
    window.checkMealSchedule = checkSchedule;

    // Notifications are browser-based and work while the dashboard is active.
    requestPermission();
    checkSchedule();
    window.setInterval(checkSchedule, 60000);
})();
