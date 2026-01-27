document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.admin-request__tab');
    const bodies = {
        'waiting-approval': document.getElementById('admin-waiting-approval'),
        'approved': document.getElementById('admin-approved'),
    };

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            //active切り替え
            tabs.forEach(t => t.classList.remove('admin-request__tab--active'));
            tab.classList.add('admin-request__tab--active');

            //tbody切り替え
            Object.values(bodies).forEach(body => {
                body.style.display = 'none';
            });

            const target = tab.dataset.target;
            bodies[target].style.display = '';
        });
    });
});