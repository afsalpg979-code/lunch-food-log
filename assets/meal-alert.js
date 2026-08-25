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
    const todayKey = () => new Date().toISOString().slice(0, 10);

    function minutesNow() {
        const now = new Date();
        return now.getHours() * 60 + now.getMinutes();
    }

    function toMinutes(value) {
        const [h, m] = value.split(':').map(Number);
        return h * 60 + m;
    }

    function currentMeal() {
        const current = minutesNow();
        return schedule.find(meal => current >= toMinutes(meal.start) && current <= toMinutes(meal.end));
    }

    function requestPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission().catch(() => {});
        }
    }

    function showReminder(meal) {
        const key = `meal-reminder-${todayKey()}-${meal.name}`;
        if (localStorage.getItem(key)) return;

        const message = `It's time for ${meal.name}. Don't forget to record your food.`;
        const banner = document.getElementById('mealAlertBanner');
        if (banner) {
            banner.innerHTML = `<strong>🔔 ${meal.name}</strong><br>${message}`;
            banner.classList.add('show');
            setTimeout(() => banner.classList.remove('show'), 12000);
        } else {
            alert(`🔔 ${meal.name}\n\n${message}`);
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

    requestPermission();
    checkSchedule();
    setInterval(checkSchedule, 60000);
})();
