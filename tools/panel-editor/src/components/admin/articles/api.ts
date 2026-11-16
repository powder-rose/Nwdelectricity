import type {
  ArticleForm,
  ArticleRecord,
  ArticleImage,
} from '@/features/articles/model';
import {
  adminJson,
  adminRequest,
  AdminRequestError,
  adminRequestTimeout,
  type AdminRequestPolicy,
} from '@/lib/admin-request';

export { adminJson as articleJson } from '@/lib/admin-request';

export async function articleRequest<T>(
  path: string,
  options: RequestInit = {},
  policy?: AdminRequestPolicy,
): Promise<T> {
  try {
    const url = new URL(path, location.origin);
    const parts = url.pathname.split('/').filter(Boolean);
    url.searchParams.set('resource', parts[2]);
    if (parts[3]) url.searchParams.set('id', parts[3]);
    const headers = new Headers(options.headers);
    const csrf = document.querySelector<HTMLMetaElement>('meta[name="nwd-csrf"]')?.content;
    if (csrf && options.method && options.method !== 'GET') headers.set('X-NWD-CSRF', csrf);
    return await adminRequest<T>('/redaktsiya/api.php?' + url.searchParams, {...options, headers}, policy);
  } catch (error) {
    if (error instanceof AdminRequestError && error.status === 401)
      throw new AdminRequestError(
        'Сессия истекла. Войдите в админку в другой вкладке и повторите сохранение.',
        error.kind,
        error.status,
      );
    throw error;
  }
}
export function saveArticle(
  form: ArticleForm,
  requestId: string,
  article?: ArticleRecord,
) {
  return articleRequest<{ article: ArticleRecord; ok: true }>(
    article ? '/api/admin/articles/' + article.id : '/api/admin/articles',
    adminJson(
      article ? 'PUT' : 'POST',
      article ? { form, revision: article.revision } : { form, requestId },
    ),
  );
}
async function inlineWebp(file: File): Promise<Blob> {
  const image = new Image();
  const url = URL.createObjectURL(file);
  try {
    image.src = url;
    await image.decode();
    if (!image.naturalWidth || image.naturalWidth * image.naturalHeight > 20_000_000)
      throw new Error('Используйте изображение до 20 мегапикселей.');
    const scale = Math.min(1, 1920 / Math.max(image.naturalWidth, image.naturalHeight));
    const canvas = document.createElement('canvas');
    canvas.width = Math.max(1, Math.round(image.naturalWidth * scale));
    canvas.height = Math.max(1, Math.round(image.naturalHeight * scale));
    const ctx = canvas.getContext('2d');
    if (!ctx) throw new Error('Не удалось подготовить изображение.');
    ctx.fillStyle = '#fff';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.drawImage(image, 0, 0, canvas.width, canvas.height);
    const blob = await new Promise<Blob | null>(resolve => canvas.toBlob(resolve, 'image/jpeg', 0.94));
    if (!blob) throw new Error('Не удалось открыть WebP.');
    return blob;
  } finally {
    URL.revokeObjectURL(url);
  }
}

export async function uploadImage(file: File) {
  const body = new FormData();
  // The current PHP/GD build accepts JPEG and PNG on every installation.
  if (file.type === 'image/webp') body.set('image', await inlineWebp(file), 'image.jpg');
  else body.set('image', file);
  return articleRequest<ArticleImage>(
    '/api/admin/article-images',
    { method: 'POST', body },
    { timeoutMs: adminRequestTimeout.image },
  );
}
export function imageSelectionError(file: File) {
  if (
    !['image/jpeg', 'image/png', 'image/webp'].includes(file.type) ||
    !/\.(?:jpe?g|png|webp)$/i.test(file.name)
  )
    return 'Разрешены только JPG, PNG и WEBP.';
  if (!file.size || file.size > 5 * 1024 * 1024)
    return 'Размер изображения должен быть от 1 байта до 5 МБ.';
  return '';
}
