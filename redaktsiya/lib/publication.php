<?php
if (!defined('NWD_PANEL')) { http_response_code(404); exit; }

// Only IDs recorded by this panel can be updated. Existing routes always win.
function panelRouteTaken($path, $siteId = 0) {
    $route = panelRow('SELECT id FROM '.panelTable('rewrite')." WHERE rewrite=? AND NOT (module_name='site' AND element_type='element' AND element_id=?) LIMIT 1 FOR UPDATE",array($path,$siteId));
    $redirect = panelRow('SELECT id FROM '.panelTable('redirect')." WHERE redirect IN (?,?) AND NOT (module_name='site' AND element_type='element' AND element_id=?) LIMIT 1 FOR UPDATE",array($path,$path.'/',$siteId));
    return $route || $redirect;
}

function panelAssertOwned($id, $theme) {
    $row = panelRow('SELECT theme,trash FROM '.panelTable('site').' WHERE id=? FOR UPDATE',array((int)$id));
    if (!$row || $row['theme'] !== $theme || $row['trash'] !== '0') {
        panelFail('Связанная страница была изменена в DIAFAN. Проверьте её перед публикацией.',409);
    }
}

function panelEncodedField($value, $limit) {
    $encoded = panelHtml($value);
    if (mb_strlen($encoded) <= $limit) { return $encoded; }
    // DIAFAN prints its stored fields as HTML. Do not cut in the middle of an entity.
    return preg_replace('/&[a-zA-Z#0-9]*$/D', '', mb_substr($encoded,0,$limit));
}

function panelWritePage($id, $parent, $theme, $slug, $title, $seoTitle, $description, $body, $active) {
    $lang=panelLanguage();
    $table=panelTable('site');
    $path=$slug ? 'articles/'.$slug : 'articles';
    if (panelRouteTaken($path,(int)$id)) { panelFail('Этот адрес уже занят страницей или перенаправлением DIAFAN.',409); }
    $values=array((int)$parent,$theme,panelEncodedField($title,100),panelEncodedField($seoTitle,250),panelHtml($description),$body,(string)(int)$active,$path.'/',time());
    $fields="parent_id=?,theme=?,name$lang=?,title_meta$lang=?,descr$lang=?,text$lang=?,act$lang=?,canonical$lang=?,timeedit=?";
    if (!$id) {
        panelQuery("INSERT INTO $table SET $fields, changefreq='monthly'", $values);
        $id=panelDb()->insert_id;
        panelQuery('INSERT INTO '.panelTable('rewrite')." (rewrite,module_name,element_id,element_type) VALUES (?,'site',?,'element')",array($path,$id));
        if ($parent) { panelQuery('INSERT INTO '.panelTable('site_parents').' (element_id,parent_id) VALUES (?,?)',array($id,$parent)); }
    } else {
        panelAssertOwned($id,$theme);
        panelQuery("UPDATE $table SET $fields WHERE id=?",array_merge($values,array((int)$id)));
        $old=panelRow('SELECT id,rewrite FROM '.panelTable('rewrite')." WHERE module_name='site' AND element_type='element' AND element_id=? FOR UPDATE",array((int)$id));
        if (!$old) { panelFail('У связанной страницы пропал адрес в DIAFAN.',409); }
        if ($old['rewrite'] !== $path) {
            $redirect=panelTable('redirect');
            panelQuery("DELETE FROM $redirect WHERE module_name='site' AND element_type='element' AND element_id=? AND redirect IN (?,?)",array((int)$id,$path,$path.'/'));
            panelQuery("INSERT INTO $redirect (redirect,module_name,element_id,element_type,code) VALUES (?,'site',?,'element',301)",array($old['rewrite'].'/',(int)$id));
            panelQuery('UPDATE '.panelTable('rewrite').' SET rewrite=? WHERE id=?',array($path,(int)$old['id']));
        }
    }
    $map=panelTable('map_index');
    panelQuery("DELETE FROM $map WHERE module_name='site' AND element_type='element' AND element_id=?",array((int)$id));
    if ($active) {
        panelQuery("INSERT INTO $map (url,module_name,element_id,element_type,timeedit,changefreq,priority,date_start,date_finish) VALUES (?,'site',?,'element',?,'monthly','0.7',0,0)",array($path.'ROUTE_END',(int)$id,time()));
    }
    return (int)$id;
}

function panelPublish(array $form, $siteId, $publishedAt, $state) {
    $published=$form['status']==='published';
    if (!$siteId && !$published) { return null; }
    $index=(int)$state['journal_id'];
    if (!$index) {
        $index=panelWritePage(null,0,'panel_journal.php','','Статьи','Статьи — Спецэнергоразвитие','Статьи об организации обслуживания электрооборудования.','',true);
        panelQuery('UPDATE '.panelTable('panel_state').' SET journal_id=? WHERE id=1',array($index));
    }
    return panelWritePage($siteId,$index,'panel_article.php',$form['slug'],$form['title'],$form['seoTitle'],$form['seoDescription'],panelArticleHtml($form,$publishedAt),$published);
}

function panelRefreshJournal($id) {
    if (!$id) { return; }
    $articles=panelTable('panel_article');
    $count=(int)panelRow("SELECT COUNT(*) AS n FROM $articles WHERE status='published' AND deleted=0")['n'];
    panelAssertOwned($id,'panel_journal.php');
    $lang=panelLanguage();
    panelQuery('UPDATE '.panelTable('site')." SET count_children=?, act$lang=?,timeedit=? WHERE id=?",array($count,'1',time(),(int)$id));
    $map=panelTable('map_index');
    panelQuery("DELETE FROM $map WHERE module_name='site' AND element_type='element' AND element_id=?",array((int)$id));
    if ($count) {
        panelQuery("INSERT INTO $map (url,module_name,element_id,element_type,timeedit,changefreq,priority,date_start,date_finish) VALUES ('articlesROUTE_END','site',?,'element',?,'weekly','0.7',0,0)",array((int)$id,time()));
    }
}
