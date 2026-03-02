import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["tile", "slots", "status", "tries", "submit"];
  static values = { claimUrl: String, statusUrl: String, isAuth: Boolean };

  connect() {
    this.hasClaimed = false;
    this.isSubmitting = false;

    this.originMap = new Map();
    this.tileTargets.forEach((el) => {
      const parent = el.parentElement;
      const index = Array.from(parent.children).indexOf(el);
      this.originMap.set(el, { parent, index });

      el.setAttribute("draggable", "false");
      el.style.touchAction = "manipulation";
      el.addEventListener("click", (e) => this.onTileClick(e, el));
    });

    this.updateStatus();
    this.updateTriesUI(null);
    this.fetchStatus(); 
  }

  updateStatus(extra = "") {
    if (!this.hasStatusTarget) return;

    if (!this.isAuthValue) {
      this.statusTarget.textContent = "Connectez-vous pour tester le jeu !";
      return;
    }
    if (this.hasClaimed) {
      this.statusTarget.textContent = "Récompense déjà obtenue";
      return;
    }
    this.statusTarget.textContent = extra || "Cliquez sur des lettres pour composer un mot.";
  }

  updateTriesUI(remaining) {
    if (!this.hasTriesTarget) return;

    if (!this.isAuthValue) {
      this.triesTarget.textContent = "essais restants : —";
      return;
    }
    if (typeof remaining === "number") {
      this.triesTarget.textContent = `essais restants : ${remaining}`;
      return;
    }
    this.triesTarget.textContent = "essais restants : …";
  }

  setSubmitDisabled(disabled) {
    if (!this.hasSubmitTarget) return;
    this.submitTarget.disabled = disabled;
    this.submitTarget.classList.toggle("opacity-50", disabled);
    this.submitTarget.classList.toggle("cursor-not-allowed", disabled);
  }

  async fetchStatus() {
    if (!this.isAuthValue || !this.statusUrlValue) return;

    try {
      const res = await fetch(this.statusUrlValue, {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });
      if (!res.ok) return;

      const data = await res.json();

      if (typeof data.triesRemaining === "number") this.updateTriesUI(data.triesRemaining);
      if (data.hasClaimed) {
        this.hasClaimed = true;
        this.updateStatus();
      }
    } catch (e) {
      console.error("échec game status:", e);
    }
  }

  onTileClick(_e, el) {
    if (this.isSubmitting) return;

    const isInSlots = el.parentElement === this.slotsTarget;

    if (!isInSlots) {
      this.slotsTarget.appendChild(el);
      return;
    }

    const origin = this.originMap.get(el);
    if (!origin) return;

    const { parent, index } = origin;
    const children = Array.from(parent.children);
    if (index >= children.length) parent.appendChild(el);
    else parent.insertBefore(el, children[index]);
  }

  getSlotsWord() {
    return Array.from(this.slotsTarget.querySelectorAll("[data-letter]"))
      .map((el) => el.dataset.letter)
      .join("");
  }

  reset() {
    for (const [el, { parent, index }] of this.originMap.entries()) {
      const children = Array.from(parent.children);
      if (index >= children.length) parent.appendChild(el);
      else parent.insertBefore(el, children[index]);
    }
    this.updateStatus();
  }

  flashSlots(kind) {
    const tiles = Array.from(this.slotsTarget.querySelectorAll("[data-letter]"));
    if (!tiles.length) return;

    const add =
      kind === "ok"
        ? ["ring-2", "ring-emerald-400/70", "bg-emerald-400/15", "border-emerald-300/40"]
        : ["ring-2", "ring-rose-400/70", "bg-rose-400/15", "border-rose-300/40"];

    const remove = [
      "ring-2",
      "ring-emerald-400/70",
      "bg-emerald-400/15",
      "border-emerald-300/40",
      "ring-rose-400/70",
      "bg-rose-400/15",
      "border-rose-300/40",
    ];

    tiles.forEach((el) => el.classList.add(...add));
    window.setTimeout(() => tiles.forEach((el) => el.classList.remove(...remove)), 1000);
  }

  getPopupHost() {
    return document.getElementById("global-popup-host");
  }

  showRetroPopup({ title, lines = [], sub = "", kind = "success" }) {
    const host = this.getPopupHost();
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

  async submit() {
    if (this.isSubmitting) return;

    const word = this.getSlotsWord();
    if (!word || word.length < 1) {
      this.showRetroPopup({
        title: "Info",
        lines: ["Clique au moins une lettre avant de valider."],
        kind: "info",
      });
      return;
    }

    if (!this.isAuthValue || !this.claimUrlValue) {
      this.flashSlots("ko");
      this.showRetroPopup({
        title: "Verrouillé",
        lines: ["Connecte-toi pour valider et récupérer le loot."],
        kind: "info",
      });
      return;
    }

    this.isSubmitting = true;
    this.setSubmitDisabled(true);

    try {
      const res = await fetch(this.claimUrlValue, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify({ word }),
      });

      const ct = res.headers.get("content-type") || "";
      const raw = await res.text();
      const data = ct.includes("application/json") ? JSON.parse(raw) : { message: raw };

      if (typeof data.triesRemaining === "number") this.updateTriesUI(data.triesRemaining);

      if (!res.ok) {
        this.flashSlots("ko");
        this.showRetroPopup({
          title: "Erreur",
          lines: [data.message || "Validation impossible."],
          sub: `HTTP ${res.status}`,
          kind: "info",
        });
        return;
      }

      if (data.ok) {
        this.flashSlots("ok");

        if (data.awarded) {
          this.hasClaimed = true;
          this.updateStatus("Récompense obtenue");

          this.showRetroPopup({
            title: "Quest Clear !",
            lines: [
              "+1000 XP ajoutés",
              `Bon : ${data.couponCode} (${data.discountLabel})`,
              `Niveau : ${data.level} | XP : ${data.xpTotal}`,
            ],
            sub: "GG.",
            kind: "success",
          });
        } else {
          this.showRetroPopup({
            title: "Validé",
            lines: ["Bien joué."],
            kind: "success",
          });
        }
      } else {
        this.flashSlots("ko");
        this.showRetroPopup({
          title: "Faux",
          lines: ["Ce n'est pas le bon mot."],
          sub: typeof data.triesRemaining === "number" ? `Essais restants : ${data.triesRemaining}` : "",
          kind: "info",
        });
      }
    } catch (err) {
      console.error(err);
      this.flashSlots("ko");
      this.showRetroPopup({
        title: "Erreur",
        lines: ["Problème réseau.", "Réessaie."],
        kind: "info",
      });
    } finally {
      this.isSubmitting = false;
      this.setSubmitDisabled(false);
    }
  }
}