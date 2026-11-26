#!/usr/bin/env bash
set -Eeuo pipefail
umask 077
commit=${1:-}
[[ "$commit" =~ ^[a-f0-9]{40}$ ]] || { echo 'CALLBACK_FAILED=Supply the published GitHub commit'; exit 1; }
site=${NWD_SITE:-/home/i/infonwt4/nwdelectricity.ru/public_html}
[[ -d "$site" && -f "$site/config.php" ]] || { echo 'CALLBACK_FAILED=Site root not found'; exit 1; }
site=$(cd "$site" && pwd -P)
private="$HOME/.nwd-production"
mkdir -p "$private"
exec 9>"$private/deployment.lock"
flock -n 9 || { echo 'CALLBACK_FAILED=Another deployment is active'; exit 1; }
backup=$(mktemp -d "$private/callback-XXXXXXXX")
echo "CALLBACK_BACKUP=$backup"
phpbin=''
for candidate in /usr/local/php7.3/bin/php /usr/local/bin/php7.3 /opt/php73/bin/php /opt/php/7.3/bin/php php7.3 php73 /usr/local/php-cgi/7.3/bin/php /usr/local/php/7.3/bin/php php; do
    if command -v "$candidate" >/dev/null 2>&1 && "$candidate" -r 'exit(PHP_MAJOR_VERSION===7 && PHP_MINOR_VERSION===3 ? 0 : 1);' 2>/dev/null; then phpbin=$candidate; break; fi
done
[[ -n "$phpbin" ]] || { echo 'CALLBACK_FAILED=PHP CLI 7.3 is required'; exit 1; }
step=github
changed=0
rollback() {
    code=$?
    trap - ERR
    set +e
    echo "CALLBACK_FAILED=$code STEP=$step"
    if [[ "$changed" == 1 ]]; then
        while IFS='|' read -r path existed; do
            if [[ "$existed" == 1 ]]; then cp -p "$backup/files/$path" "$site/$path"; else rm -f "$site/$path"; fi
        done < "$backup/restore.list"
        echo 'CALLBACK_FILES_RESTORED'
    fi
    exit "$code"
}
trap rollback ERR
curl -fsSL --retry 2 --max-time 45 "https://api.github.com/repos/powder-rose/Nwdelectricity/commits/$commit/check-runs" -o "$backup/checks.json"
"$phpbin" -r '$j=json_decode(file_get_contents($argv[1]),true);foreach(array("Git contents and secrets","PHP 7.3 syntax") as $name){$ok=false;foreach(($j["check_runs"]??array()) as $r){if($r["name"]===$name&&$r["status"]==="completed"&&$r["conclusion"]==="success"){$ok=true;break;}}if(!$ok){fwrite(STDERR,"GITHUB_CHECK_NOT_READY=".$name."\n");exit(1);}}echo "GITHUB_CHECKS_OK\n";' "$backup/checks.json"
curl -fsSL --retry 2 --max-time 180 "https://codeload.github.com/powder-rose/Nwdelectricity/tar.gz/$commit" -o "$backup/source.tgz"
mkdir "$backup/source"
tar -xzf "$backup/source.tgz" -C "$backup/source" --strip-components=1
source="$backup/source"
step=source-preflight
"$phpbin" -r '
$site=$argv[1];$source=$argv[2];$backup=$argv[3];$m=json_decode(file_get_contents($source."/tools/callback/release.json"),true);if(!$m||empty($m["files"])){exit(1);}$list=array();
foreach($m["files"] as $f){$p=$f["path"];if(!preg_match("~^(?:custom/energo/|scripts/|tools/callback/|docs/)[A-Za-z0-9_./-]+$~D",$p)||strpos($p,"..")!==false){exit(1);}$bytes=file_get_contents($source."/".$p);if(hash("sha256",str_replace("\r\n","\n",$bytes))!==$f["sha256"]){fwrite(STDERR,"RELEASE_HASH_FAILED=".$p."\n");exit(1);}$target=$site."/".$p;$exists=is_file($target);if(is_link($target)||(file_exists($target)&&!$exists)){exit(1);}$parent=dirname($target);while(strlen($parent)>strlen($site)){if(is_link($parent)){exit(1);}$parent=dirname($parent);}if($exists){$hash=hash("sha256",str_replace("\r\n","\n",file_get_contents($target)));if($hash!==$f["sha256"]&&$hash!==$f["base"]){fwrite(STDERR,"SOURCE_CONFLICT=".$p."\n");exit(1);}}elseif($f["base"]!==null){fwrite(STDERR,"SOURCE_FILE_MISSING=".$p."\n");exit(1);}$list[]=$p."|".($exists?1:0);}
$p="tools/callback/release.json";if(is_link($site."/".$p)||(file_exists($site."/".$p)&&!is_file($site."/".$p))){exit(1);}$list[]=$p."|".(is_file($site."/".$p)?1:0);file_put_contents($backup."/restore.list",implode("\n",$list)."\n");echo "SOURCE_PREFLIGHT_OK\n";
' "$site" "$source" "$backup"
step=php-checks
while IFS='|' read -r path existed; do
    if [[ "$path" == *.php ]]; then "$phpbin" -n -d short_open_tag=1 -l "$source/$path"; fi
    if [[ "$existed" == 1 ]]; then mkdir -p "$backup/files/$(dirname "$path")"; cp -p "$site/$path" "$backup/files/$path"; fi
done < "$backup/restore.list"
step=activate
changed=1
# Dependencies first, shared include last; no database writes, cache purge or service restart.
for target_existed in 0 1; do
    while IFS='|' read -r path existed; do
        [[ "$existed" == "$target_existed" ]] || continue
        mkdir -p "$site/$(dirname "$path")"
        chmod 0755 "$site/$(dirname "$path")"
        cp "$source/$path" "$site/$path.nwd-stage"
        chmod 0644 "$site/$path.nwd-stage"
        mv -f "$site/$path.nwd-stage" "$site/$path"
    done < "$backup/restore.list"
done
step=public-check
for page in izmerenie-petli-faza-nul proverka-uzo ispytanie-elektroinstrumenta elektroizmeritelnaya-laboratoriya zamer-soprotivleniya-izolyatsii proverka-soprotivleniya-zazemliteley proverka-avtomaticheskikh-vyklyuchateley; do
    public_ok=0
    for attempt in 1 2 3; do
        code=$(curl -sS --max-time 45 -o "$backup/$page.html" -w '%{http_code}' "https://nwdelectricity.ru/services/$page/?nwd_release=$commit&attempt=$attempt") || code=000
        if [[ "$code" == 200 ]] && "$phpbin" "$source/scripts/check-service-callback.php" "$backup/$page.html"; then public_ok=1; break; fi
        if [[ "$attempt" != 3 ]]; then sleep 2; fi
    done
    [[ "$public_ok" == 1 ]]
    echo "CALLBACK_PAGE_CHECKED=$page"
done
code=$(curl -sS --max-time 45 -o "$backup/callback.css" -w '%{http_code}' "https://nwdelectricity.ru/custom/energo/css/service-callback.css?nwd_release=$commit")
[[ "$code" == 200 ]]
cmp -s "$backup/callback.css" "$source/custom/energo/css/service-callback.css"
trap - ERR
echo "CALLBACK_DEPLOY_OK=$commit"
