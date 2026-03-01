import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["playerHead", "playerBody", "saveBtn", "hint"];
  static values = {
    saveUrl: String,
    initialHair: String,
    initialBody: String,
  };

  connect() {
    this.currentHair = this.normalize(this.initialHairValue) || "bald_head.webp";
    this.currentBody = this.normalize(this.initialBodyValue) || "normal_body.webp";

    this.selectedHair = this.currentHair;
    this.selectedBody = this.currentBody;

    this.applySelectionUI();
    this.setDirty(false);
  }

  normalize(v) {
    if (!v) return "";
    return String(v)
      .replace(/^https?:\/\/[^/]+\/+/i, "")
      .replace(/^\/+/, "")
      .replace(/^public\//, "")            
      .trim();
  }

  setDirty(isDirty) {
    if (this.hasSaveBtnTarget) {
      this.saveBtnTarget.disabled = !isDirty;
      this.saveBtnTarget.classList.toggle("opacity-50", !isDirty);
      this.saveBtnTarget.classList.toggle("cursor-not-allowed", !isDirty);
    }
    if (this.hasHintTarget) {
      this.hintTarget.textContent = isDirty
        ? "Modification en attente… Clique sur ENREGISTRER."
        : "Choisis un style puis enregistre.";
    }
  }

  setPlayerHead(file) {
    if (!this.hasPlayerHeadTarget) return;
    this.playerHeadTarget.src = `/${this.normalize(file)}`;
  }

  setPlayerBody(file) {
    if (!this.hasPlayerBodyTarget) return;
    this.playerBodyTarget.src = `/${this.normalize(file)}`;
  }

  applySelectionUI() {
    // preview instant
    this.setPlayerHead(this.selectedHair);
    this.setPlayerBody(this.selectedBody);

    // hair tiles
    const hairTiles = Array.from(this.element.querySelectorAll("[data-hair]"));
    hairTiles.forEach((tile) => {
      const hair = this.normalize(tile.dataset.hair);
      const badge = tile.querySelector(".skin-selected");
      const isSelected = hair === this.selectedHair;

      tile.classList.toggle("ring-2", isSelected);
      tile.classList.toggle("ring-white/40", isSelected);
      if (badge) badge.classList.toggle("hidden", !isSelected);
    });

    const bodyTiles = Array.from(this.element.querySelectorAll("[data-body]"));
    bodyTiles.forEach((tile) => {
      const body = this.normalize(tile.dataset.body);
      const badge = tile.querySelector(".skin-selected");
      const isSelected = body === this.selectedBody;

      tile.classList.toggle("ring-2", isSelected);
      tile.classList.toggle("ring-white/40", isSelected);
      if (badge) badge.classList.toggle("hidden", !isSelected);
    });

    const dirty =
      this.selectedHair !== this.currentHair ||
      this.selectedBody !== this.currentBody;

    this.setDirty(dirty);
  }

  selectHair(e) {
    const hair = this.normalize(e.currentTarget?.dataset?.hair);
    if (!hair) return;
    this.selectedHair = hair;
    this.applySelectionUI();
  }

  selectBody(e) {
    const body = this.normalize(e.currentTarget?.dataset?.body);
    if (!body) return;
    this.selectedBody = body;
    this.applySelectionUI();
  }

  async save() {
    const dirty =
      this.selectedHair !== this.currentHair ||
      this.selectedBody !== this.currentBody;

    if (!dirty) return;
    if (!this.saveUrlValue) return;

    this.setDirty(false);
    if (this.hasHintTarget) this.hintTarget.textContent = "Sauvegarde…";

    try {
      const res = await fetch(this.saveUrlValue, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify({
          hairSkin: this.selectedHair,
          bodySkin: this.selectedBody,
        }),
      });

      const ct = res.headers.get("content-type") || "";
      const raw = await res.text();
      const data = ct.includes("application/json") ? JSON.parse(raw) : { message: raw };

      if (!res.ok) {
        if (this.hasHintTarget) this.hintTarget.textContent = data.message || "Erreur sauvegarde.";
        this.setDirty(true);
        return;
      }

      this.currentHair = this.normalize(data.hairSkin || this.selectedHair);
      this.currentBody = this.normalize(data.bodySkin || this.selectedBody);

      this.selectedHair = this.currentHair;
      this.selectedBody = this.currentBody;

      this.applySelectionUI();

      if (this.hasHintTarget) this.hintTarget.textContent = "Sauvegardé.";
      window.setTimeout(() => {
        if (this.hasHintTarget) this.hintTarget.textContent = "Choisis un style puis enregistre.";
      }, 1200);
    } catch (err) {
      console.error(err);
      if (this.hasHintTarget) this.hintTarget.textContent = "Erreur réseau.";
      this.setDirty(true);
    }
  }
}