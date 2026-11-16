<?php
if (!defined('NWD_PANEL')) { http_response_code(404); exit; }

function panelUpload() {
    $file=$_FILES['image'] ?? null;
    if (!$file || $file['error']!==UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name']) || $file['size']>5*1024*1024) { panelFail('Выберите изображение JPG, PNG или WebP до 5 МБ.'); }
    $path=$file['tmp_name']; $info=@getimagesize($path);
    if (!$info || !in_array($info['mime'],array('image/jpeg','image/png','image/webp'),true) || $info[0]*$info[1]>20000000) { panelFail('Формат или размеры изображения не поддерживаются.'); }
    $src=@imagecreatefromstring(file_get_contents($path));
    if (!$src) { panelFail('Не удалось прочитать изображение.'); }
    $scale=min(1,1920/max($info[0],$info[1]));
    $w=max(1,(int)round($info[0]*$scale));$h=max(1,(int)round($info[1]*$scale));
    $dst=imagecreatetruecolor($w,$h);
    $white=imagecolorallocate($dst,255,255,255);imagefill($dst,0,0,$white);
    imagecopyresampled($dst,$src,0,0,0,0,$w,$h,$info[0],$info[1]);
    ob_start();
    $ok=imagejpeg($dst,null,91);$bytes=ob_get_clean();
    imagedestroy($src);imagedestroy($dst);
    if (!$ok || !$bytes) { panelFail('Не удалось сохранить изображение.'); }
    $id=bin2hex(random_bytes(16));
    panelQuery('INSERT INTO '.panelTable('panel_image').' (id,mime,width,height,bytes) VALUES (?,?,?,?,?)',array($id,'image/jpeg',$w,$h,$bytes));
    return array('url'=>'/redaktsiya/image.php?id='.$id,'width'=>$w,'height'=>$h);
}
