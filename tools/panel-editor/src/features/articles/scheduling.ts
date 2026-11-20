export const scheduleStates = {
  scheduled: 'Запланировано', publishing: 'Публикуется', success: 'Опубликовано',
  error: 'Нужна проверка', cancelled: 'Снято с расписания',
} as const;
export type ScheduleState = keyof typeof scheduleStates;
export interface PublicationSchedule {
  id: string; at: string; state: ScheduleState; createdAt: string;
  startedAt: string | null; finishedAt: string | null; error: string | null;
}
export interface ScheduleArticle {
  id: string; title: string; slug: string; category: string; revision: number;
  status: 'draft' | 'scheduled' | 'published'; updatedAt: string;
  schedule: PublicationSchedule;
}
export interface ScheduleListing {
  schedules: ScheduleArticle[]; serverNow: string;
  scheduler: { enabled: boolean; lastCheckedAt: string | null; lastError: string | null; intervalMs: number; timeZone: string };
}
export type ScheduleAction = 'schedule' | 'cancel' | 'publish' | 'retry';
export function toMoscowInput(value: string | number | null | undefined) {
  if (value == null) return '';
  const date = new Date(value);
  if (!Number.isFinite(date.getTime())) return '';
  const parts = Object.fromEntries(new Intl.DateTimeFormat('en-GB', {
    timeZone: 'Europe/Moscow', year: 'numeric', month: '2-digit', day: '2-digit',
    hour: '2-digit', minute: '2-digit', hourCycle: 'h23',
  }).formatToParts(date).map(part => [part.type, part.value]));
  return `${parts.year}-${parts.month}-${parts.day}T${parts.hour}:${parts.minute}`;
}
export function fromMoscowInput(value: string) {
  if (!/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/.test(value)) return null;
  const stamp = Date.parse(value + ':00+03:00');
  if (!Number.isFinite(stamp)) return null;
  return toMoscowInput(stamp) === value ? new Date(stamp).toISOString() : null;
}
export function publicationDate(value: string | null, options: Intl.DateTimeFormatOptions = {}) {
  if (!value || !Number.isFinite(Date.parse(value))) return '—';
  return new Intl.DateTimeFormat('ru-RU', {
    timeZone: 'Europe/Moscow', day: 'numeric', month: 'long', hour: '2-digit', minute: '2-digit', ...options,
  }).format(new Date(value));
}
