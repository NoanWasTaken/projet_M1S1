import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["item"];
  static values = { claimUrl: String, isAuth: Boolean };

  connect() {
    this.isDragging = false;
    this.longPressTimer = null;
    this.dragEl = null;
    this.placeholder = null;
    this.justDragged = false;
    this.pointerId = null;

    this.dragStartTop = 0;
    this.dragStartLeft = 0;
    this.dragWidth = 0;
    this.dragOffsetY = 0;

    this.moveAttempts = 0;        
    this.hintRevealed = false;
    this.updateStatusText();

    this.onPointerMove = this.onPointerMove.bind(this);
    this.onPointerUp = this.onPointerUp.bind(this);

    this.itemTargets.forEach((el) => {
      el.style.touchAction = "none";
      el.setAttribute("draggable", "false");
      el.addEventListener("pointerdown", (e) => this.onPointerDown(e, el));
      el.addEventListener("dragstart", (e) => e.preventDefault());
    });
  }

  disconnect() {
    this.cleanupDrag();
    this.itemTargets.forEach((el) => {
      el.replaceWith(el.cloneNode(true));
    });
  }

  preventClick(e) {
    if (this.isDragging || this.justDragged) {
      e.preventDefault();
      e.stopPropagation();
    }
  }

  getPopupHost() {
    return document.getElementById("global-popup-host");
    }

  onPointerDown(e, el) {
    if (e.button !== undefined && e.button !== 0) return;

    this.pointerId = e.pointerId;
    el.setPointerCapture?.(this.pointerId);

    this.longPressTimer = window.setTimeout(() => {
      this.startDrag(e, el);
    }, 1000);

    const startX = e.clientX;
    const startY = e.clientY;

    const cancelIfMoved = (moveEvent) => {
      const dx = Math.abs(moveEvent.clientX - startX);
      const dy = Math.abs(moveEvent.clientY - startY);
      if (dx + dy > 10) {
        this.clearLongPress();
        window.removeEventListener("pointermove", cancelIfMoved);
      }
    };

    window.addEventListener("pointermove", cancelIfMoved, { passive: true });

    const cancelOnUp = () => {
      this.clearLongPress();
      window.removeEventListener("pointerup", cancelOnUp);
      window.removeEventListener("pointermove", cancelIfMoved);
    };
    window.addEventListener("pointerup", cancelOnUp, { once: true });
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
    this.dragStartTop = r.top;
    this.dragStartLeft = r.left;
    this.dragWidth = r.width;

    this.placeholder = document.createElement("div");
    this.placeholder.className =
      "block w-full rounded-md border border-dashed border-white/20 bg-white/5";
    this.placeholder.style.height = `${r.height}px`;

    el.parentNode.insertBefore(this.placeholder, el.nextSibling);

    this.dragOffsetY = e.clientY - r.top;

    el.classList.add("ring-2", "ring-white/30");
    el.style.position = "fixed";
    el.style.top = `${this.dragStartTop}px`;
    el.style.left = `${this.dragStartLeft}px`;
    el.style.width = `${this.dragWidth}px`;
    el.style.zIndex = "9999";
    el.style.cursor = "grabbing";

    this.onPointerMove(e);

    window.addEventListener("pointermove", this.onPointerMove, { passive: false });
    window.addEventListener("pointerup", this.onPointerUp, { passive: false, once: true });
  }

  onPointerMove(e) {
    if (!this.isDragging || !this.dragEl) return;
    e.preventDefault();

    const list = this.element;
    const rect = list.getBoundingClientRect();

    const clampedY = Math.min(Math.max(e.clientY, rect.top), rect.bottom);
    const desiredTop = clampedY - this.dragOffsetY;

    const translateY = desiredTop - this.dragStartTop;
    this.dragEl.style.transform = `translateY(${translateY}px)`;

    const elUnder = document.elementFromPoint(e.clientX, e.clientY);
    const item = elUnder?.closest?.("[data-nav-game-target='item']");
    if (!item || item === this.dragEl) return;

    const itemRect = item.getBoundingClientRect();
    const before = e.clientY < itemRect.top + itemRect.height / 2;

    if (before) list.insertBefore(this.placeholder, item);
    else list.insertBefore(this.placeholder, item.nextSibling);
  }

  async onPointerUp(e) {
    if (!this.isDragging || !this.dragEl) return;
    e.preventDefault();

    this.justDragged = true;
    window.setTimeout(() => (this.justDragged = false), 300);

    this.dragEl.style.transform = "";
    this.dragEl.style.position = "";
    this.dragEl.style.top = "";
    this.dragEl.style.left = "";
    this.dragEl.style.width = "";
    this.dragEl.style.zIndex = "";
    this.dragEl.style.cursor = "";
    this.dragEl.classList.remove("ring-2", "ring-white/30");

    this.element.insertBefore(this.dragEl, this.placeholder);
    this.placeholder.remove();

    this.cleanupDragListeners();

    const order = Array.from(
      this.element.querySelectorAll("[data-nav-game-target='item']")
    ).map((el) => el.dataset.key);

    this.moveAttempts += 1;
    if (this.isAuthValue && !this.hintRevealed && this.moveAttempts >= 20) {
      this.hintRevealed = true;
      this.updateStatusText();
    }

    const target = ["clavier", "souris", "casque", "chaise"];
    const isWin = order.length === target.length && order.every((v, i) => v === target[i]);
    if (isWin) {
      if (!this.isAuthValue || !this.claimUrlValue) {
        this.showRetroPopup({
          title: "Accès verrouillé",
          lines: ["Connecte-toi pour valider le combo et gagner l'XP."],
          sub: "",
          kind: "info",
        });
        return;
      }

      await this.claimReward(order);
    }
  }

  cleanupDragListeners() {
    window.removeEventListener("pointermove", this.onPointerMove);
    this.isDragging = false;
    this.dragEl = null;
    this.placeholder = null;
    this.pointerId = null;
  }

  cleanupDrag() {
    this.clearLongPress();
    this.cleanupDragListeners();
  }

  updateStatusText() {
    if (!this.hasStatusTarget) return;

    if (!this.isAuthValue) {
      this.statusTarget.textContent = "Connectez - vous pour accéder à nos différents";
      return;
    }

    if (this.hintRevealed) {
      this.statusTarget.textContent = "indice : emblème d'un pays";
    } else {
      this.statusTarget.textContent = "trouvez la bonne combinaison";
    }
  }

  async claimReward(order) {
    if (!this.claimUrlValue) return;

    try {
      const res = await fetch(this.claimUrlValue, {
        method: "POST",
        headers: {"Content-Type": "application/json","X-Requested-With": "XMLHttpRequest",},
        body: JSON.stringify({ order }),
      });

      const text = await res.text();

      let data = {};
      try { data = JSON.parse(text); } catch { data = { message: "Non JSON", raw: text }; }
      

      if (!res.ok) {
        this.showRetroPopup({
          title: "Erreur",
          lines: ["Impossible de valider le combo.", "Réessaie dans quelques secondes."],
          sub: "",
          kind: "info",
        });
        return;
      }

      if (data.awarded) {
        this.showRetroPopup({
          title: "Bonne config !",
          lines: [
            "Ordre secret validé : Panier → Catalogue → Support → Config IA",
            "+800 XP ajoutés à ton profil",
            `Niveau : ${data.level}  |  XP total : ${data.xpTotal}`,
          ],
          sub: "Les couleurs forment le drapeau de la république de Maurice. C'est un archipel de plusieurs îles à l'est de Madagascar, réputé pour ses plages paradisiaques et sa biodiversité marine exceptionnelle. Une personne avec qui je fais du volley est originaire de ce pays.",
          kind: "success",
        });
      } else {
        this.showRetroPopup({
          title: "Déjà validé",
          lines: [
            "Ce combo a déjà été récompensé.",
            `Niveau : ${data.level}  |  XP : ${data.xpTotal}`,
          ],
          sub: "Cherche un autre jeu (désolé tu peux pas spam ce jeu pour les XP)",
          kind: "info",
        });
      }

      this.element.dispatchEvent(
        new CustomEvent("game:xp-awarded", { bubbles: true, detail: data })
      );
    } catch (err) {
      console.error(err);
    }
  }

  showRetroPopup({ title, lines = [], sub = "", kind = "success" }) {
  const host = this.getPopupHost();
  if (!host) return;

  host.innerHTML = "";

  const wrap = document.createElement("div");
  wrap.className = "pointer-events-auto w-full max-w-md";

  const frame = document.createElement("div");
  frame.className =
    "rounded-xl border border-white/20 bg-black/80 backdrop-blur-sm shadow-2xl " +
    "px-5 py-4 font-mono text-white";

  const badge = document.createElement("div");
  badge.className ="inline-flex items-center gap-2 rounded-md border border-white/15 bg-white/5 px-3 py-1 text-[11px] tracking-[0.25em] text-white/80";
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
    "rounded-md border border-white/20 bg-white/10 px-3 py-2 text-xs tracking-widest text-white " +
    "hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/20";
  btn.textContent = "OK";

  btn.addEventListener("click", () => {
    host.innerHTML = "";
  });


  window.setTimeout(() => {
    if (host.contains(wrap)) host.innerHTML = "";
  }, 35000);

  actions.appendChild(btn);

  const scan = document.createElement("div");
  scan.className =
    "pointer-events-none absolute inset-0 rounded-xl opacity-20 " +
    "bg-[linear-gradient(to_bottom,rgba(255,255,255,0.12)_1px,transparent_1px)] bg-[length:100%_6px]";
  scan.style.mixBlendMode = "overlay";

  frame.style.position = "relative";
  frame.appendChild(scan);

  frame.appendChild(badge);
  frame.appendChild(h);
  frame.appendChild(ul);
  if (sub) frame.appendChild(subEl);
  frame.appendChild(actions);

  frame.animate(
    [{ transform: "scale(0.92)", opacity: 0 }, { transform: "scale(1.0)", opacity: 1 }],
    { duration: 180, easing: "ease-out" }
  );

  wrap.appendChild(frame);
  host.appendChild(wrap);
}
}