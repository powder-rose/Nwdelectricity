import { useEffect, useState } from 'react';
import type { ArticleForm, ArticleRecord } from '@/features/articles/model';
import { fromMoscowInput, toMoscowInput, publicationDate, scheduleStates } from '@/features/articles/scheduling';
export default function PublicationControls({ form, record, disabled, onChange }: {
  form: ArticleForm; record?: ArticleRecord; disabled: boolean;
  onChange: <K extends keyof ArticleForm>(field: K, value: ArticleForm[K]) => void;
}) {
  const [value, setValue] = useState(() => toMoscowInput(form.scheduledAt));
  useEffect(() => { setValue(toMoscowInput(form.scheduledAt)); }, [record?.id, record?.revision]);
  return <section className="nwd-publication-controls" aria-labelledby="publication-title">
    <div className="nwd-publication-label"><h2 id="publication-title">Публикация</h2><span>МСК · UTC+3</span></div>
    <fieldset disabled={disabled}>
      <legend className="nwd-visually-hidden">Способ публикации</legend>
      {([
        ['draft', 'Черновик', 'Сохранить для дальнейшей работы'],
        ['scheduled', 'По расписанию', 'Выпустить в выбранное время'],
        ['published', record?.status === 'published' ? 'Опубликовано' : 'Опубликовать сейчас', 'Обновить публичную версию'],
      ] as const).map(([status, title, hint]) => <label className="nwd-publication-option" key={status}>
        <input type="radio" name="publication-mode" checked={form.status === status} onChange={() => onChange('status', status)} />
        <span><strong>{title}</strong><small>{hint}</small></span>
      </label>)}
    </fieldset>
    {form.status === 'scheduled' && <div className="nwd-schedule-input">
      <label htmlFor="article-schedule-at">Дата и время, Москва</label>
      <input id="article-schedule-at" type="datetime-local" value={value} disabled={disabled} step={60}
        onChange={event => { setValue(event.target.value); onChange('scheduledAt', fromMoscowInput(event.target.value)); }} />
      <small>Статья выйдет автоматически. Админку можно закрыть.</small>
    </div>}
    {record?.publicationSchedule && <p className={'nwd-schedule-note nwd-schedule-note--' + record.publicationSchedule.state} role="status">
      <strong>{scheduleStates[record.publicationSchedule.state]}</strong>
      <span>{publicationDate(record.publicationSchedule.at)} МСК</span>
      {record.publicationSchedule.error && <span>{record.publicationSchedule.error}</span>}
    </p>}
    <a className="nwd-plan-link" href="/redaktsiya/index.php?tab=plan">Открыть план публикаций ↗</a>
  </section>;
}
