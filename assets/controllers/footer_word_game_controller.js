import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["tile", "slots", "status", "tries", "submit"];
  static values = { claimUrl: String, isAuth: Boolean };

  connect() {
    this.isDragging = false;
    this.dragEl = null;
    this.placeholder = null;
    this.longPressTimer = null;

    this.hasClaimed = false;
    this.isSubmitting = false;

    this.onPointerMove = this.onPointerMove.bind(this);
    this.onPointerUp = this.onPointerUp.bind(this);

    // ✅ snapshot exact pour reset parfait
    this.originMap = new Map();
    this.tileTargets.forEach((el) => {
      const parent = el.parentElement;
      const index = Array.from(parent.children).indexOf(el);
      this.originMap.set(el, { parent, index });

      el.style.touchAction = "none";
      el.setAttribute("draggable", "false");
      el.addEventListener("pointerdown", (e) => this.onPointerDown(e, el));
      el.addEventListener("dragstart", (e) => e.preventDefault());
    });

    this.updateStatus();
    this.updateTriesUI(null);
  }

  disconnect() {
    this.cleanupDrag();
  }

  // ---------- UI helpers ----------
  updateStatus(extra = "") {
    if (!this.hasStatusTarget) return;

    if (!this.isAuthValue) {
      this.statusTarget.textContent = "Connectez-vous pour tester le jeu !";
      return;
    }
    if (this.isAuthValue && !this.hasClaimed) {
      this.statusTarget.textContent = "Déposez des lettres ici pour écrire le mot clé.";
      return;
    }
    if (this.hasClaimed) {
      this.statusTarget.textContent = "Récompense déjà obtenue";
      return;
    }
    this.statusTarget.textContent = extra || "";
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
    this.triesTarget.textContent = "essais restants : 5";
  }

  setSubmitDisabled(disabled) {
    if (!this.hasSubmitTarget) return;
    this.submitTarget.disabled = disabled;
    this.submitTarget.classList.toggle("opacity-50", disabled);
    this.submitTarget.classList.toggle("cursor-not-allowed", disabled);
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

  flashSlots(kind) {
    // kind: "ok" | "ko"
    const tiles = Array.from(this.slotsTarget.querySelectorAll("[data-letter]"));
    if (!tiles.length) return;

    const add = kind === "ok"
      ? ["ring-2", "ring-emerald-400/70", "bg-emerald-400/15", "border-emerald-300/40"]
      : ["ring-2", "ring-rose-400/70", "bg-rose-400/15", "border-rose-300/40"];

    const removeOk = ["ring-2", "ring-emerald-400/70", "bg-emerald-400/15", "border-emerald-300/40"];
    const removeKo = ["ring-2", "ring-rose-400/70", "bg-rose-400/15", "border-rose-300/40"];

    tiles.forEach((el) => el.classList.add(...add));

    window.setTimeout(() => {
      tiles.forEach((el) => el.classList.remove(...removeOk, ...removeKo));
    }, 1000);
  }

  // ---------- drag logic ----------
  onPointerDown(e, el) {
    if (this.isSubmitting) return;
    if (e.button !== undefined && e.button !== 0) return;

    const startX = e.clientX;
    const startY = e.clientY;

    this.longPressTimer = window.setTimeout(() => {
      this.startDrag(e, el);
    }, 220);

    const cancelIfMoved = (moveEvent) => {
      const dx = Math.abs(moveEvent.clientX - startX);
      const dy = Math.abs(moveEvent.clientY - startY);
      if (dx + dy > 8) {
        this.clearLongPress();
        window.removeEventListener("pointermove", cancelIfMoved);
      }
    };

    window.addEventListener("pointermove", cancelIfMoved, { passive: true });
    window.addEventListener(
      "pointerup",
      () => {
        this.clearLongPress();
        window.removeEventListener("pointermove", cancelIfMoved);
      },
      { once: true }
    );
  }

  clearLongPress() {
    if (this.longPressTimer) {
      window.clearTimeout(this.longPressTimer);
      this.longPressTimer = null;
    }
  }

  startDrag(e, el) {
    this.isDragging = true;
    this.dragEl = el;

    const r = el.getBoundingClientRect();

    this.placeholder = document.createElement("span");
    this.placeholder.className = "inline-block";
    this.placeholder.style.width = `${r.width}px`;
    this.placeholder.style.height = `${r.height}px`;
    el.parentNode.insertBefore(this.placeholder, el.nextSibling);

    this.dragStartLeft = r.left;
    this.dragStartTop = r.top;
    this.dragOffsetX = e.clientX - r.left;
    this.dragOffsetY = e.clientY - r.top;

    el.classList.add("ring-2", "ring-white/30");
    el.style.position = "fixed";
    el.style.left = `${this.dragStartLeft}px`;
    el.style.top = `${this.dragStartTop}px`;
    el.style.zIndex = "9999";

    this.onPointerMove(e);

    window.addEventListener("pointermove", this.onPointerMove, { passive: false });
    window.addEventListener("pointerup", this.onPointerUp, { passive: false, once: true });
  }

  onPointerMove(e) {
    if (!this.isDragging || !this.dragEl) return;
    e.preventDefault();

    const desiredLeft = e.clientX - this.dragOffsetX;
    const desiredTop = e.clientY - this.dragOffsetY;

    const tx = desiredLeft - this.dragStartLeft;
    const ty = desiredTop - this.dragStartTop;

    this.dragEl.style.transform = `translate(${tx}px, ${ty}px)`;
  }

  onPointerUp(e) {
    if (!this.isDragging || !this.dragEl) return;
    e.preventDefault();

    const elUnder = document.elementFromPoint(e.clientX, e.clientY);
    const dropInSlots = elUnder?.closest?.("[data-footer-word-game-target='slots']");

    this.dragEl.style.position = "";
    this.dragEl.style.left = "";
    this.dragEl.style.top = "";
    this.dragEl.style.zIndex = "";
    this.dragEl.style.transform = "";
    this.dragEl.classList.remove("ring-2", "ring-white/30");

    if (dropInSlots) {
      this.slotsTarget.appendChild(this.dragEl);
    } else {
      this.placeholder?.parentNode?.insertBefore(this.dragEl, this.placeholder);
    }

    this.placeholder?.remove();
    this.cleanupDragListeners();
  }

  cleanupDragListeners() {
    window.removeEventListener("pointermove", this.onPointerMove);
    this.isDragging = false;
    this.dragEl = null;
    this.placeholder = null;
  }

  cleanupDrag() {
    this.clearLongPress();
    this.cleanupDragListeners();
  }

  // ---------- word / reset ----------
  getSlotsWord() {
    return Array.from(this.slotsTarget.querySelectorAll("[data-letter]"))
      .map((el) => el.dataset.letter)
      .join("");
  }

  reset() {
    // clean drag
    this.cleanupDrag();
    if (this.placeholder) {
      this.placeholder.remove();
      this.placeholder = null;
    }

    // restore exact positions
    const entries = Array.from(this.originMap.entries());
    entries.sort((a, b) => {
      const oa = a[1], ob = b[1];
      if (oa.parent === ob.parent) return oa.index - ob.index;
      return 0;
    });

    for (const [el, { parent, index }] of entries) {
      const children = Array.from(parent.children);
      if (index >= children.length) parent.appendChild(el);
      else parent.insertBefore(el, children[index]);
    }

    this.updateStatus();
  }

  // ---------- submit / attempts ----------
  async submit() {
    if (this.isSubmitting) return;

    const word = this.getSlotsWord();
    if (!word || word.length < 2) {
      this.showRetroPopup({
        title: "Info",
        lines: ["Dépose des lettres dans SLOTS avant de valider."],
        kind: "info",
      });
      return;
    }

    // Pas connecté → on peut jouer, mais pas valider
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
      // endpoint unique : il gère essais/jour + win/lose + reward
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
      const data = ct.includes("application/json") ? JSON.parse(raw) : { message: "Non JSON", raw };

      // serveur renvoie triesRemaining dans tous les cas
      if (typeof data.triesRemaining === "number") {
        this.updateTriesUI(data.triesRemaining);
      }

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

      // attendu: { ok: bool, awarded?: bool, ... }
      if (data.ok) {
        this.flashSlots("ok");

        // si reward
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
            sub: "GG. Depuis l'intérim je suis plus sûr d'avoir une âme d'enfant. /n Profite avant le 35h.",
            kind: "success",
          });
        } else {
          this.showRetroPopup({
            title: "Validé",
            lines: ["Bien joué, c'est effectivement le même mot. Mais il n'y a pas d'autre bon de réduction. La vie est dure pour tout le monde"],
            kind: "success",
          });
        }
      } else {
        // faux
        this.flashSlots("ko");
        this.showRetroPopup({
          title: "Faux",
          lines: ["Ce n'est pas le bon mot."],
          sub: typeof data.triesRemaining === "number"
            ? `Essais restants : ${data.triesRemaining}`
            : "",
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