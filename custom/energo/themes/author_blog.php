<?php
if (!defined('DIAFAN')) { http_response_code(404); exit; }
if (!defined('NWD_PANEL')) { define('NWD_PANEL',true); }
require_once ABSOLUTE_PATH.'redaktsiya/lib/db.php';
require_once ABSOLUTE_PATH.'redaktsiya/lib/content.php';

final class NwdAuthorBlog {
    const AUTHOR_IMAGE='/custom/energo/images/blog/nikolai-boykov-blog.webp';
    public static function h($value) { return htmlspecialchars((string)$value,ENT_QUOTES | ENT_SUBSTITUTE,'UTF-8'); }
    public static function visible() {
        $language=panelLanguage();
        return "a.status='published' AND a.deleted=0 AND s.trash='0' AND s.act".$language."='1'";
    }
    public static function fields() {
        return "a.id,a.site_id,a.title,a.slug,a.category,a.published_at,a.updated_at,JSON_UNQUOTE(JSON_EXTRACT(a.payload,'$.image')) AS image,JSON_UNQUOTE(JSON_EXTRACT(a.payload,'$.imageAlt')) AS image_alt,LEFT(JSON_UNQUOTE(JSON_EXTRACT(a.payload,'$.content')),2800) AS intro";
    }
    public static function tables() { return panelTable('panel_article').' a JOIN '.panelTable('site').' s ON s.id=a.site_id'; }
    public static function listing(array $query) {
        panelDb();
        $search=is_string($query['q'] ?? null) ? mb_substr(trim($query['q']),0,100,'UTF-8') : '';
        $category=is_string($query['category'] ?? null) ? mb_substr(trim($query['category']),0,80,'UTF-8') : '';
        $where=self::visible(); $values=array();
        if ($search!=='') { $where.=' AND (LOCATE(?,a.title)>0 OR LOCATE(?,a.category)>0)';$values[]=$search;$values[]=$search; }
        if ($category!=='') { $where.=' AND a.category=?';$values[]=$category; }
        $total=(int)panelRow('SELECT COUNT(*) n FROM '.self::tables().' WHERE '.$where,$values)['n'];
        $pages=max(1,(int)ceil($total/10));$page=max(1,min($pages,(int)($query['page'] ?? 1)));
        $rows=panelRows('SELECT '.self::fields().' FROM '.self::tables().' WHERE '.$where.' ORDER BY a.published_at DESC,a.id LIMIT 10 OFFSET '.(($page-1)*10),$values);
        $categories=panelRows('SELECT DISTINCT a.category FROM '.self::tables().' WHERE '.self::visible()." AND a.category<>'' ORDER BY a.category");
        return array('articles'=>$rows,'categories'=>array_column($categories,'category'),'total'=>$total,'pages'=>$pages,'page'=>$page,'search'=>$search,'category'=>$category);
    }
    public static function url($slug) { return '/articles/'.rawurlencode($slug).'/'; }
    public static function query(array $changes, array $current) {
        $args=array_merge(array('page'=>$current['page'] ?? 1,'q'=>$current['search'] ?? '','category'=>$current['category'] ?? ''),$changes);
        foreach($args as $key=>$value) { if ($value==='' || ($key==='page' && (int)$value===1)) { unset($args[$key]); } }
        return '/articles/'.($args ? '?'.http_build_query($args,'','&',PHP_QUERY_RFC3986) : '').'#materials';
    }
    public static function date($stamp,$format='j F Y') {
        if (!$stamp) { return ''; }
        $day=(new DateTimeImmutable('@'.(int)$stamp))->setTimezone(new DateTimeZone('Europe/Moscow'));
        if ($format==='iso') { return $day->format('Y-m-d'); }
        $months=array('января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
        return $day->format('j').' '.$months[(int)$day->format('n')-1].' '.$day->format('Y');
    }
    public static function countLabel($count) {
        $tail=$count%100;$n=$count%10;
        return $count.' '.($tail>=11 && $tail<=14 ? 'материалов' : ($n===1 ? 'материал' : ($n>=2 && $n<=4 ? 'материала' : 'материалов')));
    }
    public static function image($value) {
        return is_string($value) && preg_match('~^/redaktsiya/image\.php\?id=[a-f0-9]{32}$~D',$value) ? $value : '';
    }
    public static function imageSize($value) {
        $id=panelImageId($value);
        if(!$id){return array();}
        $row=panelRow('SELECT width,height FROM '.panelTable('panel_image').' WHERE id=?',array($id));
        return $row && (int)$row['width']>0 && (int)$row['height']>0 ? array('width'=>(int)$row['width'],'height'=>(int)$row['height']) : array();
    }
    public static function summary($html) {
        $html=panelRestoreUtf8Html((string)$html);
        $text=trim(preg_replace('/\s+/u',' ',html_entity_decode(strip_tags($html),ENT_QUOTES | ENT_HTML5,'UTF-8')));
        return mb_strlen($text,'UTF-8')>170 ? rtrim(mb_substr($text,0,167,'UTF-8')).'…' : $text;
    }
    public static function card(array $item,$featured=false,$number=1) {
        $image=self::image($item['image'] ?? '');$title=self::h($item['title']);$url=self::h(self::url($item['slug']));
        $category=self::h($item['category'] ?: 'Практика');$date=self::h(self::date($item['published_at']));
        $classes=$featured ? 'nwd-blog-card nwd-blog-card--featured' : 'nwd-blog-card';
        echo '<article class="'.$classes.'"><a class="nwd-blog-card__visual" href="'.$url.'" tabindex="-1" aria-hidden="true">';
        if($image) { echo '<img src="'.self::h($image).'" alt="" width="1280" height="800" loading="lazy" decoding="async">'; }
        else { echo '<div class="nwd-blog-cover"><span>СПЕЦЭНЕРГОРАЗВИТИЕ</span><i aria-hidden="true"></i><strong>'.str_pad((string)$number,2,'0',STR_PAD_LEFT).'</strong><small>'.$category.'</small></div>'; }
        echo '<span class="nwd-blog-card__corner" aria-hidden="true">↗</span></a><div class="nwd-blog-card__copy">';
        if($featured) { echo '<span class="nwd-blog-eyebrow">Последнее в блоге</span>'; }
        echo '<div class="nwd-blog-meta"><span>'.$category.'</span><time datetime="'.self::h(self::date($item['published_at'],'iso')).'">'.$date.'</time></div>';
        echo '<h'.($featured?'2':'3').'><a href="'.$url.'">'.$title.'</a></h'.($featured?'2':'3').'>';
        $summary=self::summary($item['intro'] ?? '');if($summary!==''){echo '<p>'.self::h($summary).'</p>';}
        echo '<a class="nwd-blog-card__read" href="'.$url.'" aria-label="Читать: '.$title.'">Читать статью <span aria-hidden="true">↗</span></a></div></article>';
    }
    public static function article($siteId) {
        panelDb();
        $row=panelRow('SELECT a.* FROM '.self::tables().' WHERE a.site_id=? AND '.self::visible(),array((int)$siteId));
        if (!$row) { return null; }
        $form=json_decode($row['payload'],true,512,JSON_THROW_ON_ERROR);
        $form['content']=panelRestoreUtf8Html($form['content']);
        $row['form']=$form;
        return $row;
    }
    public static function reading($html) {
        preg_match_all('/[\p{L}\p{N}]+/u',strip_tags($html),$words);
        return max(1,(int)ceil(count($words[0])/180));
    }
    public static function body($html) {
        $doc=panelUtf8Document($html);$toc=array();$used=array();$index=0;
        foreach($doc->getElementsByTagName('h2') as $heading) {
            $index++;$id=$heading->getAttribute('id');
            if(!preg_match('/^[A-Za-z][A-Za-z0-9_-]{0,99}$/D',$id) || isset($used[$id])) { $id='section-'.$index; }
            $used[$id]=true;$heading->setAttribute('id',$id);
            if(count($toc)<12 && !panelInsideDocument($heading)) { $toc[]=array('id'=>$id,'text'=>$heading->textContent); }
        }
        $result='';foreach($doc->getElementsByTagName('body')->item(0)->childNodes as $node){$result.=$doc->saveHTML($node);}
        return array('html'=>panelRenderDocuments($result),'toc'=>$toc);
    }
    public static function related($id,$category) {
        return panelRows('SELECT '.self::fields().' FROM '.self::tables().' WHERE '.self::visible().' AND a.id<>? ORDER BY (a.category=?) DESC,a.published_at DESC,a.id LIMIT 3',array($id,$category));
    }
    public static function jsonld(array $data) {
        echo '<script type="application/ld+json">'.json_encode($data,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT).'</script>';
    }
}
