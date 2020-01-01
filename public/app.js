const elMain = document.querySelector('main');

window.addEventListener('load', e => {
    if (isLoggedIn()) {
        showMainScreen();
        return;
    }
    showLoginForm();
});

function isLoggedIn() {
    const token = localStorage.getItem('et_token');
    return token !== null;
}

async function showLoginForm() {
    await fetch('/login')
        .then((response) => {
            return response.json();
        })
        .then((responseData) => {
            elMain.innerHTML = responseData.data.content;
            elMain.querySelector('#login-submit-btn').addEventListener('click', evt => {
                evt.preventDefault();
                handleLoginFormSubmit();
            });
        })
        .catch((error) => {
            console.error('Error:', error);
        });
}

async function handleLoginFormSubmit() {
    const credentials = new URLSearchParams({
        email: document.querySelector('input[name="email"]').value,
        password: document.querySelector('input[name="password"]').value,
    });

    await fetch('/login?' + credentials.toString(), {
        method: 'POST',
    })
    .then((response) => {
        return response.json();
    })
    .then((responseData) => {
        if (responseData.errors) {
            document.querySelector('#error-message').innerText = responseData.errors.shift();
            return;
        }
        localStorage.setItem('et_token', responseData.data.token);
        showMainScreen();
    })
    .catch((error) => {
        console.error('Error:', error);
    });
}

async function showMainScreen() {
    await fetch('/dashboard')
        .then((response) => {
            return response.json();
        })
        .then((responseData) => {
            elMain.innerHTML = responseData.data.content;
            for (const elBtn of document.querySelectorAll('.js-stopclock-action')) {
                elBtn.addEventListener('click', evt => {
                    evt.preventDefault();
                    handleStopclockBtnClick(evt);
                });
            }
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

    await fetch('/stopclock?' + params.toString())
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
