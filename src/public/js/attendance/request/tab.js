document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.request__tab');
    const bodies = {
        'waiting-approval': document.getElementById('waiting-approval'),
        'approved': document.getElementById('approved'),
    };

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // activeクラス切り替え
            tabs.forEach(t => t.classList.remove('request__tab--active'));
            tab.classList.add('request__tab--active');

            // tbody切り替え
            Object.values(bodies).forEach(body => {
                body.style.display = 'none';
            });

            const target = tab.dataset.target;
            bodies[target].style.display = '';
        });
    });
});
