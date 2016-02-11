<?php
/*
  RoxyFileman - web based file manager. Ready to use with CKEditor, TinyMCE. 
  Can be easily integrated with any other WYSIWYG editor or CMS.

  Copyright (C) 2013, RoxyFileman.com - Lyubomir Arsov. All rights reserved.
  For licensing, see LICENSE.txt or http://RoxyFileman.com/license

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.

  Contact: Lyubomir Arsov, liubo (at) web-lobby.com
*/
include '../system.inc.php';
include 'functions.inc.php';

verifyAction('UPLOAD');
checkAccess('UPLOAD');

$path = trim(empty($_POST['d'])?getFilesPath():$_POST['d']);
verifyPath($path);
$res = '';
if(is_dir(fixPath($path))){
  if(!empty($_FILES['files']) && is_array($_FILES['files']['tmp_name'])){
    $errors = $errorsExt = array();
    foreach($_FILES['files']['tmp_name'] as $k=>$v){
      $filename = $_FILES['files']['name'][$k];
      $filename = RoxyFile::MakeUniqueFilename(fixPath($path), $filename);
      $filePath = fixPath($path).'/'.$filename;
      if(!RoxyFile::CanUploadFile($filename))
        $errorsExt[] = $filename;
      elseif(!move_uploaded_file($v, $filePath))
         $errors[] = $filename; 
      if(is_file($filePath))
         @chmod ($filePath, octdec(FILEPERMISSIONS));
      if(RoxyFile::IsImage($filename) && (intval(MAX_IMAGE_WIDTH) > 0 || intval(MAX_IMAGE_HEIGHT) > 0))
        RoxyImage::Resize($filePath, $filePath, intval(MAX_IMAGE_WIDTH), intval(MAX_IMAGE_HEIGHT));
    }
    if($errors && $errorsExt)
      $res = getSuccessRes(t('E_UploadNotAll').' '.t('E_FileExtensionForbidden'));
    elseif($errorsExt)
      $res = getSuccessRes(t('E_FileExtensionForbidden'));
    elseif($errors)
      $res = getSuccessRes(t('E_UploadNotAll'));
    else
      $res = getSuccessRes();
  }
  else
    $res = getErrorRes(t('E_UploadNoFiles'));
}
else
  $res = getErrorRes(t('E_UploadInvalidPath'));

?>
<script>
parent.fileUploaded(<?php echo $res;?>);
</script>