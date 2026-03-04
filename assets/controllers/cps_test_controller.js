import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = [
        "openBtn",
        "modal",
        "backdrop",
        "closeBtn",
        "timer",
        "clickZone",
        "score",
        "promo"
    ];

    static values = {
        duration: { type: Number, default: 10 },
        requiredClicks: { type: Number, default: 100 },
        promoCode: { type: String, default: "PROMO-100CLICKS" }
    };

    connect() {
        this._interval = null;
        this._running = false;
        this._timeLeft = this.durationValue;
        this._clicks = 0;
        this.reset();
    }

    open() {
        this.modalTarget.classList.remove("hidden");
        this.reset();
    }

    close() {
        this.modalTarget.classList.add("hidden");
        this._stopTimer();
    }

    start() {
        if (this._running) return;
        this._running = true;
        this._clicks = 0;
        this.scoreTarget.textContent = "0";
        this.promoTarget.textContent = "";
        this.clickZoneTarget.classList.remove("pointer-events-none");
        this._interval = window.setInterval(() => {
            this._timeLeft -= 1;
            this.timerTarget.textContent = String(this._timeLeft);
            if (this._timeLeft <= 0) {
                this.finish();
            }
        }, 1000);
    }

    reset() {
        this._stopTimer();
        this._running = false;
        this._timeLeft = this.durationValue;
        this._clicks = 0;
        this.timerTarget.textContent = String(this._timeLeft);
        this.scoreTarget.textContent = "0";
        this.promoTarget.textContent = "";
        this.clickZoneTarget.classList.add("pointer-events-none");
    }

    click() {
        if (!this._running) return;
        this._clicks++;
        this.scoreTarget.textContent = String(this._clicks);
    }

    finish() {
        this._stopTimer();
        this._running = false;
        this.clickZoneTarget.classList.add("pointer-events-none");
        if (this._clicks >= this.requiredClicksValue) {
            this.promoTarget.textContent = `Bravo ! Code promo : ${this.promoCodeValue}`;
        } else {
            this.promoTarget.textContent = `Raté ! (${this._clicks} clics)`;
        }
    }

    _stopTimer() {
        if (this._interval) {
            clearInterval(this._interval);
            this._interval = null;
        }
    }
}
