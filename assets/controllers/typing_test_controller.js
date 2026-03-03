import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = [
    "openBtn",
    "modal",
    "backdrop",
    "closeBtn",
    "prompt",
    "input",
    "timer",
    "startBtn",
    "resetBtn",
    "score",
    "accuracy",
    "recommendation",
  ];

  static values = {
    duration: { type: Number, default: 60 },
    endpoint: { type: String, default: "/api/typing-test/recommendation" },
    isAuth: { type: Boolean, default: false },
    claimUrl: { type: String, default: "" },
  };


  connect() {
    console.log("[typing-test] connected", this.element);
    this._interval = null;
    this._running = false;
    this._timeLeft = this.durationValue;

    this._prompts = [
      "Dans l’arène du gaming, la précision naît du calme : chaque frappe compte, chaque décision forge ton style. Un bon clavier ne fait pas tout, mais une main sûre transforme la vitesse en contrôle et la technique en victoire. La régularité, c’est l’élégance du geste : un rythme propre, une frappe nette, et l’esprit reste lucide sous pression.",
      "Dans le silence de la nuit, le clavier devient métronome. Chaque touche est une décision, chaque frappe une vision qu’on façonne. Le monde défile trop vite, mais moi je garde le contrôle, Je transforme le chaos en lignes droites, je canalise le rôle. On me parle de vitesse, mais la maîtrise vaut plus que l’urgence, Car un esprit lucide fait trembler même les plus grandes puissances. Je n’écris pas pour impressionner, j’écris pour laisser une trace, Même dans l’ombre je construis, chaque mot trouve sa place. Le talent c’est un départ, la discipline c’est l’arme secrète, Ceux qui bossent en silence finissent toujours par faire la fête.",
      "Demain, dès l’aube, à l’heure où blanchit la campagne, Je partirai. Vois-tu, je sais que tu m’attends. J’irai par la forêt, j’irai par la montagne. Je ne puis demeurer loin de toi plus longtemps. Je marcherai les yeux fixés sur mes pensées, Sans rien voir au dehors, sans entendre aucun bruit, Seul, inconnu, le dos courbé, les mains croisées, Triste, et le jour pour moi sera comme la nuit. Je ne regarderai ni l’or du soir qui tombe, Ni les voiles au loin descendant vers Harfleur, Et quand j’arriverai, je mettrai sur ta tombe Un bouquet de houx vert et de bruyère en fleur",
    ];

    this.reset();
    this._bindAntiCheat();
  }

  open() {
    console.log("[typing-test] open()");
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

    this.inputTarget.disabled = false;
    this.inputTarget.focus();

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

    this.timerTarget.textContent = String(this._timeLeft);
    this.inputTarget.value = "";
    this.inputTarget.disabled = true;

    this.scoreTarget.textContent = "—";
    this.accuracyTarget.textContent = "—";
    this.recommendationTarget.textContent = "";

    this._pickPrompt();
  }

  async finish() {
    this._stopTimer();
    this._running = false;
    this.inputTarget.disabled = true;

    const { wpm, wps, accuracy } = this._computeScore();

    this.scoreTarget.textContent = `${wpm} WPM (${wps.toFixed(2)} WPS)`;
    this.accuracyTarget.textContent = `${Math.round(accuracy * 100)}%`;

    await this._fetchRecommendation({ wpm, accuracy });

    if (wpm >= 35) {
      if (!this.isAuthValue) {
        this._showRetroPopup({
          title: "Victoire !",
          lines: [
            "Tu as dépassé 45 WPM.",
            "Récompense : +650 XP",
            "Bien joué ! tu écris plus vite que ma mère ne lit ses SMS.",
          ],
          kind: "success",
        });
        return;
      }
      await this._claimReward({ wpm, accuracy });
    }
  }

  _stopTimer() {
    if (this._interval) window.clearInterval(this._interval);
    this._interval = null;
  }

  _pickPrompt() {
    this._referenceText =
      this._prompts[Math.floor(Math.random() * this._prompts.length)];
    this.promptTarget.textContent = this._referenceText;
  }

  _normalize(s) {
    return (s || "").replace(/\s+/g, " ").trim();
  }

  _computeScore() {
    const refWords = this._normalize(this._referenceText).split(" ");
    const typedWords = this._normalize(this.inputTarget.value)
      .split(" ")
      .filter(Boolean);

    const len = Math.min(refWords.length, typedWords.length);
    let correct = 0;

    for (let i = 0; i < len; i++) {
      if (typedWords[i] === refWords[i]) correct++;
    }

    const accuracy = typedWords.length === 0 ? 0 : correct / typedWords.length;
    const wpm = correct; 
    const wps = wpm / 60;

    return { wpm, wps, accuracy };
  }

  async _fetchRecommendation({ wpm, accuracy }) {
    this.recommendationTarget.innerHTML =
      "<span class='text-gray-400'>Analyse du score…</span>";

    try {
      const res = await fetch(this.endpointValue, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ wpm, accuracy }),
      });

      const data = await res.json();
      if (!res.ok) throw new Error(data?.error || "Erreur API");

      this.recommendationTarget.textContent =
        data?.content || "Pas de recommandation.";
    } catch (e) {
      console.error(e);
      this.recommendationTarget.innerHTML =
        "<span class='text-red-400'>Impossible d’obtenir une recommandation.</span>" +
        `<div class="text-xs text-gray-400 mt-2">Endpoint: ${this.endpointValue}</div>`;
    }
  }

  _bindAntiCheat() {
    const el = this.inputTarget;

    el.addEventListener("paste", (e) => e.preventDefault());
    el.addEventListener("copy", (e) => e.preventDefault());
    el.addEventListener("cut", (e) => e.preventDefault());

    el.addEventListener("keydown", (e) => {
      const key = (e.key || "").toLowerCase();
      const isMod = e.ctrlKey || e.metaKey;

      if (isMod && ["c", "v", "x", "a"].includes(key)) {
        e.preventDefault();
      }
    });
  }

  _getPopupHost() {
  return document.getElementById("global-popup-host");
}

_showRetroPopup({ title, lines = [], sub = "", kind = "success" }) {
  const host = this._getPopupHost();
  if (!host) return;

  host.innerHTML = "";

  const wrap = document.createElement("div");
  wrap.className = "pointer-events-auto w-full max-w-md";

  const frame = document.createElement("div");
  frame.className =
    "rounded-xl border border-white/20 bg-black/80 backdrop-blur-sm shadow-2xl px-5 py-4 font-mono text-white";

  const badge = document.createElement("div");
  badge.className =
    "inline-flex items-center gap-2 rounded-md border border-white/15 bg-white/5 px-3 py-1 text-[11px] tracking-[0.25em] text-white/80";
  badge.textContent = kind === "success" ? "QUEST CLEAR" : "INFO";

  const h = document.createElement("div");
  h.className = "mt-3 text-lg tracking-wide";
  h.textContent = title;

  const ul = document.createElement("div");
  ul.className = "mt-3 space-y-2 text-sm text-white/85";

  lines.forEach((t) => {
    const row = document.createElement("div");
    row.className = "flex items-start gap-2";
    row.innerHTML = `<span class="mt-[2px] text-white/60">▶</span><span>${t}</span>`;
    ul.appendChild(row);
  });

  const subEl = document.createElement("div");
  subEl.className = "mt-4 text-xs tracking-widest text-white/60";
  subEl.textContent = sub;

  const actions = document.createElement("div");
  actions.className = "mt-4 flex items-center justify-end gap-2";

  const btn = document.createElement("button");
  btn.type = "button";
  btn.className =
    "rounded-md border border-white/20 bg-white/10 px-3 py-2 text-xs tracking-widest text-white hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/20";
  btn.textContent = "OK";
  btn.addEventListener("click", () => (host.innerHTML = ""));

  actions.appendChild(btn);

  frame.appendChild(badge);
  frame.appendChild(h);
  frame.appendChild(ul);
  if (sub) frame.appendChild(subEl);
  frame.appendChild(actions);

  wrap.appendChild(frame);
  host.appendChild(wrap);
}

async _claimReward({ wpm, accuracy }) {
  if (!this.claimUrlValue) return;

  try {
    const res = await fetch(this.claimUrlValue, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: JSON.stringify({ wpm, accuracy }),
    });

    const ct = res.headers.get("content-type") || "";
    const raw = await res.text();
    const data = ct.includes("application/json") ? JSON.parse(raw) : { message: raw };

    if (!res.ok) {
      this._showRetroPopup({
        title: "Récompense",
        lines: [data.message || "Impossible de réclamer la récompense."],
        sub: `HTTP ${res.status}`,
        kind: "info",
      });
      return;
    }

    if (data.ok && data.awarded) {
      this._showRetroPopup({
        title: "Quest Clear !",
        lines: [
          "+650 XP ajoutés",
          `Niveau : ${data.level} | XP : ${data.xpTotal}`,
        ],
        sub: "GG.",
        kind: "success",
      });
    } else {
      this._showRetroPopup({
        title: "Récompense",
        lines: [data.message || "Déjà réclamée."],
        kind: "info",
      });
    }
  } catch (e) {
    console.error(e);
    this._showRetroPopup({
      title: "Erreur",
      lines: ["Problème réseau.", "Réessaie."],
      kind: "info",
    });
  }
}
}