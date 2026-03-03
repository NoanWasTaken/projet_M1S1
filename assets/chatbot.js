import "./chatbot.css";

const STORAGE_KEY = "gearforge_chat_history";
const CONVERSATION_KEY = "gearforge_conversation_id";
const API_URL = "/api/chatbot";
const IS_AUTHENTICATED = window.GearForge?.isAuthenticated ?? false;

// ── State
let messages = loadMessages();
let conversationId = loadConversationId();
let isOpen = false;
let isLoading = false;

// ── Init
document.addEventListener("DOMContentLoaded", () => {
    createChatWidget();
    renderMessages();
});

// ── LocalStorage
function loadMessages() {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        return stored ? JSON.parse(stored) : [];
    } catch {
        return [];
    }
}

function saveMessages() {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(messages));
    } catch {
        // Storage full or unavailable
    }
}

function loadConversationId() {
    try {
        const stored = localStorage.getItem(CONVERSATION_KEY);
        return stored ? parseInt(stored, 10) : null;
    } catch {
        return null;
    }
}

function saveConversationId(id) {
    try {
        if (id) {
            localStorage.setItem(CONVERSATION_KEY, String(id));
        } else {
            localStorage.removeItem(CONVERSATION_KEY);
        }
    } catch (error) {}
}

function clearMessages() {
    messages = [];
    conversationId = null;
    saveMessages();
    saveConversationId(null);
    renderMessages();
}

// ── DOM
function createChatWidget() {
    // Bubble
    const bubble = document.createElement("button");
    bubble.id = "gf-chat-bubble";
    bubble.innerHTML = `
        <span class="gf-chat-bubble-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
        </span>
        <span class="gf-chat-bubble-close">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <path d="M18 6 6 18M6 6l12 12"/>
            </svg>
        </span>
    `;
    bubble.addEventListener("click", toggleChat);

    // Panel
    const panel = document.createElement("div");
    panel.id = "gf-chat-panel";
    panel.innerHTML = `
        <div class="gf-chat-header">
            <div class="gf-chat-header-info">
                <span class="gf-chat-header-dot"></span>
                <span class="gf-chat-header-title">GearForge Assistant</span>
            </div>
            <div class="gf-chat-header-actions">
                <button id="gf-chat-reset" title="Nouvelle conversation">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                    </svg>
                </button>
                <button id="gf-chat-close" title="Fermer">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <path d="M18 6 6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <div id="gf-chat-messages" class="gf-chat-messages">
            <div class="gf-chat-welcome">
                <div class="gf-chat-welcome-icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="7" width="20" height="13" rx="2"/>
                        <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                        <path d="M9 12h6M12 9v6"/>
                    </svg>
                </div>
                <p>Salut&nbsp;! Je suis l&rsquo;assistant GearForge.<br>Comment puis-je t&rsquo;aider&nbsp;?</p>
            </div>
        </div>
        <form id="gf-chat-form" class="gf-chat-form">
            <input
                type="text"
                id="gf-chat-input"
                placeholder="${IS_AUTHENTICATED ? "Écris ton message..." : "Connectez-vous pour utiliser le chatbot"}"
                autocomplete="off"
                ${IS_AUTHENTICATED ? "" : "disabled"}
            />
            <button type="submit" id="gf-chat-send" ${IS_AUTHENTICATED ? "" : "disabled"}>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 2L11 13"/>
                    <path d="M22 2L15 22L11 13L2 9L22 2Z"/>
                </svg>
            </button>
        </form>
    `;

    document.body.appendChild(panel);
    document.body.appendChild(bubble);

    // Event listeners
    document
        .getElementById("gf-chat-close")
        .addEventListener("click", toggleChat);
    document.getElementById("gf-chat-reset").addEventListener("click", () => {
        clearMessages();
    });
    document
        .getElementById("gf-chat-form")
        .addEventListener("submit", handleSubmit);
}

// ── Toggle
function toggleChat() {
    isOpen = !isOpen;
    const panel = document.getElementById("gf-chat-panel");
    const bubble = document.getElementById("gf-chat-bubble");

    if (isOpen) {
        panel.classList.add("gf-chat-open");
        bubble.classList.add("gf-chat-bubble-active");
        document.getElementById("gf-chat-input").focus();
        scrollToBottom();
    } else {
        panel.classList.remove("gf-chat-open");
        bubble.classList.remove("gf-chat-bubble-active");
    }
}

// ── Render
function renderMessages() {
    const container = document.getElementById("gf-chat-messages");
    if (!container) return;

    // Keep welcome message if no messages
    if (messages.length === 0) {
        container.innerHTML = `
            <div class="gf-chat-welcome">
                <div class="gf-chat-welcome-icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="7" width="20" height="13" rx="2"/>
                        <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                        <path d="M9 12h6M12 9v6"/>
                    </svg>
                </div>
                <p>Salut ! Je suis l’assistant GearForge.<br>Comment puis-je t’aider ?</p>
            </div>
        `;
        return;
    }

    container.innerHTML = "";
    messages.forEach((msg) => {
        const msgEl = document.createElement("div");
        msgEl.className = `gf-chat-msg gf-chat-msg-${msg.role}`;
        msgEl.innerHTML = `<div class="gf-chat-msg-content">${formatMessage(msg.content)}</div>`;
        container.appendChild(msgEl);
    });

    scrollToBottom();
}

function formatMessage(text) {
    return text
        .replace(/\*\*(.*?)\*\*/g, "<strong>$1</strong>")
        .replace(/\*(.*?)\*/g, "<em>$1</em>")
        .replace(/`(.*?)`/g, "<code>$1</code>")
        .replace(/\n/g, "<br>");
}

function appendMessage(role, content) {
    messages.push({ role, content });
    saveMessages();
    renderMessages();
}

function showTypingIndicator() {
    const container = document.getElementById("gf-chat-messages");
    const typing = document.createElement("div");
    typing.className = "gf-chat-msg gf-chat-msg-assistant gf-chat-typing";
    typing.id = "gf-chat-typing";
    typing.innerHTML = `
        <div class="gf-chat-msg-content">
            <div class="gf-typing-dots">
                <span></span><span></span><span></span>
            </div>
        </div>
    `;
    container.appendChild(typing);
    scrollToBottom();
}

function removeTypingIndicator() {
    const typing = document.getElementById("gf-chat-typing");
    if (typing) typing.remove();
}

function scrollToBottom() {
    const container = document.getElementById("gf-chat-messages");
    if (container) {
        requestAnimationFrame(() => {
            container.scrollTop = container.scrollHeight;
        });
    }
}

// ── Submit
async function handleSubmit(e) {
    e.preventDefault();

    if (!IS_AUTHENTICATED) return;

    const input = document.getElementById("gf-chat-input");
    const sendBtn = document.getElementById("gf-chat-send");
    const text = input.value.trim();

    if (!text || isLoading) return;

    appendMessage("user", text);
    input.value = "";
    isLoading = true;
    sendBtn.disabled = true;
    input.disabled = true;

    showTypingIndicator();

    try {
        const response = await fetch(API_URL, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ messages, conversationId }),
        });

        const data = await response.json();

        removeTypingIndicator();
        if (data.conversationId) {
            conversationId = data.conversationId;
            saveConversationId(conversationId);
        }

        if (data.error) {
            appendMessage("assistant", `⚠️ ${data.error}`);
        } else {
            appendMessage("assistant", data.content);
        }
    } catch (error) {
        removeTypingIndicator();
        appendMessage(
            "assistant",
            "⚠️ Impossible de contacter le serveur. Vérifie ta connexion.",
        );
    } finally {
        isLoading = false;
        sendBtn.disabled = false;
        input.disabled = false;
        input.focus();
    }
}
