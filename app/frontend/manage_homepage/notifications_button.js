document.getElementById('notificationButton').addEventListener('click', () => {
    let notificationList = document.getElementById('notificationList');
    if (notificationList.style.display === 'none') {
        notificationList.style.display = 'block';
    } else {
        notificationList.style.display = 'none';
    }
});