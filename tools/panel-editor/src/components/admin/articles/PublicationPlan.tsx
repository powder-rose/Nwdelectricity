import { useEffect, useRef, useState } from 'react';
import { articleJson, articleRequest } from './api';
import { fromMoscowInput, toMoscowInput, publicationDate, scheduleStates,
  type ScheduleAction, type ScheduleArticle, type ScheduleListing } from '@/features/articles/scheduling';

const tabs = [['upcoming','Предстоящие'],['history','История'],['error','Ошибки'],['all','Все']] as const;
const copy = {
  schedule: ['Перенести публикацию', 'Сохранить время', 'Выберите новую дату и время по Москве.'],
  cancel: ['Снять с расписания?', 'Вернуть в черновики', 'Статья сохранится в черновиках. Её можно будет запланировать снова.'],
  publish: ['Опубликовать сейчас?', 'Начать публикацию', 'Статья выйдет без ожидания запланированного времени.'],
  retry: ['Повторить публикацию?', 'Повторить публикацию', 'Сервер ещё раз попробует обновить публичную версию статьи.'],
} as const;
const messages = { schedule: 'Время публикации изменено.', cancel: 'Статья возвращена в черновики.', publish: 'Статья опубликована.', retry: 'Публикация выполнена повторно.' };
function PlanDialog({ record, action, busy, submit, close }: {
  record: ScheduleArticle; action: ScheduleAction; busy: boolean;
  submit: (record: ScheduleArticle, action: ScheduleAction, at: string | null) => Promise<void>; close: () => void;
}) {
  const ref = useRef<HTMLDialogElement>(null);
  const [value,setValue] = useState(() => toMoscowInput(record.schedule.at));
  const [error,setError] = useState('');
  const [title,button,description] = copy[action];
  useEffect(() => { const dialog=ref.current!; dialog.showModal(); return () => dialog.close(); }, []);
  return <dialog ref={ref} className="nwd-plan-dialog" aria-labelledby="plan-dialog-title" onCancel={event => { if (busy) event.preventDefault(); else close(); }}>
    <form onSubmit={async event => {
      event.preventDefault();
      const at=fromMoscowInput(value);
      if (action === 'schedule' && (!at || Date.parse(at) <= Date.now())) { setError('Выберите будущую дату и время.'); return; }
      setError('');
      try { await submit(record,action,at); } catch (failure) { setError(failure instanceof Error ? failure.message : 'Не удалось выполнить действие.'); }
    }}>
      <span className="nwd-plan-kicker">План публикаций · МСК</span>
      <h2 id="plan-dialog-title">{title}</h2><p className="nwd-plan-dialog-title">{record.title}</p><p>{description}</p>
      {action === 'schedule' ? <label>Дата и время, Москва<input type="datetime-local" value={value} min={toMoscowInput(Date.now())} disabled={busy} required autoFocus onChange={event => setValue(event.target.value)} /></label>
        : <p>По плану · {publicationDate(record.schedule.at)} МСК</p>}
      {error && <p className="form-error" role="alert">{error}</p>}
      <div className="nwd-plan-dialog-actions"><button type="button" disabled={busy} onClick={close}>Отмена</button><button className="lime-button" type="submit" disabled={busy}>{busy ? 'Сохранение…' : button}</button></div>
    </form>
  </dialog>;
}
export default function PublicationPlan() {
  const [data,setData]=useState<ScheduleListing>();
  const [error,setError]=useState('');
  const [notice,setNotice]=useState('');
  const [busy,setBusy]=useState(false);
  const busyRef=useRef(false);
  const [refresh,setRefresh]=useState(0);
  const [tab,setTab]=useState('upcoming');
  const [search,setSearch]=useState('');
  const [dialog,setDialog]=useState<{record:ScheduleArticle; action:ScheduleAction} | null>(null);
  const [clock,setClock]=useState(Date.now());
  const offset=useRef(0);
  useEffect(() => {
    let controller: AbortController | undefined;
    let active=true;
    async function load() {
      if (busyRef.current || document.hidden) return;
      controller?.abort(); const request=new AbortController(); controller=request;
      try {
        const result=await articleRequest<ScheduleListing>('/api/admin/article-schedules',{signal:request.signal});
        if (active && !request.signal.aborted) { offset.current=Date.parse(result.serverNow)-Date.now(); setData(result); setError(''); }
      } catch (failure) {
        if (active && !request.signal.aborted) setError(failure instanceof Error ? failure.message : 'Не удалось загрузить расписание.');
      }
    }
    void load();
    const timer=window.setInterval(() => { setClock(Date.now()); void load(); },15000);
    const visibility=() => { if (!document.hidden) void load(); };
    document.addEventListener('visibilitychange',visibility);
    return () => { active=false; controller?.abort(); clearInterval(timer); document.removeEventListener('visibilitychange',visibility); };
  },[refresh]);
  async function change(record:ScheduleArticle, action:ScheduleAction, at:string | null) {
    if (busyRef.current) return;
    busyRef.current=true;setBusy(true);setError('');setNotice('');
    try {
      await articleRequest('/api/admin/article-schedules/'+record.id,articleJson('POST',{
        action,scheduledAt:at,revision:record.revision,scheduleId:record.schedule.id,
      }));
      setDialog(null);setNotice(messages[action]);setRefresh(value=>value+1);
    } catch (failure) { setRefresh(value=>value+1); throw failure; }
    finally { busyRef.current=false;setBusy(false); }
  }
  const rows=data?.schedules || [];
  const upcoming=rows.filter(row=>['scheduled','publishing'].includes(row.schedule.state));
  const failures=rows.filter(row=>row.schedule.state==='error');
  const success=rows.filter(row=>row.schedule.state==='success');
  const next=upcoming.filter(row=>row.schedule.state==='scheduled').sort((a,b)=>Date.parse(a.schedule.at)-Date.parse(b.schedule.at))[0];
  const remaining=next ? Math.ceil((Date.parse(next.schedule.at)-(clock+offset.current))/60000) : 0;
  const query=search.trim().toLocaleLowerCase('ru');
  const visible=rows.filter(row=> (tab==='all' || (tab==='upcoming' && ['scheduled','publishing'].includes(row.schedule.state)) || (tab==='history' && ['success','cancelled'].includes(row.schedule.state)) || row.schedule.state===tab)
    && (!query || (row.title+' '+row.category).toLocaleLowerCase('ru').includes(query)))
    .sort((a,b)=>(tab==='history' ? -1 : 1)*(Date.parse(a.schedule.at)-Date.parse(b.schedule.at)));
  const groups=new Map<string,ScheduleArticle[]>();
  for(const row of visible) { const day=toMoscowInput(row.schedule.at).slice(0,10); groups.set(day,[...(groups.get(day)||[]),row]); }
  return <div className="nwd-publication-plan">
    <header className="nwd-plan-heading"><div><span className="nwd-plan-kicker">Редакционный календарь</span><h1>План публикаций<span>.</span></h1><p>Вы управляете временем.<br />Статьи выходят автоматически.</p></div>
      <div><span className="nwd-plan-timezone">МОСКВА · UTC+3</span><a className="lime-button" href="/redaktsiya/index.php?new=1">Новая статья <span aria-hidden="true">↗</span></a></div>
    </header>
    <section className="nwd-plan-summary" aria-label="Сводка публикаций">
      <button type="button" onClick={()=>setTab('upcoming')}><span>В расписании</span><strong>{data ? String(upcoming.length).padStart(2,'0') : '—'}</strong><small>Запланированы и публикуются</small></button>
      <button type="button" onClick={()=>setTab('history')}><span>Опубликовано по плану</span><strong>{data ? String(success.length).padStart(2,'0') : '—'}</strong><small>Публичная версия обновлена</small></button>
      <button type="button" onClick={()=>setTab('error')} className={failures.length ? 'has-errors' : ''}><span>Требуют внимания</span><strong>{data ? String(failures.length).padStart(2,'0') : '—'}</strong><small>{failures.length ? 'Проверьте причину ошибки' : 'Ошибок публикации нет'}</small></button>
      <div className="nwd-plan-next"><span>Ближайший выход</span><strong>{next ? publicationDate(next.schedule.at) : data ? 'План свободен' : error ? 'Нет данных' : 'Загрузка…'}</strong><p>{next?.title || 'Запланируйте статью в редакторе'}</p>{next && <small>{remaining > 0 ? remaining >= 1440 ? 'До выхода · '+Math.floor(remaining/1440)+' дн.' : remaining >= 60 ? 'До выхода · '+Math.floor(remaining/60)+' ч '+remaining%60+' мин' : 'До выхода · '+remaining+' мин' : 'Ожидает ближайшей проверки'} · МСК</small>}</div>
    </section>
    <div className="nwd-plan-live"><span className={'nwd-plan-dot'+(data?.scheduler.enabled && !data.scheduler.lastError ? ' is-online' : '')} aria-hidden="true" /><p>{data?.scheduler.lastError || (data?.scheduler.enabled ? 'Расписание работает на сервере' : data ? data.scheduler.lastCheckedAt ? 'Серверный таймер не отвечает. Проверьте CronTab Beget.' : 'Подключите серверный таймер в CronTab Beget.' : 'Подключение к расписанию…')}<small>{data?.scheduler.lastCheckedAt ? 'Проверено · '+publicationDate(data.scheduler.lastCheckedAt)+' МСК' : 'Ожидаем первую серверную проверку'}</small></p><span>Админку можно закрыть</span><button type="button" disabled={busy} onClick={()=>setRefresh(value=>value+1)}>Обновить ↻</button></div>
    {notice && <p className="nwd-plan-notice" role="status">{notice}</p>}
    {error && <p className="nwd-plan-error" role="alert">{error}{data && ' Показан последний успешно загруженный план.'}</p>}
    <section aria-label="Расписание статей"><div className="nwd-plan-toolbar"><div role="group" aria-label="Фильтр публикаций">{tabs.map(([key,label])=><button type="button" key={key} aria-pressed={tab===key} onClick={()=>setTab(key)}>{label}{key==='error' && failures.length>0 && <span>{failures.length}</span>}</button>)}</div><label>Поиск по статьям<input type="search" placeholder="Название или категория" maxLength={100} value={search} onChange={event=>setSearch(event.target.value)} /></label></div>
      {!data && !error ? <p className="nwd-plan-empty" role="status">Загружаем расписание…</p> : [...groups].map(([day,items])=><section className="nwd-plan-day" key={day}>
        <h2>{publicationDate(items[0].schedule.at,{hour:undefined,minute:undefined,weekday:'long',year:'numeric'})}<span>{items.length} в плане</span></h2>
        {items.map(row=><article className="nwd-plan-row" key={row.id}>
          <time dateTime={row.schedule.at}>{publicationDate(row.schedule.at,{day:undefined,month:undefined})}<small>МСК</small></time>
          <div className="nwd-plan-copy"><span className={'nwd-plan-state nwd-plan-state--'+row.schedule.state}>{scheduleStates[row.schedule.state]}</span><h3><a href={'/redaktsiya/index.php?edit='+row.id}>{row.title || 'Без названия'}</a></h3><p>{row.category || 'Без категории'}</p>{row.schedule.error && <p className="form-error">{row.schedule.error}</p>}{row.schedule.finishedAt && <small>{row.schedule.state==='cancelled' ? 'Снято' : 'Обновлено'} · {publicationDate(row.schedule.finishedAt)} МСК</small>}</div>
          <div className="nwd-plan-row-actions"><a href={'/redaktsiya/index.php?edit='+row.id}>Редактировать ↗</a>{row.schedule.state==='scheduled' && row.status==='scheduled' && <><button type="button" disabled={busy} onClick={()=>setDialog({record:row,action:'schedule'})}>Перенести</button><button type="button" disabled={busy} onClick={()=>setDialog({record:row,action:'publish'})}>Опубликовать сейчас</button><button type="button" disabled={busy} onClick={()=>setDialog({record:row,action:'cancel'})}>Снять с расписания</button></>}{row.schedule.state==='error' && <button className="lime-button" type="button" disabled={busy} onClick={()=>setDialog({record:row,action:'retry'})}>Повторить публикацию</button>}{row.status==='published' && <a href={'/articles/'+row.slug+'/'} target="_blank" rel="noopener noreferrer">Открыть на сайте ↗</a>}</div>
        </article>)}
      </section>)}
      {data && !visible.length && <div className="nwd-plan-empty"><span aria-hidden="true">↗</span><h2>{query ? 'Ничего не найдено' : tab==='error' ? 'Ошибок публикации нет' : tab==='history' ? 'История пока пуста' : 'Здесь появятся ваши публикации'}</h2><p>{query ? 'Попробуйте другое название или категорию.' : 'Откройте статью, выберите «По расписанию» и задайте время выхода.'}</p><a href="/redaktsiya/index.php">Перейти к статьям ↗</a></div>}
    </section>
    <footer className="nwd-plan-footer">Время на этой странице всегда московское. История показывает последний результат расписания каждой статьи.</footer>
    {dialog && <PlanDialog key={dialog.record.id+dialog.action} {...dialog} busy={busy} submit={change} close={()=>setDialog(null)} />}
  </div>;
}
