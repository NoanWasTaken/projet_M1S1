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
        promoCode: { type: String, default: "PROMO-100CLICKS" },
        isAuth: { type: Boolean, default: false }
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

    async finish() {
        this._stopTimer();
        this._running = false;
        this.clickZoneTarget.classList.add("pointer-events-none");
        if (this._clicks >= this.requiredClicksValue) {
            // Vérifier l'authentification
            if (!this.isAuthValue) {
                this.promoTarget.textContent = "Vous devez être connecté pour débloquer le code promo.";
                this.promoTarget.classList.remove("text-green-600");
                this.promoTarget.classList.add("text-yellow-500");
                return;
            }
            // Appel API pour débloquer le code promo côté serveur
            try {
                const response = await fetch("/api/cps-test/claim", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    body: JSON.stringify({ clicks: this._clicks })
                });
                const data = await response.json();
                if (data.ok) {
                    this.promoTarget.textContent = `Bravo ! Code promo : ${data.promoCode}`;
                    this.promoTarget.classList.remove("text-yellow-500");
                    this.promoTarget.classList.add("text-green-600");
                } else {
                    this.promoTarget.textContent = data.message || "Erreur lors du déblocage du code promo.";
                    this.promoTarget.classList.remove("text-green-600");
                    this.promoTarget.classList.add("text-yellow-500");
                }
            } catch (e) {
                this.promoTarget.textContent = "Erreur serveur.";
                this.promoTarget.classList.remove("text-green-600");
                this.promoTarget.classList.add("text-red-600");
            }
        } else {
            this.promoTarget.textContent = `Raté ! (${this._clicks} clics)`;
            this.promoTarget.classList.remove("text-green-600");
            this.promoTarget.classList.add("text-red-600");
        }
    }

    _stopTimer() {
        if (this._interval) {
            clearInterval(this._interval);
            this._interval = null;
        }
    }
}
