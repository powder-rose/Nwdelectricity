#!/usr/bin/env bash
set -Eeuo pipefail
umask 077
commit=${1:-}
[[ "$commit" =~ ^[a-f0-9]{40}$ ]] || { echo 'PHASE_NEUTRAL_FAILED=Supply the published GitHub commit'; exit 1; }
site=${NWD_SITE:-/home/i/infonwt4/nwdelectricity.ru/public_html}
[[ -d "$site" && -f "$site/config.php" ]] || { echo 'PHASE_NEUTRAL_FAILED=Site root not found'; exit 1; }
site=$(cd "$site" && pwd -P)
private="$HOME/.nwd-production"
mkdir -p "$private"
exec 9>"$private/deployment.lock"
flock -n 9 || { echo 'PHASE_NEUTRAL_FAILED=Another deployment is active'; exit 1; }
backup=$(mktemp -d "$private/phase-neutral-XXXXXXXX")
echo "PHASE_NEUTRAL_BACKUP=$backup"
phpbin=''
for candidate in /usr/local/php7.3/bin/php /usr/local/bin/php7.3 /opt/php73/bin/php /opt/php/7.3/bin/php php7.3 php73 /usr/local/php-cgi/7.3/bin/php /usr/local/php/7.3/bin/php php; do
    if command -v "$candidate" >/dev/null 2>&1 && "$candidate" -r 'exit(version_compare(PHP_VERSION,"7.3.0",">=") && version_compare(PHP_VERSION,"7.4.0","<") ? 0 : 1);' 2>/dev/null; then phpbin=$candidate; break; fi
done
[[ -n "$phpbin" ]] || { echo 'PHASE_NEUTRAL_FAILED=PHP CLI 7.3 is required'; exit 1; }
changed=0
db_changed=0
step=github
rollback() {
    code=$?
    trap - ERR
    set +e
    echo "PHASE_NEUTRAL_FAILED=$code STEP=$step"
    if [[ "$db_changed" == 1 && -f "$backup/database.json" ]]; then
        "$phpbin" "$source/scripts/install-phase-neutral.php" --root="$site" --rollback="$backup/database.json"
    fi
    if [[ "$changed" == 1 ]]; then
        while IFS='|' read -r path existed; do
            if [[ "$existed" == 1 ]]; then cp -p "$backup/files/$path" "$site/$path"; else rm -f "$site/$path"; fi
        done < "$backup/restore.list"
        "$phpbin" "$source/scripts/install-phase-neutral.php" --root="$site" --clear-cache
        echo 'PHASE_NEUTRAL_FILES_RESTORED'
    fi
    exit "$code"
}
trap rollback ERR
curl -fsSL --retry 2 --max-time 45 "https://api.github.com/repos/powder-rose/Nwdelectricity/commits/$commit/check-runs" -o "$backup/checks.json"
"$phpbin" -r '$j=json_decode(file_get_contents($argv[1]),true);$wanted=array("Git contents and secrets","PHP 7.3 syntax");foreach($wanted as $name){$ok=false;foreach(($j["check_runs"] ?? array()) as $r){if($r["name"]===$name && $r["status"]==="completed" && $r["conclusion"]==="success"){$ok=true;break;}}if(!$ok){fwrite(STDERR,"GITHUB_CHECK_NOT_READY=".$name."\n");exit(1);}}echo "GITHUB_CHECKS_OK\n";' "$backup/checks.json"
curl -fsSL --retry 2 --max-time 180 "https://codeload.github.com/powder-rose/Nwdelectricity/tar.gz/$commit" -o "$backup/source.tgz"
mkdir "$backup/source"
tar -xzf "$backup/source.tgz" -C "$backup/source" --strip-components=1
source="$backup/source"
step=source-preflight
"$phpbin" -r '
$site=$argv[1];$source=$argv[2];$backup=$argv[3];
$m=json_decode(file_get_contents($source."/tools/phase-neutral/release.json"),true);if(!$m || empty($m["files"])){exit(1);}
$list=array();$conflicts=false;
foreach($m["files"] as $f){$p=$f["path"];if(!preg_match("~^(?:custom/energo/|redaktsiya/assets/|scripts/|tools/phase-neutral/|docs/)[A-Za-z0-9_./-]+$~D",$p)||strpos($p,"..")!==false){exit(1);}
$bytes=file_get_contents($source."/".$p);if($f["text"]){$bytes=str_replace("\r\n","\n",$bytes);}if(hash("sha256",$bytes)!==$f["sha256"]){fwrite(STDERR,"RELEASE_HASH_FAILED=".$p."\n");exit(1);}
$target=$site."/".$p;$exists=is_file($target);if(is_link($target)||(file_exists($target)&&!$exists)){exit(1);}
$ancestor=dirname($target);while(strlen($ancestor)>strlen($site)){if(is_link($ancestor)){exit(1);}$ancestor=dirname($ancestor);}
if($exists){$old=file_get_contents($target);$hash=hash("sha256",$f["text"]?str_replace("\r\n","\n",$old):$old);if($hash!==$f["sha256"] && $hash!==$f["base"] && !in_array($hash,$f["previous"] ?? array(),true)){echo "SOURCE_CONFLICT=".$p."\n";$conflicts=true;}}
elseif($f["base"]!==null){echo "SOURCE_FILE_MISSING=".$p."\n";$conflicts=true;}
$list[]=$p."|".($exists?1:0);
}
if($conflicts){echo "NO_SITE_FILES_CHANGED\n";exit(1);}file_put_contents($backup."/restore.list",implode("\n",$list)."\n");echo "SOURCE_PREFLIGHT_OK\n";
' "$site" "$source" "$backup"
step=database-preflight
"$phpbin" "$source/scripts/install-phase-neutral.php" --root="$site" --check
step=php-checks
while IFS='|' read -r path existed; do
    if [[ "$path" == *.php ]]; then "$phpbin" -n -d short_open_tag=1 -l "$source/$path" >/dev/null; fi
    if [[ "$existed" == 1 ]]; then mkdir -p "$backup/files/$(dirname "$path")"; cp -p "$site/$path" "$backup/files/$path"; fi
done < "$backup/restore.list"
"$phpbin" "$source/scripts/test-document-request.php"
"$phpbin" "$source/scripts/test-phase-neutral.php"
"$phpbin" "$source/scripts/test-phase-neutral-render.php"
step=activate
changed=1
while IFS='|' read -r path existed; do
    mkdir -p "$site/$(dirname "$path")"
    chmod 0755 "$site/$(dirname "$path")"
    cp "$source/$path" "$site/$path.nwd-stage"
    chmod 0644 "$site/$path.nwd-stage"
    mv -f "$site/$path.nwd-stage" "$site/$path"
done < "$backup/restore.list"
step=database
# Set before running: even a post-commit cache error must trigger database rollback.
db_changed=1
"$phpbin" "$source/scripts/install-phase-neutral.php" --root="$site" --backup="$backup/database.json"
step=public-check
url='https://nwdelectricity.ru/services/izmerenie-petli-faza-nul/'
code=$(curl -sS --max-time 45 -o "$backup/page.html" -w '%{http_code}' "$url?nwd_release=$commit")
[[ "$code" == 200 ]]
"$phpbin" -r 'define("DIAFAN",true);$h=file_get_contents($argv[1]);$c=require $argv[2];foreach(array($c["name"],$c["seoTitle"],$c["description"],"raschet-stoimosti","data-fn-form","phase-neutral.css") as $s){if(strpos(html_entity_decode(strip_tags($h,"<link><input><div><form><script>"),ENT_QUOTES,"UTF-8"),$s)===false && strpos($h,$s)===false){fwrite(STDERR,"PUBLIC_CONTENT_CHECK_FAILED\n");exit(1);}}if(preg_match_all("~<h1\\b~i",$h)!==1 || preg_match("~<meta[^>]+name=[\x27\x22]robots[\x27\x22][^>]+content=[\x27\x22][^\x27\x22]*noindex~i",$h)){exit(1);}if(!preg_match("~<link[^>]+href=[\x27\x22]https://nwdelectricity\\.ru/services/izmerenie-petli-faza-nul/[\x27\x22][^>]+rel=[\x27\x22]canonical~i",$h)){exit(1);}echo "PUBLIC_PAGE_OK\n";' "$backup/page.html" "$source/custom/energo/themes/phase_neutral_content.php"
trap - ERR
echo "PHASE_NEUTRAL_DEPLOY_OK=$commit"
echo "PAGE=$url"
