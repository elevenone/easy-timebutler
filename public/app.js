const elMain = document.querySelector('main');

window.addEventListener('load', e => {
    showTemplate('#tpl-loading');
    if (isLoggedIn()) {
        showDashboard();
        return;
    }
    showLoginForm();
});

function isLoggedIn() {
    const token = localStorage.getItem('et_token');
    return token !== null;
}

async function showLoginForm() {
    showTemplate('#tpl-login-form');
    elMain.querySelector('#login-submit-btn').addEventListener('click', evt => {
        evt.preventDefault();
        handleLoginFormSubmit();
    });
}

async function showDashboard() {
    showTemplate('#tpl-dashboard');
    for (const elBtn of elMain.querySelectorAll('.js-stopclock-action')) {
        elBtn.addEventListener('click', evt => {
            evt.preventDefault();
            handleStopclockBtnClick(evt);
        });
    }
}

async function handleLoginFormSubmit() {
    const credentials = new URLSearchParams({
        email: document.querySelector('input[name="email"]').value,
        password: document.querySelector('input[name="password"]').value,
    });

    await fetch('/login?' + credentials.toString(), { method: 'POST', })
        .then((response) => {
            return response.json();
        })
        .then((responseData) => {
            if (responseData.errors) {
                document.querySelector('#error-message').innerText = responseData.errors.shift();
                return;
            }
            localStorage.setItem('et_token', responseData.data.token);
            showDashboard();
        })
        .catch((error) => {
            console.error('Error:', error);
        });
}

async function handleStopclockBtnClick(evt) {
    const elEvtSource = evt.target;
    const params = new URLSearchParams({
        action: elEvtSource.getAttribute('data-action'),
        token: localStorage.getItem('et_token'),
    });

    await fetch('/stopclock?' + params.toString(), { method: 'POST' })
        .then((response) => {
            return response.json();
        })
        .then((responseData) => {
            console.log(responseData);
        })
        .catch((error) => {
            console.error('Error:', error);
        });
}

function showTemplate(templateId) {
    const tplDashboard = document.querySelector(templateId);
    const tplDashboardClone = tplDashboard.content.cloneNode(true);
    elMain.innerHTML = '';
    elMain.appendChild(tplDashboardClone);
}
