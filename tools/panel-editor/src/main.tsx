import { createRoot } from 'react-dom/client';
import { useEffect, useState } from 'react';
import ArticlesList from './components/admin/articles/ArticlesList';
import ArticleEdit from './components/admin/articles/ArticleEdit';
import { readArticlePreview } from './components/admin/articles/preview-storage';
import { articleJson, articleRequest } from './components/admin/articles/api';
import './styles/admin-articles.css';

function Preview() {
  const [html, setHtml] = useState('');
  const [error, setError] = useState('');
  useEffect(() => {
    const controller = new AbortController();
    async function load() {
      try {
        const form = readArticlePreview(location.hash.slice(1));
        const data = await articleRequest<{html:string}>('/api/admin/articles/preview', {
          ...articleJson('POST', form), signal: controller.signal,
        });
        setHtml(data.html);
      } catch (cause) {
        if (!controller.signal.aborted) setError(cause instanceof Error ? cause.message : 'Не удалось открыть предпросмотр.');
      }
    }
    void load();
    return () => controller.abort();
  }, []);
  return <>
    <div className="preview-bar"><span>Предпросмотр</span><a href="/redaktsiya/index.php">К статьям</a></div>
    {html ? <div dangerouslySetInnerHTML={{__html:html}} /> : <p role={error ? 'alert' : 'status'}>{error || 'Загрузка…'}</p>}
  </>;
}

const query = new URLSearchParams(location.search);
const root = document.getElementById('editor-root');
if (root) createRoot(root).render(query.has('preview') ? <Preview /> :
  query.has('edit') ? <ArticleEdit id={query.get('edit') || undefined} /> :
  query.has('new') ? <ArticleEdit /> : <ArticlesList />);
