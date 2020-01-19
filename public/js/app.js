import Stopclock from './libs/stopclock.js';

const elMain = document.querySelector('main');
let installPrompt;

window.addEventListener('load', e => {
    showTemplate('#tpl-loading');

    if ('serviceWorker' in navigator) {
        try {
            navigator.serviceWorker.register('/sw.js');
            console.log('ServiceWorker registered.');
        } catch (e) {
            console.error('Could not register ServiceWorker.');
        }
    }

    window.addEventListener('beforeinstallprompt', event => {
        event.preventDefault();
        installPrompt = event;
    });

    if (hasToken()) {
        showDashboard();
        return;
    }
    showLoginForm();
});

async function addToHome() {
    installPrompt.prompt();
    const installed = await installPrompt.userChoice;
    installPrompt = null;
}

function hasToken() {
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
    const token = localStorage.getItem('et_token');
    const stopclock = new Stopclock(elMain.querySelector('#stopclock'), token);
    document.querySelector('#ath-trigger').addEventListener('click', evt => {
       addToHome();
    });
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

function showTemplate(templateId) {
    const tplDashboard = document.querySelector(templateId);
    const tplDashboardClone = tplDashboard.content.cloneNode(true);
    elMain.innerHTML = '';
    elMain.appendChild(tplDashboardClone);
}
