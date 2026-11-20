import '../styles/article-documents.css';

const bound = new WeakSet<Document>();
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
export function initDocumentActions(root: Document | HTMLElement = document) {
  root.querySelectorAll<HTMLButtonElement>('[data-document-copy]').forEach(button => { button.hidden = false; });
  const owner = root instanceof Document ? root : root.ownerDocument;
  if (bound.has(owner)) return;
  bound.add(owner);
  owner.addEventListener('click', async event => {
    if (!(event.target instanceof Element)) return;
    const button = event.target.closest<HTMLButtonElement>('[data-document-copy]');
    if (!button || button.disabled) return;
    const card = button.closest<HTMLElement>('.nwd-document');
    const body = card?.querySelector<HTMLElement>('[data-nwd-document-body]');
    const status = card?.querySelector<HTMLElement>('.nwd-document__status');
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
      const details = card!.querySelector('details');
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
