import '../styles/article-documents.css';

const bound = new WeakSet<Document>();
const accessChecks = new WeakMap<Document, Promise<boolean>>();
const protectedCard = '.nwd-document[data-document-access="visitor"]';

type SiteJQuery = { fancybox?: { open: (options: { src: string; type: string; opts?: Record<string, unknown> }) => void } };

export function documentPlainText(node: Node): string {
  if (node.nodeType === 3) return node.nodeValue || '';
  if (!(node instanceof Element)) return Array.from(node.childNodes).map(documentPlainText).join('');
  const tag = node.tagName.toLowerCase();
  if (tag === 'br') return '\n';
  const text = Array.from(node.childNodes).map(documentPlainText).join('');
  if (tag === 'li') {
    const parent = node.parentElement;
    const index = parent ? Array.from(parent.children).indexOf(node) : 0;
    const start = Number(parent?.getAttribute('start') || 1);
    return `${parent?.tagName === 'OL' ? `${start + index}.` : '•'} ${text.trim()}\n`;
  }
  if (tag === 'td' || tag === 'th') return text.trim() + '\t';
  if (tag === 'tr') return text.replace(/\t$/, '') + '\n';
  return text + (/^(p|h[1-6]|ul|ol|blockquote|table|pre)$/.test(tag) ? '\n\n' : '');
}

function selectionProtected(owner: Document, target: EventTarget | null): boolean {
  if (target instanceof Element) {
    if (target.closest(protectedCard)) return true;
    if (target.closest('input,textarea,[contenteditable="true"]')) return false;
  }
  const selection = owner.getSelection();
  if (!selection || selection.isCollapsed) return false;
  return Array.from(owner.querySelectorAll(protectedCard)).some(card => {
    for (let i = 0; i < selection.rangeCount; i++) {
      if (selection.getRangeAt(i).intersectsNode(card)) return true;
    }
    return false;
  });
}

function editorAccess(owner: Document): Promise<boolean> {
  let check = accessChecks.get(owner);
  if (!check) {
    const controller = new AbortController();
    const timer = window.setTimeout(() => controller.abort(), 5000);
    check = fetch('/redaktsiya/document-access.php', { credentials: 'same-origin', cache: 'no-store', signal: controller.signal })
      .then(response => response.ok ? response.json() : null)
      .then(data => data?.copyAllowed === true).catch(() => false)
      .finally(() => window.clearTimeout(timer));
    accessChecks.set(owner, check);
  }
  return check;
}

export function initDocumentActions(root: Document | HTMLElement = document) {
  const owner = root instanceof Document ? root : root.ownerDocument;
  const cards = root.querySelectorAll<HTMLElement>('.nwd-document');
  cards.forEach(card => {
    // Editor previews receive their permission only from the authenticated preview API.
    if (card.dataset.documentAccess === 'editor') return;
    void editorAccess(owner).then(allowed => {
      if (!allowed || !card.isConnected) return;
      card.dataset.documentAccess = 'editor';
      const copy = card.querySelector<HTMLButtonElement>('[data-document-copy]');
      const request = card.querySelector<HTMLElement>('[data-document-request]');
      if (copy) copy.hidden = false;
      if (request) request.hidden = true;
    });
  });
  if (bound.has(owner)) return;
  bound.add(owner);
  ['copy', 'cut', 'contextmenu', 'dragstart'].forEach(name => {
    owner.addEventListener(name, event => {
      if (!selectionProtected(owner, event.target)) return;
      event.preventDefault();
      if ('clipboardData' in event) (event as ClipboardEvent).clipboardData?.clearData();
    }, true);
  });
  owner.addEventListener('keydown', event => {
    if ((event.ctrlKey || event.metaKey) && /^[cx]$/i.test(event.key) && selectionProtected(owner, event.target)) event.preventDefault();
  }, true);
  owner.addEventListener('click', async event => {
    if (!(event.target instanceof Element)) return;
    const request = event.target.closest<HTMLAnchorElement>('[data-document-request]');
    if (request) {
      const modal = owner.getElementById('nwd-document-request');
      const form = modal?.querySelector<HTMLFormElement>('form');
      const card = request.closest<HTMLElement>('.nwd-document');
      const article = request.closest<HTMLElement>('[data-document-article]');
      const jq = (window as unknown as { jQuery?: SiteJQuery }).jQuery;
      if (!form || !card || !article || !jq?.fancybox) return; // Ordinary contacts link is the no-JS fallback.
      event.preventDefault();
      if (form.getAttribute('aria-busy') === 'true') return;
      const set = (name: string, value: string) => { const input = form.elements.namedItem(name); if (input instanceof HTMLInputElement) input.value = value; };
      set('nwd_document_article', article.dataset.documentArticle || '');
      set('nwd_document_index', card.dataset.documentIndex || '');
      const bytes = window.crypto.getRandomValues(new Uint8Array(16));
      set('nwd_request_id', Array.from(bytes, n => n.toString(16).padStart(2, '0')).join(''));
      const title = modal.querySelector<HTMLElement>('[data-document-request-title]');
      if (title) title.textContent = card.dataset.title || 'Образец документа';
      const consent = form.querySelector<HTMLInputElement>('[name="nwd_consent"]');
      if (consent) consent.checked = false;
      form.querySelectorAll<HTMLElement>('.errors,[data-lead-error]').forEach(box => { box.textContent = ''; if (box.classList.contains('errors')) box.style.display = 'none'; });
      form.querySelectorAll('.error_input').forEach(input => input.classList.remove('error_input'));
      form.querySelectorAll('.input--error').forEach(input => input.classList.remove('input--error'));
      jq.fancybox.open({ src: '#nwd-document-request', type: 'inline', opts: { toolbar: false, smallBtn: false, infobar: false, touch: false } });
      return;
    }
    const button = event.target.closest<HTMLButtonElement>('[data-document-copy]');
    if (!button || button.disabled) return;
    const card = button.closest<HTMLElement>('.nwd-document');
    if (card?.dataset.documentAccess !== 'editor') return;
    const body = card.querySelector<HTMLElement>('[data-nwd-document-body]');
    const status = card.querySelector<HTMLElement>('.nwd-document__status');
    if (!body || !status) return;
    event.preventDefault();
    const text = documentPlainText(body).replace(/[ \t]+\n/g, '\n').replace(/\n{3,}/g, '\n\n').trim();
    if (!text) { status.textContent = 'В этом образце пока нет текста для копирования.'; return; }
    const label = button.textContent || 'Скопировать образец';
    button.disabled = true;
    try {
      if (!navigator.clipboard?.writeText) throw new Error('Clipboard unavailable');
      await navigator.clipboard.writeText(text);
      button.textContent = 'Скопировано';
      status.textContent = 'Текст образца скопирован. Его можно вставить в редактор документов.';
      window.setTimeout(() => { if (button.isConnected) button.textContent = label; }, 1800);
    } catch {
      const details = card.querySelector('details');
      if (details) details.open = true;
      const range = owner.createRange(); range.selectNodeContents(body);
      const selection = owner.getSelection(); selection?.removeAllRanges(); selection?.addRange(range);
      status.textContent = 'Текст выделен. Нажмите Ctrl+C или выберите «Копировать».';
    } finally { button.disabled = false; }
  });
}
if (typeof document !== 'undefined') {
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => initDocumentActions(), { once: true });
  else initDocumentActions();
}
