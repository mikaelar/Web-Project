document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.card');
    const lists = document.querySelectorAll('.cards');
document.getElementById('notificationButton').onclick = function() {
    var notificationList = document.getElementById('notificationList');
    if (notificationList.style.display === 'none') {
        notificationList.style.display = 'block';
    } else {
        notificationList.style.display = 'none';
    }
};

document.querySelectorAll('.card').forEach(card => {
    card.addEventListener('click', () => {
        window.location.href = `../project_details.php?id=${card.dataset.id}`;
    });
});

        document.getElementById('notificationButton').onclick = function() {
            var notificationList = document.getElementById('notificationList');
            if (notificationList.style.display === 'none') {
                notificationList.style.display = 'block';
            } else {
                notificationList.style.display = 'none';
            }
        };
        
// Затваряне на dropdown ако се кликне извън него
window.onclick = function(event) {
    if (!event.target.matches('.dropbtn')) {
        var dropdowns = document.getElementsByClassName("dropdown-content");
        for (var i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.style.display === 'block') {
                openDropdown.style.display = 'none';
            }
        }
    }
};


    cards.forEach(card => {
        card.addEventListener('dragstart', dragStart);
    });

    lists.forEach(list => {
        list.addEventListener('dragover', dragOver);
        list.addEventListener('drop', dropCard);
    });

    function dragStart(event) {
        event.dataTransfer.setData('text/plain', event.target.dataset.id);
        setTimeout(() => {
            event.target.style.display = 'none';
        }, 0);
    }

    function dragOver(event) {
        event.preventDefault();
    }

    function dropCard(event) {
        event.preventDefault();
        const id = event.dataTransfer.getData('text/plain');
        const card = document.querySelector(`[data-id='${id}']`);
        card.style.display = 'block';
        if (event.target.classList.contains('cards')) {
            event.target.appendChild(card);
        }
    }
});
