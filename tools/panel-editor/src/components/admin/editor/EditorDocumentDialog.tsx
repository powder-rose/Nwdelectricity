import { useEffect, useRef, useState } from 'react';
export default function EditorDocumentDialog({ title, editing, onSave, onRemove, onClose }: { title: string; editing: boolean; onSave: (title: string) => boolean; onRemove?: () => void; onClose: () => void }) {
  const dialog = useRef<HTMLDialogElement>(null);
  const [value, setValue] = useState(title);
  const [error, setError] = useState('');
  useEffect(() => { dialog.current?.showModal(); }, []);
  return <dialog ref={dialog} className="editor-dialog" aria-labelledby="document-dialog-title" onCancel={event => { event.preventDefault(); onClose(); }}>
    <form onSubmit={event => { event.preventDefault(); if (!value.trim()) { setError('Укажите название документа.'); return; } if (!onSave(value)) setError('Выделите текст документа отдельными абзацами и повторите вставку.'); }}>
      <h2 id="document-dialog-title">{editing ? 'Настроить документ' : 'Врезка документа'}</h2>
      <p className="editor-document-hint">Образец будет оформлен как отдельный документ с раскрытием и копированием текста.</p>
      <label>Название документа<input autoFocus value={value} maxLength={160} required placeholder="Должностная инструкция электрика" onChange={event => setValue(event.target.value)} /></label>
      <p className="editor-document-hint">Текст можно редактировать внутри врезки. Выделенный фрагмент или текущая обычная врезка сохраняются целиком.</p>
      {error && <p className="form-error" role="alert">{error}</p>}
      <div className="editor-dialog__actions">{onRemove && <button type="button" onClick={onRemove}>Убрать оформление</button>}<button type="button" onClick={onClose}>Отмена</button><button type="submit">{editing ? 'Сохранить' : 'Вставить документ'}</button></div>
    </form>
  </dialog>;
}
