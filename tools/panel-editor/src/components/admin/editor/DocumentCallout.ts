import { Node, type Editor } from '@tiptap/core';
import { NodeSelection } from '@tiptap/pm/state';

export const DocumentCallout = Node.create({
  name: 'documentCallout', group: 'block', content: 'block+', defining: true,
  addAttributes() {
    return { title: { default: 'Образец документа', parseHTML: element => element.getAttribute('data-title') || 'Образец документа', renderHTML: attrs => ({ 'data-title': attrs.title }) } };
  },
  parseHTML() {
    return [{ tag: 'section[data-nwd-document="1"]', contentElement: element => (element.querySelector(':scope > details > [data-nwd-document-body]') || element) as HTMLElement }];
  },
  renderHTML({ HTMLAttributes }) { return ['section', { ...HTMLAttributes, 'data-nwd-document': '1' }, 0]; },
});
export type DocumentTarget = { from: number; to: number; kind: 'documentCallout' | 'blockquote'; title: string };
export function documentTarget(editor: Editor): DocumentTarget | undefined {
  const { $from } = editor.state.selection;
  if (editor.state.selection instanceof NodeSelection && editor.state.selection.node.type.name === 'documentCallout') {
    return { from: editor.state.selection.from, to: editor.state.selection.to, kind: 'documentCallout', title: editor.state.selection.node.attrs.title };
  }
  for (const kind of ['documentCallout', 'blockquote'] as const) {
    for (let depth = $from.depth; depth > 0; depth--) {
      const node = $from.node(depth);
      if (node.type.name === kind) return { from: $from.before(depth), to: $from.after(depth), kind, title: kind === 'documentCallout' ? node.attrs.title : 'Образец документа' };
    }
  }
}
export function applyDocument(editor: Editor, title: string, selection: { from: number; to: number }, target?: DocumentTarget) {
  const attrs = { title: title.trim().slice(0, 160) || 'Образец документа' };
  if (target) {
    const node = editor.state.doc.nodeAt(target.from);
    if (!node || node.type.name !== target.kind) return false;
    return editor.chain().focus().insertContentAt({ from: target.from, to: target.to }, { type: 'documentCallout', attrs, content: node.toJSON().content }).run();
  }
  if (selection.from !== selection.to) return editor.chain().focus().setTextSelection(selection).wrapIn('documentCallout', attrs).run();
  return editor.chain().focus().setTextSelection(selection.from).insertContent({ type: 'documentCallout', attrs, content: [{ type: 'paragraph' }] }).run();
}
export function unwrapDocument(editor: Editor, target: DocumentTarget) {
  const node = editor.state.doc.nodeAt(target.from);
  if (!node || node.type.name !== 'documentCallout') return false;
  return editor.chain().focus().insertContentAt({ from: target.from, to: target.to }, node.toJSON().content || [{ type: 'paragraph' }]).run();
}
