export default class Stopclock {
    constructor(el, token) {
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
        this.syncClock();
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
        switch (action) {
            case "start":
                return this.startClock();
            case "pause":
                return this.pauseClock();
            case "resume":
                return this.resumeClock();
            case "stop":
                return this.stopClock();
            default:
                console.error('Invalid stopclock action requested.');
        }
    }

    syncClock() {
        const params = new URLSearchParams({
            action: 'state',
            token: this.token,
        });

        this.elStateIndicatior.innerText = 'Syncing...';
        fetch('/stopclock?' + params.toString(), { method: 'POST' })
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

    startClock() {
        const params = new URLSearchParams({
            action: 'start',
            token: this.token,
        });

        fetch('/stopclock?' + params.toString(), { method: 'POST' })
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

    pauseClock() {
        const params = new URLSearchParams({
            action: 'pause',
            token: this.token,
        });

        fetch('/stopclock?' + params.toString(), { method: 'POST' })
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

    resumeClock() {
        const params = new URLSearchParams({
            action: 'resume',
            token: this.token,
        });

        fetch('/stopclock?' + params.toString(), { method: 'POST' })
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

    stopClock() {

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
        } else if (this.running === 1 && this.paused === 0) {
            this.elStateIndicatior.innerText = 'Running';
            if (!this.mainClockTimer) {
                this.startMainTimer();
            }
        } else if (this.running === 0 && this.paused === 1) {
            this.elStateIndicatior.innerText = 'Paused';
            if (!this.pauseClockTimer) {
                this.startPauseTimer();
            }
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
        this.mainTimer();
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
        this.pauseTimer();
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
