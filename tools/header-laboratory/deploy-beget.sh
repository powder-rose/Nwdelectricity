#!/usr/bin/env bash
set -Eeuo pipefail
umask 077
commit=${1:-}
[[ "$commit" =~ ^[a-f0-9]{40}$ ]] || { echo 'HEADER_LABORATORY_FAILED=Supply the published commit'; exit 1; }
site=${NWD_SITE:-/home/i/infonwt4/nwdelectricity.ru/public_html}
[[ -f "$site/config.php" ]] || { echo 'HEADER_LABORATORY_FAILED=Site not found'; exit 1; }
site=$(cd "$site" && pwd -P)
private="$HOME/.nwd-production"
mkdir -p "$private"
exec 9>"$private/deployment.lock"
flock -n 9 || { echo 'HEADER_LABORATORY_FAILED=Another deployment is active'; exit 1; }
backup=$(mktemp -d "$private/header-laboratory-XXXXXXXX")
echo "HEADER_LABORATORY_BACKUP=$backup"
phpbin=''
for p in /usr/local/php7.3/bin/php /usr/local/bin/php7.3 /opt/php73/bin/php /opt/php/7.3/bin/php php7.3 php73 /usr/local/php-cgi/7.3/bin/php /usr/local/php/7.3/bin/php php; do
    if command -v "$p" >/dev/null 2>&1 && "$p" -r 'exit(PHP_MAJOR_VERSION===7 && PHP_MINOR_VERSION===3?0:1);' 2>/dev/null; then phpbin=$p;break;fi
done
[[ -n "$phpbin" ]] || { echo 'HEADER_LABORATORY_FAILED=PHP 7.3 required'; exit 1; }
changed=0
step=github
clear_cache() {
    "$phpbin" -r '$root=$argv[1];foreach(array("cache","tmp/cache") as $d){$p=$root."/".$d;if(!is_dir($p)||is_link($p)){continue;}$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($p,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);foreach($it as $f){if(!$f->isLink()&&$f->isFile()&&!in_array($f->getFilename(),array(".htaccess",".gitkeep","index.php"),true)){if(!unlink($f->getPathname())){throw new RuntimeException("Cannot clear generated CMS cache");}}}}' "$site"
}
rollback() {
    code=$?;trap - ERR;set +e
    echo "HEADER_LABORATORY_FAILED=$code STEP=$step"
    if [[ "$changed" == 1 ]]; then
        cp -p "$backup/service_navigation.php" "$site/custom/energo/modules/menu/service_navigation.php"
        clear_cache
        echo 'HEADER_LABORATORY_FILES_RESTORED'
    fi
    exit "$code"
}
trap rollback ERR
curl -fsSL --retry 2 --max-time 45 "https://api.github.com/repos/powder-rose/Nwdelectricity/commits/$commit/check-runs" -o "$backup/checks.json"
"$phpbin" -r '$j=json_decode(file_get_contents($argv[1]),true);foreach(array("Git contents and secrets","PHP 7.3 syntax") as $name){$ok=false;foreach($j["check_runs"]??array() as $r){if($r["name"]===$name&&$r["status"]==="completed"&&$r["conclusion"]==="success"){$ok=true;}}if(!$ok){fwrite(STDERR,"GITHUB_CHECK_NOT_READY=".$name."\n");exit(1);}}echo "GITHUB_CHECKS_OK\n";' "$backup/checks.json"
curl -fsSL --retry 2 --max-time 180 "https://codeload.github.com/powder-rose/Nwdelectricity/tar.gz/$commit" -o "$backup/source.tgz"
mkdir "$backup/source"
tar -xzf "$backup/source.tgz" -C "$backup/source" --strip-components=1
source="$backup/source"
step=source-preflight
"$phpbin" -r '$m=json_decode(file_get_contents($argv[1]."/tools/header-laboratory/release.json"),true);$path="custom/energo/modules/menu/service_navigation.php";$entry=null;foreach($m["files"]??array() as $f){if($f["path"]===$path){$entry=$f;}}if(!$entry){exit(1);}$target=$argv[2]."/".$path;if(!is_file($target)||is_link($target)){exit(1);}$hash=function($p){return hash("sha256",str_replace("\r\n","\n",file_get_contents($p)));};if($hash($argv[1]."/".$path)!==$entry["sha256"]){fwrite(STDERR,"RELEASE_HASH_FAILED\n");exit(1);}if(!in_array($hash($target),array($entry["base"],$entry["sha256"]),true)){fwrite(STDERR,"SOURCE_CONFLICT_NO_SITE_FILES_CHANGED\n");exit(1);}echo "SOURCE_PREFLIGHT_OK\n";' "$source" "$site"
"$phpbin" -n -l "$source/custom/energo/modules/menu/service_navigation.php"
cp -p "$site/custom/energo/modules/menu/service_navigation.php" "$backup/service_navigation.php"
step=activate
changed=1
cp "$source/custom/energo/modules/menu/service_navigation.php" "$site/custom/energo/modules/menu/service_navigation.php.nwd-stage"
chmod 0644 "$site/custom/energo/modules/menu/service_navigation.php.nwd-stage"
mv -f "$site/custom/energo/modules/menu/service_navigation.php.nwd-stage" "$site/custom/energo/modules/menu/service_navigation.php"
clear_cache
step=public-check
ok=0
for attempt in 1 2 3; do
    code=$(curl -sS --max-time 45 -o "$backup/page.html" -w '%{http_code}' "https://nwdelectricity.ru/?nwd_release=$commit&attempt=$attempt") || code=000
    if [[ "$code" == 200 ]] && "$phpbin" -r '$html=file_get_contents($argv[1]);if(strpos($html,"[ERROR#")!==false){exit(1);}$d=new DOMDocument;$prev=libxml_use_internal_errors(true);$d->loadHTML("<?xml encoding=\"UTF-8\">".$html,LIBXML_NONET);libxml_clear_errors();libxml_use_internal_errors($prev);$x=new DOMXPath($d);$path="/services/elektroizmeritelnaya-laboratoriya/";foreach(array("//header//*[contains(concat(\" \",normalize-space(@class),\" \"),\" nwd-service-dropdown \")]//a","//header//*[@id=\"mobMenu\"]//a[contains(concat(\" \",normalize-space(@class),\" \"),\" nwd-service-mobile-link \")]") as $selector){$count=0;foreach($x->query($selector) as $a){if(parse_url($a->getAttribute("href"),PHP_URL_PATH)===$path && trim(preg_replace("/[↗\\s]+/u"," ",$a->textContent))==="Электроизмерительная лаборатория"){$count++;}}if($count!==1){fwrite(STDERR,"HEADER_LABORATORY_PUBLIC_FAILED=Menu link missing or duplicated\n");exit(1);}}echo "HEADER_LABORATORY_PUBLIC_OK\n";' "$backup/page.html"; then ok=1;break;fi
    if [[ "$attempt" != 3 ]];then sleep 2;fi
done
[[ "$ok" == 1 ]]
code=$(curl -sS --max-time 45 -o "$backup/laboratory.html" -w '%{http_code}' 'https://nwdelectricity.ru/services/elektroizmeritelnaya-laboratoriya/')
[[ "$code" == 200 ]]
trap - ERR
echo "HEADER_LABORATORY_DEPLOY_OK=$commit"
