l<?php
require '.auth.php';

// Need to require these files
if (!function_exists('media_handle_upload')) {
    require_once '../wp-admin'.'/includes/image.php';
    require_once '../wp-admin'.'/includes/file.php';
    require_once '../wp-admin'.'/includes/media.php';
}

/**
 * Cancella il file da kiri-uploads.
 */
function cleanUploads()
{
  array_map('unlink', glob('kiri-uploads/*.*'));
}

/**
 * Copia il file in kiri-uploads.
 */
function copyUploads($fileImgFullPath)
{
    $dest = 'kiri-uploads/'.basename($fileImgFullPath);
    copy($fileImgFullPath, $dest);
}

function addImage($productId, $fileImgFullPath, $description)
{
    $found = false;
    if (file_exists($fileImgFullPath.'.png')) {
        $fileImgFullPath .= '.png';
        $found = true;
    } elseif (file_exists($fileImgFullPath.'.jpg')) {
        $fileImgFullPath .= '.jpg';
        $found = true;
    } elseif (file_exists($fileImgFullPath.'.jpeg')) {
        $fileImgFullPath .= '.jpeg';
        $found = true;
    } elseif (file_exists($fileImgFullPath.'.gif')) {
        $fileImgFullPath .= '.gif';
        $found = true;
    } elseif (file_exists($fileImgFullPath.'.ico')) {
        $fileImgFullPath .= '.ico';
        $found = true;
    }

    if ($found) {
      $fileImg=basename($fileImgFullPath);
        cleanUploads();
        copyUploads($fileImgFullPath);
        $title = "title: " . $description  ;
        $tagline = "tagline: " . $description;
        $html = addMediaLibrary($productId, $fileImg, $description);
        $images = get_attached_media( 'image', $productId );
        foreach($images as $image){
          $thumbnailId=$image->ID;
          break;
        }
        echo "productId =  $productId, thumbnailId= $thumbnailId, image: ".$fileImg." description: $description\n\r";
        update_post_meta($productId, '_thumbnail_id', $image->ID);
        // postInsertMetaRow($thumbnailId,'_wp_attachment_image_alt',$fileImg);
        // postInsertMetaRow($thumbnailId,'_wp_attached_file','2016/11/'.$fileImg);
    }
    return $found;
}

function addMediaLibrary($productId, $filename, $description)
{
    $file = 'kiri-uploads/'.$filename;
    if (!file_exists($file) || 0 === strlen(trim($filename))) {
        error_log('The file you are attempting to upload, '.$file.', does not exist.');
        return;
    }
    $uploads = wp_upload_dir();
    $uploads_dir = $uploads['path'];
    $uploads_url = $uploads['url'];

    copy($file, trailingslashit($uploads_dir).$filename);
    $file = trailingslashit($uploads_url).$filename;
    $html = media_sideload_image($file, $productId, $description);
    if (is_wp_error($productId)) {
        error_log(print_r($productId, true));
        return 'errore!!!';
    }
    return $html;
}
