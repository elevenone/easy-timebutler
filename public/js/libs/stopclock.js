export default class Stopclock {
    constructor(el, token, initialState) {
        this.elStopclock = el;
        this.elStateIndicatior = el.querySelector('#clock-state');
        this.elMainClock = el.querySelector('#clock-main');
        this.elPauseClock = el.querySelector('#clock-pause');

        this.mainClockTimer = null;
        this.pauseClockTimer = null;

        this.token = token;
        this.state = 0;
        this.running = 0;
        this.paused = 0;
        this.pausesec = 0;
        this.dauersec = 0;

        this.registerListeners();
        this.updateState(initialState);
    }

    registerListeners() {
        for (const elBtn of this.elStopclock.querySelectorAll('.js-stopclock-action')) {
            elBtn.addEventListener('click', evt => {
                evt.preventDefault();
                this.handleButtonClick(evt);
            });
        }
    }

    handleButtonClick(evt) {
        const elEvtSource = evt.target;
        const action = elEvtSource.getAttribute('data-action');
        elEvtSource.classList.add('loading', 'disabled');
        switch (action) {
            case "start":
                return this.startClock().then(() => {
                    elEvtSource.classList.remove('loading', 'disabled');
                });
            case "pause":
                return this.pauseClock().then(() => {
                    elEvtSource.classList.remove('loading', 'disabled');
                });
            case "resume":
                return this.resumeClock().then(() => {
                    elEvtSource.classList.remove('loading', 'disabled');
                });
            case "stop":
                return this.stopClock().then(() => {
                    elEvtSource.classList.remove('loading', 'disabled');
                });
            case "state":
                return this.syncClock().then(() => {
                    elEvtSource.classList.remove('loading', 'disabled');
                });
            default:
                console.error('Invalid stopclock action requested.');
        }
    }

    async syncClock() {
        const params = new URLSearchParams({
            action: 'state',
            token: this.token,
        });

        this.elStateIndicatior.innerText = 'Syncing...';
        await fetch('/stopclock?' + params.toString(), { method: 'POST' })
            .then((response) => {
                return response.json();
            })
            .then((responseData) => {
                if (!responseData.data) {
                    console.error('Unexpected response.');
                    return;
                }
                const clockState = responseData.data.state.clock_state;
                this.updateState(clockState);
            })
            .catch((error) => {
                console.error('Error:', error);
                // @todo show error message
            });
    }

    async startClock() {
        if (this.running === 1) {
            return;
        }

        const params = new URLSearchParams({
            action: 'start',
            token: this.token,
        });

        await fetch('/stopclock?' + params.toString(), { method: 'POST' })
            .then((response) => {
                return response.json();
            })
            .then((responseData) => {
                this.updateState(responseData.data.clock_state);
                this.startMainTimer();
            })
            .catch((error) => {
                console.error('Error:', error);
            });
    }

    async pauseClock() {
        if (this.paused === 1) {
            return;
        }

        const params = new URLSearchParams({
            action: 'pause',
            token: this.token,
        });

        await fetch('/stopclock?' + params.toString(), { method: 'POST' })
            .then((response) => {
                return response.json();
            })
            .then((responseData) => {
                this.updateState(responseData.data.clock_state);
                this.stopMainTimer();
                this.startPauseTimer();
            })
            .catch((error) => {
                console.error('Error:', error);
            });
    }

    async resumeClock() {
        if (this.running === 1 || this.paused !== 1) {
            return;
        }

        const params = new URLSearchParams({
            action: 'resume',
            token: this.token,
        });

        await fetch('/stopclock?' + params.toString(), { method: 'POST' })
            .then((response) => {
                return response.json();
            })
            .then((responseData) => {
                this.updateState(responseData.data.clock_state);
                this.stopPauseTimer();
                this.startMainTimer();
            })
            .catch((error) => {
                console.error('Error:', error);
            });
    }

    async stopClock() {
        if (this.running === 0 && this.paused === 0) {
            return;
        }

        const params = new URLSearchParams({
            action: 'stop',
            token: this.token,
        });

        await fetch('/stopclock?' + params.toString(), { method: 'POST' })
            .then((response) => {
                return response.json();
            })
            .then((responseData) => {
                this.updateState(responseData.data.clock_state);
                this.stopMainTimer();
                this.stopPauseTimer();
            })
            .catch((error) => {
                console.error('Error:', error);
            });
    }

    updateState(clockState) {
        this.state = clockState.state;
        this.running = clockState.running;
        this.paused = clockState.paused;
        this.pausesec = clockState.pausesec;
        this.dauersec = clockState.dauersec;

        // @todo hide/show elements

        this.updateMainClock();
        this.updatePauseClock();

        if (this.running === 0 && this.paused === 0) {
            this.elStateIndicatior.innerText = 'Stopped';
            this.stopMainTimer();
            this.stopPauseTimer();
        } else if (this.running === 1 && this.paused === 0) {
            this.elStateIndicatior.innerText = 'Running';
            this.startMainTimer();
        } else if (this.running === 0 && this.paused === 1) {
            this.elStateIndicatior.innerText = 'Paused';
            this.startPauseTimer();
        } else {
            this.elStateIndicatior.innerText = 'Unknown';
        }
    }

    updateMainClock() {
        this.elMainClock.innerText = this._secToTime(this.dauersec);
    }

    updatePauseClock() {
        this.elPauseClock.innerText = this._secToTime(this.pausesec);
    }

    mainTimer() {
        this.dauersec++;
        this.updateMainClock();
        this.mainClockTimer = setTimeout(this.mainTimer.bind(this), 1000);
    }

    startMainTimer() {
        if (!this.mainClockTimer) {
            this.mainTimer();
        }
    }

    stopMainTimer() {
        clearTimeout(this.mainClockTimer);
    }

    pauseTimer() {
        this.pausesec++;
        this.updatePauseClock();
        this.pauseClockTimer = setTimeout(this.pauseTimer.bind(this), 1000);
    }

    startPauseTimer() {
        if (!this.pauseClockTimer) {
            this.pauseTimer();
        }
    }

    stopPauseTimer() {
        clearTimeout(this.pauseClockTimer);
    }

    _secToTime(seconds) {
        const date = new Date(1970,0,1);
        date.setSeconds(seconds);
        return date.toTimeString().replace(/.*(\d{2}:\d{2}:\d{2}).*/, "$1");
    }
}
